# Dashboard Starter Kit

A complete admin dashboard and analytics platform built with the TALL Stack. Perfect for admin panels, internal tools, and analytics dashboards.

## ✨ Features

- 📊 **Real-time Statistics**: Live metrics and KPIs
- 📈 **Charts & Graphs**: Interactive charts (Chart.js, ApexCharts)
- 📋 **Data Tables**: Sortable, filterable tables with export
- 👥 **User Management**: CRUD operations for users
- 🔔 **Notifications**: Real-time notifications system
- 📥 **Data Export**: Export to CSV, Excel, PDF
- 🎨 **Customizable Widgets**: Drag-and-drop dashboard builder
- 🔍 **Advanced Filters**: Date ranges, multi-select filters
- 📱 **Responsive**: Mobile-friendly admin interface
- 🌓 **Dark Mode**: Built-in dark mode support

## 🚀 Quick Start

```bash
# Copy starter files
cp -r starters/dashboard-starter/* your-project/

# Install dependencies
composer install
npm install

# Setup database
php artisan migrate:fresh --seed

# Start servers
npm run dev
php artisan serve
```

## 📦 Sample Data

After seeding:
- Admin user with full permissions
- Sample analytics data (last 30 days)
- User activity logs
- Mock metrics and statistics

**Admin Login:**
- Email: `admin@dashboard.com`
- Password: `password`

## 🎨 Key Components

### Dashboard Widgets
- `StatCard` - Metric cards with trends
- `RevenueChart` - Revenue over time
- `UsersChart` - User growth chart
- `ActivityFeed` - Recent activity list
- `TopProducts` - Best performing items
- `TaskList` - To-do list widget

### Data Management
- `UserTable` - User management with CRUD
- `DataTable` - Reusable data table component
- `FilterPanel` - Advanced filtering UI
- `ExportData` - Multi-format export
- `BulkActions` - Select and batch operations

### UI Components
- `Sidebar` - Collapsible navigation
- `TopBar` - Header with search and notifications
- `NotificationCenter` - Notification dropdown
- `Modal` - Reusable modal component
- `Toast` - Toast notifications

## 📊 Charts & Analytics

### Chart Types Included

**Line Charts:**
```php
<livewire:line-chart
    :data="$revenueData"
    :labels="$months"
    title="Monthly Revenue"
/>
```

**Bar Charts:**
```php
<livewire:bar-chart
    :data="$salesByCategory"
    title="Sales by Category"
/>
```

**Pie Charts:**
```php
<livewire:pie-chart
    :data="$usersByPlan"
    title="Users by Plan"
/>
```

**Area Charts:**
```php
<livewire:area-chart
    :data="$trafficData"
    title="Website Traffic"
/>
```

### Real-time Updates

Charts update automatically using Livewire polling:

```php
class RevenueChart extends Component
{
    #[Computed]
    public function chartData()
    {
        return Order::selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->get();
    }

    public function render()
    {
        return view('livewire.revenue-chart');
    }
}

// In view: wire:poll.30s refreshes every 30 seconds
```

## 🗄️ Database Schema

### Analytics Events
```
- id, event_name, event_data (JSON)
- user_id, session_id
- ip_address, user_agent
- created_at
```

### Dashboard Widgets
```
- id, user_id
- widget_type, position, size
- settings (JSON)
- timestamps
```

### User Activity Log
```
- id, user_id
- action, description
- properties (JSON)
- ip_address
- created_at
```

## 🔒 Authorization

```php
// Role-based dashboard access
Gate::define('view-dashboard', fn ($user) => $user->isAdmin());
Gate::define('view-analytics', fn ($user) => $user->hasRole(['admin', 'analyst']));
Gate::define('manage-users', fn ($user) => $user->isAdmin());
```

## 🧪 Testing

```bash
php artisan test --filter=DashboardTest
```

Tests include:
- Widget rendering
- Data table operations
- Export functionality
- Chart data accuracy
- Permission checks

## 💡 Customization

### Add Custom Widget
```php
// 1. Create widget component
php artisan make:livewire CustomWidget

// 2. Register in config/dashboard.php
'widgets' => [
    'custom_widget' => [
        'name' => 'Custom Widget',
        'component' => App\Livewire\CustomWidget::class,
        'default_size' => 'medium',
    ],
],
```

### Configure Metrics
```php
// config/dashboard.php
'metrics' => [
    'total_users' => [
        'label' => 'Total Users',
        'query' => fn() => User::count(),
        'format' => 'number',
        'trend' => true,
    ],
    'revenue' => [
        'label' => 'Revenue',
        'query' => fn() => Order::sum('total'),
        'format' => 'currency',
        'trend' => true,
    ],
],
```

### Customize Theme
```javascript
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eff6ff',
          // ... your brand colors
          900: '#1e3a8a',
        },
      },
    },
  },
}
```

## 📚 Usage Examples

### Track Event
```php
Analytics::track('user_login', [
    'user_id' => auth()->id(),
    'ip' => request()->ip(),
]);
```

### Create Custom Metric
```php
class ActiveUsersMetric
{
    public function calculate()
    {
        return User::where('last_login_at', '>=', now()->subDays(7))->count();
    }

    public function trend()
    {
        $current = $this->calculate();
        $previous = User::where('last_login_at', '>=', now()->subDays(14))
            ->where('last_login_at', '<', now()->subDays(7))
            ->count();

        return [
            'value' => $current,
            'change' => (($current - $previous) / $previous) * 100,
            'direction' => $current > $previous ? 'up' : 'down',
        ];
    }
}
```

### Export Data
```php
// Automatic export from any data table
<button wire:click="export('csv')">Export CSV</button>
<button wire:click="export('xlsx')">Export Excel</button>
<button wire:click="export('pdf')">Export PDF</button>
```

## 🎨 UI Features

### Dark Mode
Toggle between light/dark mode:
```blade
<x-theme-toggle />
```

### Responsive Sidebar
Collapsible on mobile, persistent on desktop:
```blade
<x-layouts.dashboard>
    <x-slot:sidebar>
        <!-- Navigation items -->
    </x-slot>

    <!-- Page content -->
</x-layouts.dashboard>
```

### Notifications
Real-time notifications:
```php
// Send notification
auth()->user()->notify(new DashboardNotification([
    'title' => 'New Order',
    'message' => 'You have a new order #1234',
    'link' => '/orders/1234',
]));

// Display in UI (automatic)
<livewire:notification-center />
```

## 📊 Performance

- **Caching**: Metrics cached for 5 minutes
- **Lazy Loading**: Charts load on scroll
- **Pagination**: Tables paginated (25 items/page)
- **Query Optimization**: Eager loading, indexes
- **Asset Optimization**: Minified CSS/JS

## 🚀 Going Live

### Production Optimization
```bash
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize assets
npm run build

# Set up queue for background jobs
php artisan queue:work --daemon
```

### Environment Configuration
```env
# Production settings
APP_ENV=production
APP_DEBUG=false

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Queue
QUEUE_CONNECTION=redis

# Analytics
ANALYTICS_ENABLED=true
```

### Launch Checklist
- [ ] Configure user roles
- [ ] Set up data retention policies
- [ ] Test all charts with real data
- [ ] Configure export limits
- [ ] Enable rate limiting
- [ ] Set up monitoring
- [ ] Test mobile responsiveness
- [ ] Configure backup schedule
- [ ] Test notification delivery
- [ ] Enable SSL

See `/tall-deploy` for complete deployment guide.

---

**Happy Dashboarding! 📊**
