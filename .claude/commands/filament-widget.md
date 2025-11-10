---
description: Create custom dashboard widgets for Filament 4
---

# Create Filament Widget

You are tasked with creating a **Filament 4.x Widget** for displaying data on dashboards.

## Information Gathering

Ask the user for:

1. **Widget Type**:
   - Stats widget (KPIs, metrics)
   - Chart widget (line, bar, pie, doughnut)
   - Table widget (data table)
   - Custom widget (fully custom HTML)

2. **Widget Details**:
   - Widget name
   - Data source (model, API, calculation)
   - Refresh rate (if real-time)
   - Column span (full, half, third)

## Widget Types

### 1. Stats Widget

```bash
php artisan make:filament-widget StatsOverview --stats-overview
```

Implementation:

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    // Update every 30 seconds
    protected static ?string $pollingInterval = '30s';

    // Sort order on dashboard
    protected static ?int $sort = 1;

    // Column span (1-12, or 'full')
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Revenue', '$' . number_format($this->getTotalRevenue(), 2))
                ->description('32k increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('New Customers', User::where('created_at', '>=', now()->subDays(7))->count())
                ->description('7% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Active Orders', Order::where('status', 'processing')->count())
                ->description('3 decrease')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Low Stock Items', Product::where('stock', '<=', 10)->count())
                ->description('Needs attention')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning')
                ->url(route('filament.admin.resources.products.index', [
                    'tableFilters' => ['low_stock' => ['value' => true]],
                ])),
        ];
    }

    protected function getTotalRevenue(): float
    {
        return Order::where('status', 'completed')
            ->sum('total');
    }
}
```

### 2. Chart Widget

```bash
php artisan make:filament-widget SalesChart --chart
```

Implementation - Line Chart:

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Sales';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    // Filter options
    public ?string $filter = 'year';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $data = match ($activeFilter) {
            'today' => Trend::model(Order::class)
                ->between(
                    start: now()->startOfDay(),
                    end: now()->endOfDay(),
                )
                ->perHour()
                ->sum('total'),
            'week' => Trend::model(Order::class)
                ->between(
                    start: now()->subWeek(),
                    end: now(),
                )
                ->perDay()
                ->sum('total'),
            'month' => Trend::model(Order::class)
                ->between(
                    start: now()->subMonth(),
                    end: now(),
                )
                ->perDay()
                ->sum('total'),
            'year' => Trend::model(Order::class)
                ->between(
                    start: now()->startOfYear(),
                    end: now()->endOfYear(),
                )
                ->perMonth()
                ->sum('total'),
        };

        return [
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
```

Bar Chart Example:

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

class TopSellingProducts extends ChartWidget
{
    protected static ?string $heading = 'Top 10 Products';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $topProducts = Product::withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $topProducts->pluck('order_items_count')->toArray(),
                    'backgroundColor' => [
                        '#f59e0b',
                        '#10b981',
                        '#3b82f6',
                        '#8b5cf6',
                        '#ec4899',
                        '#6366f1',
                        '#14b8a6',
                        '#f97316',
                        '#06b6d4',
                        '#84cc16',
                    ],
                ],
            ],
            'labels' => $topProducts->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
```

Pie/Doughnut Chart Example:

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderStatusDistribution extends ChartWidget
{
    protected static ?string $heading = 'Order Status Distribution';

    protected function getData(): array
    {
        $statusCounts = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'datasets' => [
                [
                    'data' => array_values($statusCounts),
                    'backgroundColor' => [
                        '#f59e0b', // pending
                        '#3b82f6', // processing
                        '#10b981', // completed
                        '#ef4444', // cancelled
                    ],
                ],
            ],
            'labels' => array_map('ucfirst', array_keys($statusCounts)),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // or 'pie'
    }
}
```

### 3. Table Widget

```bash
php artisan make:filament-widget LatestOrders --table
```

Implementation:

```php
<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Order $record): string =>
                        OrderResource::getUrl('view', ['record' => $record])
                    ),
            ])
            ->paginated(false);
    }
}
```

### 4. Custom Widget

```bash
php artisan make:filament-widget CustomOverview
```

Create Blade view `resources/views/filament/widgets/custom-overview.blade.php`:

```blade
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($this->stats as $stat)
                <div class="relative overflow-hidden rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-icon
                                :name="$stat['icon']"
                                class="h-8 w-8 text-gray-400"
                            />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ $stat['label'] }}
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ $stat['value'] }}
                                    </div>
                                    @if (isset($stat['change']))
                                        <div @class([
                                            'ml-2 flex items-baseline text-sm font-semibold',
                                            'text-green-600' => $stat['change'] > 0,
                                            'text-red-600' => $stat['change'] < 0,
                                        ])>
                                            {{ $stat['change'] > 0 ? '+' : '' }}{{ $stat['change'] }}%
                                        </div>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
```

Widget class:

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class CustomOverview extends Widget
{
    protected static string $view = 'filament.widgets.custom-overview';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    // Cache for 5 minutes
    public function getStats(): array
    {
        return Cache::remember('dashboard-stats', 300, function () {
            return [
                [
                    'label' => 'Total Revenue',
                    'value' => '$' . number_format($this->getTotalRevenue(), 2),
                    'icon' => 'heroicon-o-currency-dollar',
                    'change' => 12.5,
                ],
                [
                    'label' => 'Active Users',
                    'value' => User::where('is_active', true)->count(),
                    'icon' => 'heroicon-o-users',
                    'change' => 5.3,
                ],
                [
                    'label' => 'Total Products',
                    'value' => Product::count(),
                    'icon' => 'heroicon-o-shopping-bag',
                ],
                [
                    'label' => 'Pending Orders',
                    'value' => Order::where('status', 'pending')->count(),
                    'icon' => 'heroicon-o-shopping-cart',
                    'change' => -2.1,
                ],
            ];
        });
    }

    protected function getTotalRevenue(): float
    {
        return Order::where('status', 'completed')->sum('total');
    }
}
```

## Advanced Features

### Real-Time Updates

```php
protected static ?string $pollingInterval = '10s'; // Update every 10 seconds
```

### Conditional Visibility

```php
public static function canView(): bool
{
    return auth()->user()->hasRole('admin');
}
```

### Interactive Widgets

```php
public function refreshWidget(): void
{
    // Clear cache or reload data
    Cache::forget('dashboard-stats');
    $this->dispatch('$refresh');
}
```

Add button in blade:

```blade
<x-filament::button wire:click="refreshWidget">
    Refresh
</x-filament::button>
```

### Filters

```php
public ?string $filter = 'today';

protected function getFilters(): ?array
{
    return [
        'today' => 'Today',
        'week' => 'This Week',
        'month' => 'This Month',
        'year' => 'This Year',
    ];
}
```

## Install Dependencies

For advanced charts with Trend:

```bash
composer require flowframe/laravel-trend
```

## Register Widget

Widgets are auto-discovered if placed in `app/Filament/Widgets/`.

Or manually register in `AdminPanelProvider`:

```php
->widgets([
    Widgets\AccountWidget::class,
    \App\Filament\Widgets\StatsOverview::class,
    \App\Filament\Widgets\SalesChart::class,
])
```

## Testing

```php
<?php

namespace Tests\Feature\Filament\Widgets;

use App\Filament\Widgets\StatsOverview;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class StatsOverviewTest extends TestCase
{
    public function test_widget_renders_successfully(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(StatsOverview::class)
            ->assertSuccessful();
    }

    public function test_widget_displays_correct_stats(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(StatsOverview::class)
            ->assertSee('Total Revenue')
            ->assertSee('New Customers')
            ->assertSee('Active Orders');
    }
}
```

## Performance Optimization

```php
// Cache expensive queries
public function getStats(): array
{
    return Cache::remember('stats-' . auth()->id(), 300, function () {
        return [
            // Your stats
        ];
    });
}

// Use lazy loading
protected static bool $isLazy = true;

// Disable polling when not needed
protected static ?string $pollingInterval = null;
```

## Common Use Cases

### 1. Revenue Widget with Comparison

```php
Stat::make('Revenue', '$' . number_format($currentRevenue, 2))
    ->description(
        ($change >= 0 ? '+' : '') . number_format($change, 1) . '% from last month'
    )
    ->descriptionIcon($change >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
    ->color($change >= 0 ? 'success' : 'danger')
    ->chart($chartData);
```

### 2. Multi-Series Chart

```php
protected function getData(): array
{
    return [
        'datasets' => [
            [
                'label' => 'Revenue',
                'data' => [65, 59, 80, 81, 56, 55, 40],
                'borderColor' => 'rgb(59, 130, 246)',
            ],
            [
                'label' => 'Costs',
                'data' => [28, 48, 40, 19, 86, 27, 90],
                'borderColor' => 'rgb(239, 68, 68)',
            ],
        ],
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
    ];
}
```

### 3. Clickable Stats

```php
Stat::make('Low Stock Products', Product::where('stock', '<=', 10)->count())
    ->url(route('filament.admin.resources.products.index', [
        'tableFilters' => ['low_stock' => ['isActive' => true]],
    ]))
    ->openUrlInNewTab(false);
```

## Completion Checklist

- [ ] Widget created
- [ ] Data source connected
- [ ] Styling applied
- [ ] Performance optimized
- [ ] Tests written
- [ ] Registered in panel
- [ ] Polling configured (if needed)
- [ ] Permissions set (if needed)

---

**Widget created successfully!** View it on your dashboard at `/admin`.
