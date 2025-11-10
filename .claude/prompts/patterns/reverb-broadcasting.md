# Laravel Reverb Broadcasting Pattern

## What is Laravel Reverb?

**Laravel Reverb** is Laravel's official WebSocket server (introduced in Laravel 11), providing a blazing-fast, scalable real-time broadcasting solution without external dependencies like Pusher or Ably.

### Key Benefits
- **Zero External Dependencies** - Self-hosted WebSocket server
- **Native Laravel Integration** - Works seamlessly with Laravel Broadcasting
- **Horizontal Scaling** - Redis-backed for multi-server deployments
- **Developer-Friendly** - Same API as Pusher, easy migration
- **Cost-Effective** - No subscription fees for WebSocket services

## When to Use Laravel Reverb

### ✅ Use Reverb When:
- Building real-time features (chat, notifications, live updates)
- You want full control over your WebSocket infrastructure
- Cost reduction is important (vs. Pusher/Ably)
- You need to scale horizontally
- Building SaaS with many concurrent users

### ❌ Don't Use Reverb When:
- You have very simple, low-frequency updates (polling might suffice)
- Your hosting doesn't support long-running processes
- You need guaranteed message delivery (use queues instead)
- You're on shared hosting without daemon support

## Installation & Setup

### 1. Install Laravel Reverb

```bash
# Install Reverb
composer require laravel/reverb

# Install Reverb configuration
php artisan reverb:install

# Install Laravel Echo (frontend)
npm install --save-dev laravel-echo pusher-js
```

### 2. Configure Environment

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

# For production with Redis
REVERB_SERVER_HOST="0.0.0.0"
REVERB_SERVER_PORT=8080
```

### 3. Start Reverb Server

```bash
# Development
php artisan reverb:start

# Production with supervisor
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### 4. Frontend Setup (Laravel Echo)

In `resources/js/echo.js` or `bootstrap.js`:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

Update `.env`:

```env
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## Common Patterns

### Pattern 1: Real-Time Notifications

**Backend - Create Event:**

```php
<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public string $status
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.' . $this->order->user_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->number,
            'status' => $this->status,
            'message' => "Your order #{$this->order->number} is now {$this->status}",
        ];
    }
}
```

**Dispatch Event:**

```php
// In your controller or service
OrderStatusUpdated::dispatch($order, 'shipped');
```

**Frontend - Listen (Livewire):**

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class OrderNotifications extends Component
{
    public array $notifications = [];

    public function mount(): void
    {
        // Listen to private channel
        $this->dispatch('echo-private:orders.' . auth()->id(), 'order.status.updated');
    }

    #[On('echo-private:orders.{userId}:.order.status.updated')]
    public function handleOrderUpdate($event): void
    {
        $this->notifications[] = $event['message'];

        // Show toast notification
        $this->dispatch('notify', [
            'message' => $event['message'],
            'type' => 'success',
        ]);
    }

    public function render()
    {
        return view('livewire.order-notifications');
    }
}
```

**Frontend - Listen (Alpine.js):**

```javascript
// In your Blade file
<div x-data="orderNotifications()">
    <template x-for="notification in notifications">
        <div x-text="notification"></div>
    </template>
</div>

<script>
function orderNotifications() {
    return {
        notifications: [],

        init() {
            Echo.private(`orders.${userId}`)
                .listen('.order.status.updated', (e) => {
                    this.notifications.push(e.message);

                    // Show toast (using your preferred notification library)
                    Notification.success(e.message);
                });
        }
    }
}
</script>
```

### Pattern 2: Live Data Tables

**Livewire Component:**

```php
<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class OrdersTable extends Component
{
    use WithPagination;

    public function mount(): void
    {
        // Subscribe to public channel for new orders
        $this->dispatch('echo:orders', 'OrderCreated');
    }

    #[On('echo:orders:.OrderCreated')]
    public function handleNewOrder(): void
    {
        // Refresh the component
        $this->resetPage();
    }

    #[On('echo-private:orders.{userId}:.order.status.updated')]
    public function handleOrderUpdate($event): void
    {
        // Optionally update specific order in place
        // without full page refresh
    }

    public function render()
    {
        return view('livewire.orders-table', [
            'orders' => Order::latest()->paginate(10),
        ]);
    }
}
```

**Blade View:**

```blade
<div>
    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr wire:key="order-{{ $order->id }}">
                    <td>{{ $order->number }}</td>
                    <td>{{ $order->customer->name }}</td>
                    <td>
                        <span class="badge badge-{{ $order->status }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>${{ number_format($order->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $orders->links() }}
</div>
```

### Pattern 3: Presence Channels (Who's Online)

**Define Channel in routes/channels.php:**

```php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    if ($user->canAccessRoom($roomId)) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar_url,
        ];
    }
});
```

**Livewire Component:**

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class ChatRoom extends Component
{
    public string $roomId;
    public array $onlineUsers = [];
    public array $messages = [];

    public function mount(string $roomId): void
    {
        $this->roomId = $roomId;
    }

    public function getListeners(): array
    {
        return [
            "echo-presence:chat.{$this->roomId},here" => 'handleHere',
            "echo-presence:chat.{$this->roomId},joining" => 'handleJoining',
            "echo-presence:chat.{$this->roomId},leaving" => 'handleLeaving',
            "echo-presence:chat.{$this->roomId},.MessageSent" => 'handleMessage',
        ];
    }

    public function handleHere($users): void
    {
        $this->onlineUsers = $users;
    }

    public function handleJoining($user): void
    {
        $this->onlineUsers[] = $user;
    }

    public function handleLeaving($user): void
    {
        $this->onlineUsers = array_filter(
            $this->onlineUsers,
            fn($u) => $u['id'] !== $user['id']
        );
    }

    public function handleMessage($event): void
    {
        $this->messages[] = $event['message'];
    }

    public function sendMessage(string $content): void
    {
        $message = Message::create([
            'room_id' => $this->roomId,
            'user_id' => auth()->id(),
            'content' => $content,
        ]);

        MessageSent::dispatch($message);
    }

    public function render()
    {
        return view('livewire.chat-room');
    }
}
```

**Alpine.js Alternative:**

```javascript
<div x-data="chatRoom()">
    <!-- Online Users -->
    <div>
        <h3>Online (<span x-text="onlineUsers.length"></span>)</h3>
        <template x-for="user in onlineUsers" :key="user.id">
            <div>
                <img :src="user.avatar" :alt="user.name">
                <span x-text="user.name"></span>
            </div>
        </template>
    </div>

    <!-- Messages -->
    <div>
        <template x-for="message in messages" :key="message.id">
            <div x-text="message.content"></div>
        </template>
    </div>

    <!-- Send Message -->
    <form @submit.prevent="sendMessage">
        <input x-model="newMessage">
        <button type="submit">Send</button>
    </form>
</div>

<script>
function chatRoom() {
    return {
        onlineUsers: [],
        messages: [],
        newMessage: '',

        init() {
            Echo.join(`chat.${roomId}`)
                .here((users) => {
                    this.onlineUsers = users;
                })
                .joining((user) => {
                    this.onlineUsers.push(user);
                })
                .leaving((user) => {
                    this.onlineUsers = this.onlineUsers.filter(u => u.id !== user.id);
                })
                .listen('.MessageSent', (e) => {
                    this.messages.push(e.message);
                });
        },

        sendMessage() {
            fetch('/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    room_id: roomId,
                    content: this.newMessage
                })
            });

            this.newMessage = '';
        }
    }
}
</script>
```

### Pattern 4: Real-Time Dashboard Metrics

**Event:**

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MetricsUpdated implements ShouldBroadcast
{
    public function __construct(
        public array $metrics
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('metrics'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'revenue' => $this->metrics['revenue'],
            'orders' => $this->metrics['orders'],
            'users' => $this->metrics['users'],
            'timestamp' => now()->toISOString(),
        ];
    }
}
```

**Scheduled Job (every minute):**

```php
<?php

namespace App\Jobs;

use App\Events\MetricsUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateDashboardMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $metrics = [
            'revenue' => Order::today()->sum('total'),
            'orders' => Order::today()->count(),
            'users' => User::online()->count(),
        ];

        MetricsUpdated::dispatch($metrics);
    }
}
```

**Schedule in app/Console/Kernel.php:**

```php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new UpdateDashboardMetrics)->everyMinute();
}
```

**Livewire Dashboard:**

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class DashboardMetrics extends Component
{
    public float $revenue = 0;
    public int $orders = 0;
    public int $users = 0;

    public function mount(): void
    {
        $this->loadMetrics();
    }

    #[On('echo:metrics,.MetricsUpdated')]
    public function handleMetricsUpdate($event): void
    {
        $this->revenue = $event['revenue'];
        $this->orders = $event['orders'];
        $this->users = $event['users'];
    }

    protected function loadMetrics(): void
    {
        $this->revenue = Order::today()->sum('total');
        $this->orders = Order::today()->count();
        $this->users = User::online()->count();
    }

    public function render()
    {
        return view('livewire.dashboard-metrics');
    }
}
```

## Best Practices

### 1. Authorization

**Always authorize private channels:**

```php
// routes/channels.php
Broadcast::channel('orders.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

### 2. Queueing Broadcasts

**For better performance, queue broadcasts:**

```php
class OrderCreated implements ShouldBroadcastNow
{
    // Broadcasts immediately, bypassing the queue
}

// or

class OrderCreated implements ShouldBroadcast
{
    use Queueable;

    // Will be queued automatically
}
```

### 3. Error Handling

```javascript
Echo.private(`orders.${userId}`)
    .listen('.order.status.updated', (e) => {
        // Handle event
    })
    .error((error) => {
        console.error('Echo error:', error);
    });
```

### 4. Reconnection Handling

```javascript
// Detect connection state
Echo.connector.pusher.connection.bind('connected', () => {
    console.log('Connected to Reverb');
});

Echo.connector.pusher.connection.bind('disconnected', () => {
    console.log('Disconnected from Reverb');
});

Echo.connector.pusher.connection.bind('reconnecting', () => {
    console.log('Reconnecting to Reverb...');
});
```

### 5. Clean Up Listeners

```javascript
// In Livewire
public function destroy(): void
{
    Echo.leave(`orders.${this->userId}`);
}

// In Alpine
destroy() {
    Echo.leave(`chat.${roomId}`);
}
```

## Production Deployment

### Using Supervisor

Create `/etc/supervisor/conf.d/reverb.conf`:

```ini
[program:reverb]
command=php /var/www/html/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/var/www/html
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/reverb.log
```

### Nginx Configuration

```nginx
server {
    listen 443 ssl;
    server_name your-domain.com;

    # WebSocket proxy
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

### Horizontal Scaling with Redis

```env
REVERB_DRIVER=redis
REDIS_CLIENT=phpredis
REDIS_HOST=your-redis-host
```

## Performance Tips

1. **Use Redis for scaling** - Enable Redis driver for multi-server setups
2. **Queue broadcasts** - Don't block requests with broadcasts
3. **Limit event data** - Only broadcast necessary data
4. **Use presence wisely** - Presence channels are more expensive
5. **Monitor connections** - Use Laravel Pulse or custom monitoring

## Troubleshooting

### Connection Refused
- Check Reverb server is running: `php artisan reverb:start`
- Verify firewall allows connections to port 8080
- Check `.env` configuration matches frontend

### Events Not Broadcasting
- Verify event implements `ShouldBroadcast`
- Check broadcast driver is set to `reverb`
- Clear config cache: `php artisan config:clear`
- Check Laravel logs for errors

### Slow Performance
- Enable Redis for scaling
- Queue broadcasts instead of synchronous
- Reduce broadcast payload size
- Monitor server resources

---

**Laravel Reverb makes real-time features simple and cost-effective. Start small and scale as needed!**
