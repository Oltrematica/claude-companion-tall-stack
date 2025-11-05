# Action Pattern

Single-purpose classes that perform one specific action. Perfect for complex operations that don't fit in controllers or services.

## When to Use

✅ **USE Actions when:**
- Operation has a single, well-defined responsibility
- Logic is complex but self-contained
- Operation is reused across different contexts
- You want highly testable, focused code
- Building command-style operations

❌ **DON'T use Actions when:**
- Simple CRUD operations (use controllers)
- Multiple related operations (use Service)
- Logic belongs in model (use Model methods)

## Basic Structure

```php
<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUser
{
    /**
     * Execute the action
     */
    public function execute(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Or use __invoke to make class callable
     */
    public function __invoke(array $data): User
    {
        return $this->execute($data);
    }
}
```

## Invokable Actions

```php
<?php

namespace App\Actions;

use App\Models\Order;
use App\Notifications\OrderConfirmation;

class ProcessOrder
{
    public function __invoke(Order $order): void
    {
        // 1. Validate stock
        foreach ($order->items as $item) {
            if ($item->product->stock < $item->quantity) {
                throw new \Exception("Insufficient stock for {$item->product->name}");
            }
        }

        // 2. Reduce stock
        foreach ($order->items as $item) {
            $item->product->decrement('stock', $item->quantity);
        }

        // 3. Update order status
        $order->update(['status' => 'processing']);

        // 4. Send notification
        $order->user->notify(new OrderConfirmation($order));
    }
}
```

## With Dependencies

```php
<?php

namespace App\Actions;

use App\Models\Post;
use App\Services\ImageService;
use App\Services\SeoService;

class PublishPost
{
    public function __construct(
        private ImageService $imageService,
        private SeoService $seoService
    ) {}

    public function __invoke(Post $post): Post
    {
        // 1. Optimize featured image
        if ($post->featured_image) {
            $this->imageService->optimize($post->featured_image);
        }

        // 2. Generate SEO meta
        $this->seoService->generateMeta($post);

        // 3. Update post
        $post->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        // 4. Clear cache
        cache()->tags(['posts'])->flush();

        return $post;
    }
}
```

## Chainable Actions

```php
<?php

namespace App\Actions;

use App\Models\User;

class RegisterUser
{
    private array $data;
    private ?User $user = null;

    public function withData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function create(): self
    {
        $this->user = User::create([
            'name' => $this->data['name'],
            'email' => $this->data['email'],
            'password' => Hash::make($this->data['password']),
        ]);

        return $this;
    }

    public function assignRole(string $role): self
    {
        $this->user->assignRole($role);
        return $this;
    }

    public function sendWelcomeEmail(): self
    {
        $this->user->sendWelcomeNotification();
        return $this;
    }

    public function get(): User
    {
        return $this->user;
    }
}

// Usage
$user = app(RegisterUser::class)
    ->withData($data)
    ->create()
    ->assignRole('user')
    ->sendWelcomeEmail()
    ->get();
```

## Result Object Pattern

```php
<?php

namespace App\Actions;

class ActionResult
{
    public function __construct(
        public readonly bool $success,
        public readonly mixed $data = null,
        public readonly ?string $message = null,
        public readonly array $errors = []
    ) {}

    public static function success(mixed $data = null, ?string $message = null): self
    {
        return new self(true, $data, $message);
    }

    public static function failure(string $message, array $errors = []): self
    {
        return new self(false, null, $message, $errors);
    }
}

class CreateInvoice
{
    public function __invoke(Order $order): ActionResult
    {
        try {
            $invoice = Invoice::create([
                'order_id' => $order->id,
                'amount' => $order->total,
                'due_date' => now()->addDays(30),
            ]);

            return ActionResult::success(
                $invoice,
                'Invoice created successfully'
            );
        } catch (\Exception $e) {
            return ActionResult::failure(
                'Failed to create invoice',
                ['error' => $e->getMessage()]
            );
        }
    }
}

// Usage
$result = app(CreateInvoice::class)($order);

if ($result->success) {
    $invoice = $result->data;
    // Success handling
} else {
    // Error handling
    logger()->error($result->message, $result->errors);
}
```

## Usage in Controllers

```php
<?php

namespace App\Http\Controllers;

use App\Actions\CreateUser;
use App\Http\Requests\RegisterRequest;

class RegisterController extends Controller
{
    public function store(RegisterRequest $request, CreateUser $createUser)
    {
        $user = $createUser($request->validated());

        auth()->login($user);

        return redirect()->route('dashboard');
    }
}
```

## Usage in Livewire

```php
<?php

namespace App\Livewire;

use App\Actions\ProcessOrder;
use Livewire\Component;

class CheckoutForm extends Component
{
    public function checkout(ProcessOrder $processOrder)
    {
        $this->validate();

        try {
            $processOrder($this->order);

            session()->flash('message', 'Order placed successfully!');
            $this->redirectRoute('orders.show', $this->order);
        } catch (\Exception $e) {
            $this->addError('checkout', $e->getMessage());
        }
    }
}
```

## Usage in Jobs

```php
<?php

namespace App\Jobs;

use App\Actions\GenerateInvoice;
use App\Models\Order;

class ProcessOrderJob
{
    public function __construct(
        private Order $order
    ) {}

    public function handle(GenerateInvoice $generateInvoice): void
    {
        $generateInvoice($this->order);
    }
}
```

## Complex Example: Multi-Step Action

```php
<?php

namespace App\Actions;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionCreated;
use Illuminate\Support\Facades\DB;

class CreateSubscription
{
    public function __invoke(
        User $user,
        string $plan,
        string $paymentMethodId
    ): Subscription {
        return DB::transaction(function () use ($user, $plan, $paymentMethodId) {
            // 1. Create Stripe customer if needed
            if (!$user->stripe_customer_id) {
                $customer = $this->createStripeCustomer($user);
                $user->update(['stripe_customer_id' => $customer->id]);
            }

            // 2. Attach payment method
            $this->attachPaymentMethod($user, $paymentMethodId);

            // 3. Create subscription
            $subscription = $user->subscriptions()->create([
                'plan' => $plan,
                'status' => 'active',
                'trial_ends_at' => now()->addDays(14),
            ]);

            // 4. Create Stripe subscription
            $stripeSubscription = $this->createStripeSubscription(
                $user,
                $plan
            );

            $subscription->update([
                'stripe_subscription_id' => $stripeSubscription->id,
            ]);

            // 5. Send notification
            $user->notify(new SubscriptionCreated($subscription));

            return $subscription;
        });
    }

    private function createStripeCustomer(User $user)
    {
        return \Stripe\Customer::create([
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }

    private function attachPaymentMethod(User $user, string $paymentMethodId): void
    {
        \Stripe\PaymentMethod::retrieve($paymentMethodId)->attach([
            'customer' => $user->stripe_customer_id,
        ]);
    }

    private function createStripeSubscription(User $user, string $plan)
    {
        return \Stripe\Subscription::create([
            'customer' => $user->stripe_customer_id,
            'items' => [['price' => $plan]],
            'trial_period_days' => 14,
        ]);
    }
}
```

## Testing Actions

```php
<?php

use App\Actions\CreateUser;
use App\Models\User;

test('creates user with hashed password', function () {
    $action = new CreateUser();

    $user = $action->execute([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('John Doe');
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

test('processes order and reduces stock', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $order = Order::factory()
        ->hasItems(1, ['product_id' => $product->id, 'quantity' => 3])
        ->create();

    $action = new ProcessOrder();
    $action($order);

    expect($product->fresh()->stock)->toBe(7);
    expect($order->fresh()->status)->toBe('processing');
});
```

## Best Practices

### ✅ DO
- Keep actions single-purpose
- Use dependency injection
- Make actions invokable with `__invoke()`
- Return meaningful results
- Use database transactions for multi-step operations
- Write comprehensive tests

### ❌ DON'T
- Don't put multiple responsibilities in one action
- Don't handle HTTP responses directly
- Don't make actions stateful (use parameters)
- Don't put validation in actions (validate before calling)

## When to Use Each Pattern

- **Controller**: HTTP request handling only
- **Service**: Multiple related operations
- **Action**: Single, focused operation
- **Repository**: Data access abstraction
- **Model**: Data representation and simple queries
