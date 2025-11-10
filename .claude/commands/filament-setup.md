---
description: Complete Filament 4 setup and configuration wizard
---

# Filament 4 Setup Wizard

You are tasked with setting up **Filament 4.x** in a Laravel application. This is a complete, step-by-step installation and configuration process.

## Step 1: Pre-Installation Checks

First, verify the project meets requirements:

```bash
# Check PHP version (must be 8.3+)
php -v

# Check Laravel version (must be 11+ or 12+)
php artisan --version

# Check if Livewire is installed
composer show livewire/livewire
```

If requirements are not met, inform the user and stop.

## Step 2: Install Filament

```bash
# Install Filament Panel Builder
composer require filament/filament:"^4.0" -W

# Install Filament
php artisan filament:install --panels
```

During installation, you'll be prompted for:
- **Panel ID**: Default is "admin" (you can customize)
- **Create user**: Yes (create an admin user)

## Step 3: Create Admin User

If you chose to create a user during installation:

```bash
# The installer will prompt for:
# - Name
# - Email
# - Password
```

Or create manually later:

```bash
php artisan make:filament-user
```

## Step 4: Publish Configuration (Optional)

```bash
# Only if customization is needed
php artisan vendor:publish --tag=filament-config
php artisan vendor:publish --tag=filament-views
```

## Step 5: Configure Panel Provider

Open `app/Providers/Filament/AdminPanelProvider.php` and customize:

```php
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->brandName('My Application')
            ->favicon(asset('images/favicon.png'))
            ->darkMode(true)
            ->font('Inter')
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
```

## Step 6: Enhanced Customization (Optional)

### 6.1 Custom Theme

```bash
# Create custom theme
php artisan make:filament-theme admin
```

This creates: `resources/css/filament/admin/theme.css`

Edit the file:

```css
@import '/vendor/filament/filament/resources/css/theme.css';

@config 'tailwind.config.js';

/* Your custom styles here */
.fi-sidebar {
    @apply bg-gradient-to-b from-gray-900 to-gray-800;
}
```

Update `tailwind.config.js`:

```js
export default {
    content: [
        './resources/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: colors.amber,
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
```

Build assets:

```bash
npm install
npm run build
```

### 6.2 Multi-Panel Setup (Optional)

Create additional panels for different user types:

```bash
# Create a "client" panel
php artisan make:filament-panel client

# Create a "vendor" panel
php artisan make:filament-panel vendor
```

## Step 7: Install Essential Plugins

### 7.1 Spatie Media Library (for file uploads)

```bash
composer require filament/spatie-laravel-media-library-plugin:"^4.0" -W
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan migrate
```

### 7.2 Filament Shield (for roles & permissions)

```bash
composer require bezhansalleh/filament-shield
php artisan vendor:publish --tag="filament-shield-config"
php artisan shield:install

# Generate permissions for existing resources
php artisan shield:generate --all
```

### 7.3 Filament Breezy (for user profile & 2FA)

```bash
composer require jeffgreco13/filament-breezy
php artisan vendor:publish --tag="filament-breezy-config"
```

Add to `AdminPanelProvider`:

```php
use Jeffgreco13\FilamentBreezy\BreezyCore;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->plugins([
            BreezyCore::make()
                ->myProfile(
                    shouldRegisterUserMenu: true,
                    shouldRegisterNavigation: false,
                    hasAvatars: true,
                    slug: 'my-profile'
                )
                ->enableTwoFactorAuthentication(),
        ]);
}
```

## Step 8: Create First Resource

```bash
# Generate a resource for an existing model
php artisan make:filament-resource Product

# Or generate with model and migration
php artisan make:filament-resource Product --generate

# Generate with relation manager
php artisan make:filament-resource Product --view
```

## Step 9: Testing Setup

Add to `tests/Feature/Filament/`:

```php
<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\ProductResource;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_render_list_page()
    {
        $this->get(ProductResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_render_create_page()
    {
        $this->get(ProductResource::getUrl('create'))
            ->assertSuccessful();
    }
}
```

## Step 10: Database Seeding for Demo

Create a seeder for demo data:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FilamentDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create demo data
        // Add your models here
    }
}
```

Run seeder:

```bash
php artisan db:seed --class=FilamentDemoSeeder
```

## Step 11: Security Hardening

### 11.1 Update Policies

Generate policies for resources:

```bash
php artisan make:policy ProductPolicy --model=Product
```

Register in `AuthServiceProvider`:

```php
protected $policies = [
    Product::class => ProductPolicy::class,
];
```

### 11.2 Configure CORS (if using API)

Update `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie', 'admin/*'],
```

### 11.3 Rate Limiting

Add to `app/Http/Kernel.php`:

```php
'api' => [
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
'filament' => [
    'throttle:60,1',
],
```

## Step 12: Production Optimization

```bash
# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache icons (Filament specific)
php artisan icons:cache
```

## Step 13: Documentation

Create a `docs/FILAMENT.md` file documenting:

1. **Access URLs**
   - Admin panel: `https://your-domain.com/admin`
   - Login: `https://your-domain.com/admin/login`

2. **Default Credentials**
   - Email: (from seeder)
   - Password: (from seeder)

3. **Resources Created**
   - List all Filament resources
   - Their purposes

4. **Plugins Installed**
   - List plugins and their purposes

5. **Customizations Made**
   - Theme changes
   - Configuration modifications

## Completion Checklist

Present this checklist to the user:

- [ ] Filament 4.x installed via Composer
- [ ] Admin panel configured
- [ ] Admin user created
- [ ] Panel provider customized
- [ ] Custom theme created (if requested)
- [ ] Essential plugins installed
- [ ] At least one resource created
- [ ] Tests written for resources
- [ ] Policies configured
- [ ] Security hardening applied
- [ ] Production optimizations documented
- [ ] Documentation created

## Post-Setup Recommendations

1. **Explore the Admin Panel**
   - Visit `/admin` to see your panel
   - Test CRUD operations
   - Check responsive design

2. **Read Filament Documentation**
   - [Official Docs](https://filamentphp.com/docs)
   - [Plugin Directory](https://filamentphp.com/plugins)

3. **Join the Community**
   - [Discord Server](https://filamentphp.com/discord)
   - [GitHub Discussions](https://github.com/filamentphp/filament/discussions)

4. **Next Steps**
   - Create more resources with `/filament-resource`
   - Add custom widgets with `/filament-widget`
   - Customize theme further

## Troubleshooting

### Issue: 404 on /admin

**Solution:**
```bash
php artisan route:clear
php artisan config:clear
```

### Issue: Styles not loading

**Solution:**
```bash
npm run build
php artisan view:clear
```

### Issue: Cannot login

**Solution:**
- Verify user exists in database
- Check `guards` configuration in `config/auth.php`
- Ensure email is verified (if required)

### Issue: Slow performance

**Solution:**
- Enable query caching
- Add database indexes
- Use `->deferLoading()` on heavy tables
- Optimize images

## Support

If you encounter issues:

1. Check Filament documentation
2. Search GitHub issues
3. Ask on Discord
4. Use `/tall-security` to audit setup
5. Use `/tall-optimize` for performance tuning

---

**Setup Complete!** 🎉

Your Filament 4 admin panel is ready. Access it at `/admin` and start building!
