# Pest 3.x Architecture Testing

## What is Architecture Testing?

**Architecture tests** ensure your codebase follows defined architectural rules and constraints. Pest 3.x includes a powerful architecture testing DSL that helps maintain code quality and consistency.

### Benefits
- **Enforce conventions** - Automatically check naming conventions
- **Prevent violations** - Stop bad patterns before they merge
- **Document architecture** - Tests serve as living documentation
- **Refactoring safety** - Catch breaking changes early
- **Team alignment** - Everyone follows the same rules

## Installation & Setup

### Install Pest 3.x

```bash
# Install Pest
composer require pestphp/pest --dev --with-all-dependencies

# Install Pest plugin for Laravel
composer require pestphp/pest-plugin-laravel --dev

# Install Pest plugin for Architecture
composer require pestphp/pest-plugin-arch --dev

# Initialize Pest
php artisan pest:install
```

### Configuration

Edit `tests/Pest.php`:

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Unit');

// Add architecture expectations
expect()->extend('toBeReadonly', function () {
    return $this->toHaveMethod('__construct')
        ->and($this)->not->toHaveMethod('set');
});
```

## Common Architecture Tests

### Test 1: Layer Dependencies

Create `tests/Architecture/LayerTest.php`:

```php
<?php

use PHPUnit\Framework\Attributes\Group;

#[Group('architecture')]
test('controllers do not use models directly')
    ->expect('App\Http\Controllers')
    ->not->toUse([
        'App\Models',
    ]);

test('controllers use services')
    ->expect('App\Http\Controllers')
    ->toOnlyUse([
        'App\Services',
        'App\Http\Requests',
        'App\Http\Resources',
        'Illuminate',
        'Inertia',
        'Livewire',
    ]);

test('services use repositories')
    ->expect('App\Services')
    ->toUse('App\Repositories');

test('repositories use models')
    ->expect('App\Repositories')
    ->toUse('App\Models');

test('models do not use controllers')
    ->expect('App\Models')
    ->not->toUse([
        'App\Http\Controllers',
        'App\Services',
    ]);

test('livewire components use services')
    ->expect('App\Livewire')
    ->toUse('App\Services')
    ->not->toUse('App\Models');
```

### Test 2: Naming Conventions

```php
<?php

test('controllers have Controller suffix')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');

test('services have Service suffix')
    ->expect('App\Services')
    ->toHaveSuffix('Service');

test('repositories have Repository suffix')
    ->expect('App\Repositories')
    ->toHaveSuffix('Repository');

test('livewire components have correct namespace')
    ->expect('App\Livewire')
    ->toBeClasses()
    ->toExtend('Livewire\Component');

test('requests have Request suffix')
    ->expect('App\Http\Requests')
    ->toHaveSuffix('Request')
    ->toExtend('Illuminate\Foundation\Http\FormRequest');

test('resources have Resource suffix')
    ->expect('App\Http\Resources')
    ->toHaveSuffix('Resource')
    ->toExtend('Illuminate\Http\Resources\Json\JsonResource');

test('jobs have Job suffix or use queued action pattern')
    ->expect('App\Jobs')
    ->toHaveSuffix('Job')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

test('events have Event suffix')
    ->expect('App\Events')
    ->toHaveSuffix('Event');

test('listeners have Listener suffix')
    ->expect('App\Listeners')
    ->toHaveSuffix('Listener');
```

### Test 3: Class Structure

```php
<?php

test('models are final or abstract')
    ->expect('App\Models')
    ->toBeFinal()
    ->ignoring('App\Models\User'); // Allow inheritance for User

test('services are readonly')
    ->expect('App\Services')
    ->classes()
    ->toBeReadonly();

test('DTOs are readonly')
    ->expect('App\DataTransferObjects')
    ->classes()
    ->toBeReadonly();

test('enums have correct structure')
    ->expect('App\Enums')
    ->toBeEnums()
    ->toOnlyBeUsedIn([
        'App\Models',
        'App\Services',
        'App\Http\Controllers',
    ]);

test('interfaces are suffixed with Interface')
    ->expect('App\Contracts')
    ->toBeInterfaces()
    ->toHaveSuffix('Interface');

test('traits have Trait suffix')
    ->expect('App\Traits')
    ->toBeTraits()
    ->toHaveSuffix('Trait');
```

### Test 4: Security Rules

```php
<?php

test('models use mass assignment protection')
    ->expect('App\Models')
    ->toHaveMethod('getFillable')
    ->or->toHaveMethod('getGuarded');

test('no debug code in production')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsedIn('App');

test('no env() in codebase except config')
    ->expect('env')
    ->not->toBeUsedIn('App')
    ->ignoring('config');

test('no die() or exit() in application code')
    ->expect(['die', 'exit'])
    ->not->toBeUsedIn('App');

test('controllers authorize requests')
    ->expect('App\Http\Controllers')
    ->toUse([
        'Illuminate\Foundation\Auth\Access\AuthorizesRequests',
    ]);
```

### Test 5: Dependency Rules

```php
<?php

test('application does not use deprecated Laravel features')
    ->expect('App')
    ->not->toUse([
        'Illuminate\Support\Facades\Input',
        'Illuminate\Support\Facades\Session', // Use request()->session()
    ]);

test('use specific Eloquent methods over generic query builder')
    ->expect('App\Models')
    ->not->toUse('DB');

test('jobs are queueable')
    ->expect('App\Jobs')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue')
    ->toUse('Illuminate\Bus\Queueable');

test('events are broadcastable when needed')
    ->expect('App\Events')
    ->when(fn ($class) => str_contains($class, 'Broadcast'))
    ->toImplement('Illuminate\Contracts\Broadcasting\ShouldBroadcast');
```

### Test 6: TALL Stack Specific

```php
<?php

test('livewire components follow naming')
    ->expect('App\Livewire')
    ->toExtend('Livewire\Component')
    ->toHaveMethod('render');

test('livewire components use proper authorization')
    ->expect('App\Livewire')
    ->toUse([
        'Illuminate\Foundation\Auth\Access\AuthorizesRequests',
    ])
    ->or->toHaveMethod('authorize');

test('filament resources extend base resource')
    ->expect('App\Filament\Resources')
    ->toExtend('Filament\Resources\Resource')
    ->toHaveSuffix('Resource');

test('filament resources have required methods')
    ->expect('App\Filament\Resources')
    ->toHaveMethod('form')
    ->toHaveMethod('table')
    ->toHaveMethod('getPages');

test('alpine components are in correct directory')
    ->expect('resources/js/components')
    ->toBeFiles();
```

### Test 7: Testing Best Practices

```php
<?php

test('feature tests use RefreshDatabase')
    ->expect('Tests\Feature')
    ->toUse('Illuminate\Foundation\Testing\RefreshDatabase');

test('tests do not use real external services')
    ->expect('Tests')
    ->not->toUse([
        'GuzzleHttp\Client',
        'Illuminate\Support\Facades\Http',
    ])
    ->ignoring([
        'Tests\Integration', // Allow in integration tests
    ]);

test('tests use factories for models')
    ->expect('Tests')
    ->toUse('Database\Factories');
```

## Advanced Patterns

### Custom Architecture Rules

```php
<?php

use PHPUnit\Framework\Attributes\Test;

#[Test]
function custom_services_must_implement_contract(): void
{
    expect('App\Services')
        ->classes()
        ->each(function ($class) {
            $contractName = 'App\Contracts\\' . class_basename($class->name) . 'Interface';
            return expect($class->name)->toImplement($contractName);
        });
}

#[Test]
function repositories_must_have_corresponding_interface(): void
{
    expect('App\Repositories')
        ->classes()
        ->each(function ($class) {
            $interfaceName = str_replace('Repositories', 'Contracts', $class->name) . 'Interface';
            return expect($interfaceName)->toBeInterface();
        });
}
```

### Conditional Rules

```php
<?php

test('admin controllers require admin middleware')
    ->expect('App\Http\Controllers\Admin')
    ->toHaveMethod('__construct')
    ->and(function ($class) {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        $file = file_get_contents($reflection->getFileName());

        return expect($file)->toContain('middleware(\'admin\')');
    });
```

### Domain-Driven Design Rules

```php
<?php

test('domain models only use domain services')
    ->expect('App\Domain\Orders\Models')
    ->toOnlyUse([
        'App\Domain\Orders\Services',
        'App\Domain\Orders\ValueObjects',
        'Illuminate\Database',
    ]);

test('bounded contexts are isolated')
    ->expect('App\Domain\Orders')
    ->not->toUse([
        'App\Domain\Payments',
        'App\Domain\Shipping',
    ]);

test('value objects are immutable')
    ->expect('App\Domain\*\ValueObjects')
    ->toBeReadonly()
    ->not->toHaveMethod('set*');
```

## Running Architecture Tests

```bash
# Run all architecture tests
php artisan test --filter=architecture

# Run specific test file
php artisan test tests/Architecture/LayerTest.php

# Run with coverage
php artisan test --coverage --min=80

# Run in parallel
php artisan test --parallel

# Generate architecture report
php artisan test --filter=architecture --testdox
```

## CI/CD Integration

### GitHub Actions

```yaml
name: Architecture Tests

on: [push, pull_request]

jobs:
  architecture:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
          extensions: mbstring, xml, ctype, iconv, intl, pdo_sqlite
          coverage: none

      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run Architecture Tests
        run: php artisan test --filter=architecture --stop-on-failure
```

### Pre-commit Hook

Create `.git/hooks/pre-commit`:

```bash
#!/bin/bash

echo "Running architecture tests..."
php artisan test --filter=architecture --stop-on-failure

if [ $? -ne 0 ]; then
    echo "Architecture tests failed. Commit aborted."
    exit 1
fi
```

## Best Practices

1. **Start Simple** - Begin with basic naming and layer rules
2. **Iterate** - Add rules as patterns emerge
3. **Document Exceptions** - Comment why rules are ignored
4. **Run in CI** - Fail builds on violations
5. **Team Agreement** - Discuss rules with the team first
6. **Granular Tests** - One concern per test
7. **Use Groups** - Organize with `#[Group('architecture')]`

## Common Pitfalls

### ❌ Too Strict Too Soon
```php
// Don't start with this
test('everything must be readonly')->expect('App')->toBeReadonly();
```

### ✅ Gradual Adoption
```php
// Start here
test('new services are readonly')->expect('App\Services\V2')->toBeReadonly();
```

### ❌ Ignoring Too Much
```php
test('controllers dont use models')
    ->expect('App\Http\Controllers')
    ->not->toUse('App\Models')
    ->ignoring([/* 20 controllers */]); // Red flag!
```

### ✅ Refactor Instead
```php
// Fix the architecture, don't ignore it
test('controllers dont use models')->expect('App\Http\Controllers')->not->toUse('App\Models');
```

## Example: Complete TALL Stack Architecture

Create `tests/Architecture/TallStackTest.php`:

```php
<?php

use PHPUnit\Framework\Attributes\Group;

#[Group('architecture')]
#[Group('tall-stack')]
describe('TALL Stack Architecture', function () {
    describe('Livewire Layer', function () {
        test('components extend base component')
            ->expect('App\Livewire')
            ->toExtend('Livewire\Component');

        test('components use services not models')
            ->expect('App\Livewire')
            ->toUse('App\Services')
            ->not->toUse('App\Models');

        test('components have render method')
            ->expect('App\Livewire')
            ->toHaveMethod('render');
    });

    describe('Service Layer', function () {
        test('services are readonly')
            ->expect('App\Services')
            ->toBeReadonly();

        test('services use repositories')
            ->expect('App\Services')
            ->toUse('App\Repositories');
    });

    describe('Repository Layer', function () {
        test('repositories implement contracts')
            ->expect('App\Repositories')
            ->toImplement('App\Contracts\*Interface');

        test('repositories use models')
            ->expect('App\Repositories')
            ->toUse('App\Models');
    });

    describe('Model Layer', function () {
        test('models extend base model')
            ->expect('App\Models')
            ->toExtend('Illuminate\Database\Eloquent\Model');

        test('models use mass assignment protection')
            ->expect('App\Models')
            ->toHaveProperty('fillable')
            ->or->toHaveProperty('guarded');
    });
});
```

## Summary

Architecture testing with Pest 3.x helps you:
- **Maintain clean architecture**
- **Enforce team conventions**
- **Prevent technical debt**
- **Document architectural decisions**
- **Catch violations early**

Start with basic rules and expand as your application grows!
