---
description: Setup performance monitoring and error tracking for TALL Stack applications
---

# TALL Stack Performance Monitoring

You are an expert in setting up monitoring, logging, and error tracking for TALL Stack (Tailwind, Alpine.js, Laravel, Livewire) applications. Your goal is to implement comprehensive observability for production applications.

## Your Task

Guide the user through setting up complete monitoring infrastructure:

1. **Gather Requirements**
   Ask the user:
   - Is this for development, staging, or production?
   - What's your budget? (free tools, paid services, self-hosted)
   - What do you want to monitor? (errors, performance, logs, uptime)
   - Do you have existing monitoring tools?
   - Expected traffic volume?
   - Compliance requirements? (data residency, GDPR)

2. **Monitoring Stack Options**

   ### A. Development Environment
   - Laravel Telescope (queries, requests, exceptions)
   - Laravel Debugbar (in-app debugging)
   - Clockwork (browser-based profiling)

   ### B. Production Environment - Free/Self-hosted
   - Laravel Telescope (careful with performance)
   - Laravel Pulse (official monitoring)
   - Log files + analysis tools
   - Self-hosted Sentry

   ### C. Production Environment - Paid Services
   - Sentry (error tracking)
   - Bugsnag (error monitoring)
   - New Relic (APM)
   - Datadog (full observability)
   - Laravel Forge monitoring
   - Oh Dear (uptime + performance)

3. **Implementation: Laravel Telescope**

   ### Installation
   ```bash
   composer require laravel/telescope --dev
   php artisan telescope:install
   php artisan migrate
   ```

   ### Configuration
   ```php
   // config/telescope.php
   <?php

   use Laravel\Telescope\Watchers;

   return [
       'enabled' => env('TELESCOPE_ENABLED', true),

       'storage' => [
           'database' => [
               'connection' => env('DB_CONNECTION', 'mysql'),
               'chunk' => 1000,
           ],
       ],

       'path' => env('TELESCOPE_PATH', 'telescope'),

       'watchers' => [
           Watchers\CacheWatcher::class => env('TELESCOPE_CACHE_WATCHER', true),

           Watchers\CommandWatcher::class => [
               'enabled' => env('TELESCOPE_COMMAND_WATCHER', true),
               'ignore' => ['schedule:run'],
           ],

           Watchers\DumpWatcher::class => env('TELESCOPE_DUMP_WATCHER', true),

           Watchers\EventWatcher::class => env('TELESCOPE_EVENT_WATCHER', true),

           Watchers\ExceptionWatcher::class => env('TELESCOPE_EXCEPTION_WATCHER', true),

           Watchers\JobWatcher::class => env('TELESCOPE_JOB_WATCHER', true),

           Watchers\LogWatcher::class => [
               'enabled' => env('TELESCOPE_LOG_WATCHER', true),
               'level' => 'error',
           ],

           Watchers\MailWatcher::class => env('TELESCOPE_MAIL_WATCHER', true),

           Watchers\ModelWatcher::class => [
               'enabled' => env('TELESCOPE_MODEL_WATCHER', true),
               'events' => ['created', 'updated', 'deleted'],
               'hydrations' => true,
           ],

           Watchers\NotificationWatcher::class => env('TELESCOPE_NOTIFICATION_WATCHER', true),

           Watchers\QueryWatcher::class => [
               'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
               'ignore_packages' => true,
               'slow' => 100, // milliseconds
           ],

           Watchers\RedisWatcher::class => env('TELESCOPE_REDIS_WATCHER', true),

           Watchers\RequestWatcher::class => [
               'enabled' => env('TELESCOPE_REQUEST_WATCHER', true),
               'size_limit' => env('TELESCOPE_RESPONSE_SIZE_LIMIT', 64),
           ],

           Watchers\ScheduleWatcher::class => env('TELESCOPE_SCHEDULE_WATCHER', true),

           Watchers\ViewWatcher::class => env('TELESCOPE_VIEW_WATCHER', true),
       ],
   ];
   ```

   ### Restrict Access in Production
   ```php
   // app/Providers/TelescopeServiceProvider.php
   <?php

   namespace App\Providers;

   use Laravel\Telescope\IncomingEntry;
   use Laravel\Telescope\Telescope;
   use Laravel\Telescope\TelescopeApplicationServiceProvider;

   class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
   {
       public function register(): void
       {
           // Only enable Telescope in non-production or for admins
           Telescope::night();

           $this->hideSensitiveRequestDetails();

           Telescope::filter(function (IncomingEntry $entry) {
               if ($this->app->environment('local')) {
                   return true;
               }

               return $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
           });
       }

       protected function gate(): void
       {
           Gate::define('viewTelescope', function ($user) {
               return in_array($user->email, [
                   'admin@example.com',
               ]) || $user->is_admin;
           });
       }

       protected function hideSensitiveRequestDetails(): void
       {
           if ($this->app->environment('local')) {
               return;
           }

           Telescope::hideRequestParameters(['_token']);
           Telescope::hideRequestHeaders([
               'cookie',
               'x-csrf-token',
               'x-xsrf-token',
           ]);
       }
   }
   ```

4. **Implementation: Laravel Pulse**

   ### Installation
   ```bash
   composer require laravel/pulse
   php artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"
   php artisan migrate
   ```

   ### Configuration
   ```php
   // config/pulse.php
   <?php

   return [
       'domain' => env('PULSE_DOMAIN'),
       'path' => env('PULSE_PATH', 'pulse'),

       'storage' => [
           'driver' => env('PULSE_STORAGE_DRIVER', 'database'),
       ],

       'recorders' => [
           // Requests
           \Laravel\Pulse\Recorders\Requests::class => [
               'enabled' => env('PULSE_REQUESTS_ENABLED', true),
               'sample_rate' => env('PULSE_REQUESTS_SAMPLE_RATE', 1),
               'ignore' => [
                   '/pulse',
                   '/telescope',
               ],
           ],

           // Slow Queries
           \Laravel\Pulse\Recorders\SlowQueries::class => [
               'enabled' => env('PULSE_SLOW_QUERIES_ENABLED', true),
               'threshold' => env('PULSE_SLOW_QUERIES_THRESHOLD', 1000),
               'sample_rate' => env('PULSE_SLOW_QUERIES_SAMPLE_RATE', 1),
           ],

           // Exceptions
           \Laravel\Pulse\Recorders\Exceptions::class => [
               'enabled' => env('PULSE_EXCEPTIONS_ENABLED', true),
               'sample_rate' => env('PULSE_EXCEPTIONS_SAMPLE_RATE', 1),
               'ignore' => [
                   \Illuminate\Http\Exceptions\ThrottleRequestsException::class,
               ],
           ],

           // Queues
           \Laravel\Pulse\Recorders\Queues::class => [
               'enabled' => env('PULSE_QUEUES_ENABLED', true),
               'sample_rate' => env('PULSE_QUEUES_SAMPLE_RATE', 1),
           ],

           // Servers (CPU, Memory)
           \Laravel\Pulse\Recorders\Servers::class => [
               'enabled' => env('PULSE_SERVERS_ENABLED', true),
           ],

           // User Requests
           \Laravel\Pulse\Recorders\UserRequests::class => [
               'enabled' => env('PULSE_USER_REQUESTS_ENABLED', true),
               'sample_rate' => env('PULSE_USER_REQUESTS_SAMPLE_RATE', 1),
           ],

           // User Jobs
           \Laravel\Pulse\Recorders\UserJobs::class => [
               'enabled' => env('PULSE_USER_JOBS_ENABLED', true),
               'sample_rate' => env('PULSE_USER_JOBS_SAMPLE_RATE', 1),
           ],
       ],
   ];
   ```

   ### Access Control
   ```php
   // app/Providers/PulseServiceProvider.php
   Gate::define('viewPulse', function ($user) {
       return $user->is_admin;
   });
   ```

5. **Implementation: Sentry (Error Tracking)**

   ### Installation
   ```bash
   composer require sentry/sentry-laravel
   php artisan sentry:publish --dsn=your-dsn-here
   ```

   ### Configuration
   ```php
   // config/sentry.php
   <?php

   return [
       'dsn' => env('SENTRY_LARAVEL_DSN'),

       'environment' => env('APP_ENV'),

       'breadcrumbs' => [
           'logs' => true,
           'cache' => true,
           'livewire' => true,
       ],

       'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.2),

       'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE', 0.2),

       'send_default_pii' => false,

       'before_send' => function (\Sentry\Event $event): ?\Sentry\Event {
           // Don't send certain exceptions
           if (app()->environment('local')) {
               return null;
           }

           return $event;
       },
   ];
   ```

   ### Custom Context
   ```php
   // app/Providers/AppServiceProvider.php
   <?php

   public function boot()
   {
       if (app()->bound('sentry')) {
           app('sentry')->configureScope(function (\Sentry\State\Scope $scope): void {
               if (auth()->check()) {
                   $scope->setUser([
                       'id' => auth()->id(),
                       'email' => auth()->user()->email,
                       'username' => auth()->user()->name,
                   ]);
               }

               $scope->setTag('app.version', config('app.version'));
           });
       }
   }
   ```

6. **Custom Livewire Performance Monitoring**

   Create a trait to monitor Livewire component performance:

   ```php
   <?php

   namespace App\Livewire\Concerns;

   use Illuminate\Support\Facades\Log;

   trait MonitorsPerformance
   {
       protected $performanceStart;

       public function bootMonitorsPerformance()
       {
           $this->performanceStart = microtime(true);
       }

       public function dehydrate()
       {
           $duration = (microtime(true) - $this->performanceStart) * 1000;

           // Log slow components
           if ($duration > 1000) { // 1 second
               Log::warning('Slow Livewire component', [
                   'component' => static::class,
                   'duration' => round($duration, 2) . 'ms',
                   'method' => request()->header('X-Livewire-Method'),
                   'url' => request()->url(),
               ]);
           }

           // Send to monitoring service
           if (config('app.env') === 'production') {
               app('metrics')->timing('livewire.render', $duration, [
                   'component' => class_basename(static::class),
               ]);
           }
       }
   }
   ```

   Use in components:
   ```php
   <?php

   class ProductList extends Component
   {
       use MonitorsPerformance;

       // ... component code
   }
   ```

7. **Query Performance Monitoring**

   Create a service provider to log slow queries:

   ```php
   <?php

   namespace App\Providers;

   use Illuminate\Database\Events\QueryExecuted;
   use Illuminate\Support\Facades\DB;
   use Illuminate\Support\Facades\Log;
   use Illuminate\Support\ServiceProvider;

   class QueryMonitoringServiceProvider extends ServiceProvider
   {
       public function boot(): void
       {
           if (config('app.env') === 'production') {
               DB::listen(function (QueryExecuted $query) {
                   if ($query->time > 1000) { // Slower than 1 second
                       Log::warning('Slow query detected', [
                           'sql' => $query->sql,
                           'bindings' => $query->bindings,
                           'time' => $query->time . 'ms',
                           'connection' => $query->connectionName,
                       ]);
                   }
               });
           }
       }
   }
   ```

8. **Health Check Endpoint**

   ```php
   <?php

   namespace App\Http\Controllers;

   use Illuminate\Support\Facades\DB;
   use Illuminate\Support\Facades\Cache;

   class HealthCheckController extends Controller
   {
       public function __invoke()
       {
           $checks = [
               'app' => $this->checkApp(),
               'database' => $this->checkDatabase(),
               'cache' => $this->checkCache(),
               'queue' => $this->checkQueue(),
           ];

           $healthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');

           return response()->json([
               'status' => $healthy ? 'healthy' : 'unhealthy',
               'timestamp' => now()->toISOString(),
               'checks' => $checks,
           ], $healthy ? 200 : 503);
       }

       protected function checkApp(): array
       {
           return [
               'status' => 'ok',
               'version' => config('app.version'),
               'environment' => config('app.env'),
           ];
       }

       protected function checkDatabase(): array
       {
           try {
               DB::connection()->getPdo();
               return ['status' => 'ok'];
           } catch (\Exception $e) {
               return ['status' => 'error', 'message' => $e->getMessage()];
           }
       }

       protected function checkCache(): array
       {
           try {
               Cache::put('health_check', true, 10);
               $value = Cache::get('health_check');
               return ['status' => $value ? 'ok' : 'error'];
           } catch (\Exception $e) {
               return ['status' => 'error', 'message' => $e->getMessage()];
           }
       }

       protected function checkQueue(): array
       {
           try {
               $size = \Illuminate\Support\Facades\Queue::size();
               return [
                   'status' => 'ok',
                   'pending_jobs' => $size,
               ];
           } catch (\Exception $e) {
               return ['status' => 'error', 'message' => $e->getMessage()];
           }
       }
   }
   ```

   Route:
   ```php
   Route::get('/health', HealthCheckController::class);
   ```

9. **Frontend Error Tracking**

   Add to your layout:

   ```blade
   @production
   <script>
       window.addEventListener('error', function(event) {
           fetch('/api/log-frontend-error', {
               method: 'POST',
               headers: {
                   'Content-Type': 'application/json',
                   'X-CSRF-TOKEN': '{{ csrf_token() }}'
               },
               body: JSON.stringify({
                   message: event.message,
                   source: event.filename,
                   line: event.lineno,
                   column: event.colno,
                   stack: event.error?.stack,
                   url: window.location.href,
                   userAgent: navigator.userAgent
               })
           });
       });
   </script>
   @endproduction
   ```

10. **Alerting Configuration**

    Create alert thresholds:

    ```php
    // config/monitoring.php
    <?php

    return [
        'alerts' => [
            'error_rate' => [
                'threshold' => 10, // errors per minute
                'channels' => ['slack', 'email'],
            ],
            'slow_queries' => [
                'threshold' => 5, // queries over 1s per minute
                'channels' => ['slack'],
            ],
            'failed_jobs' => [
                'threshold' => 5, // failed jobs per hour
                'channels' => ['slack', 'email'],
            ],
            'high_memory' => [
                'threshold' => 90, // percent
                'channels' => ['slack'],
            ],
        ],
    ];
    ```

## Monitoring Checklist

- [ ] Laravel Telescope installed (dev)
- [ ] Laravel Pulse installed (production)
- [ ] Sentry or error tracking service configured
- [ ] Slow query monitoring enabled
- [ ] Livewire performance monitoring
- [ ] Health check endpoint created
- [ ] Frontend error tracking
- [ ] Alerting configured
- [ ] Log rotation configured
- [ ] Uptime monitoring (Oh Dear, Pingdom)
- [ ] Backup monitoring
- [ ] SSL certificate monitoring

## Best Practices

1. **Don't Monitor Everything**: Focus on what matters
2. **Set Appropriate Thresholds**: Avoid alert fatigue
3. **Regular Reviews**: Check monitoring data weekly
4. **Optimize Storage**: Don't keep too much historical data
5. **Secure Access**: Monitoring dashboards need authentication
6. **Performance Impact**: Monitor the monitors
7. **Privacy**: Don't log sensitive user data

## Start

Ask the user:
1. What environment is this for? (development, production)
2. Budget for monitoring tools? (free, paid, self-hosted)
3. What's most important to monitor? (errors, performance, uptime)
4. Do you have existing monitoring tools?
5. Expected traffic and data volume?

Then proceed with the appropriate monitoring setup.
