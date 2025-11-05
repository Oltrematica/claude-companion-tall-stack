# SaaS Starter Kit

A complete multi-tenant SaaS application foundation built with the TALL Stack. Perfect for building subscription-based applications with team management.

## ✨ Features

### Core Features
- 🏢 **Multi-tenancy**: Isolated data per organization
- 👥 **Team Management**: Organizations, teams, and member roles
- 💳 **Subscription Billing**: Stripe/Paddle integration
- 🔐 **Authentication**: Email/password, 2FA, social login
- 🎫 **Authorization**: Role-based permissions (RBAC)
- 📧 **Team Invitations**: Email invites with role assignment
- 🔑 **API Tokens**: Per-team API access tokens
- 📊 **Activity Log**: Track user actions
- ⚙️ **Settings**: User and team settings management

### Technical Features
- ✅ Laravel 10+ with Livewire 3
- ✅ Tailwind CSS 3 with dark mode
- ✅ Alpine.js for interactivity
- ✅ Full test coverage
- ✅ Database optimized with indexes
- ✅ Queue-based email sending
- ✅ Redis caching
- ✅ API rate limiting

## 🚀 Quick Start

### 1. Installation

```bash
# Copy starter to your Laravel project
cp -r starters/saas-starter/* your-project/

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations with sample data
php artisan migrate:fresh --seed

# Build assets
npm run dev
```

### 2. Configure Billing

Choose your payment provider:

**For Stripe:**
```bash
# Install Cashier
composer require laravel/cashier

# Add to .env
STRIPE_KEY=your-stripe-key
STRIPE_SECRET=your-stripe-secret
```

**For Paddle:**
```bash
# Install Cashier Paddle
composer require laravel/cashier-paddle

# Add to .env
PADDLE_VENDOR_ID=your-vendor-id
PADDLE_VENDOR_AUTH_CODE=your-auth-code
```

### 3. Sample Accounts

After seeding, you can login with:

**Admin Account:**
- Email: `admin@example.com`
- Password: `password`

**Regular User:**
- Email: `user@example.com`
- Password: `password`

## 📦 Included Components

### Livewire Components

#### Organization Management
- `CreateOrganization` - Create new organizations
- `OrganizationSettings` - Manage organization settings
- `OrganizationSwitcher` - Switch between organizations
- `DeleteOrganization` - Delete organization

#### Team Management
- `ManageTeamMembers` - Add/remove team members
- `TeamMemberRoles` - Manage member roles
- `TeamInvitations` - Send and manage invitations
- `TeamSettings` - Configure team settings

#### Subscription Management
- `SubscriptionPlans` - Display available plans
- `ManageSubscription` - Upgrade/downgrade subscription
- `BillingPortal` - Access billing portal
- `PaymentMethod` - Update payment method

#### User Management
- `UserProfile` - Edit user profile
- `UserSecurity` - Password, 2FA settings
- `UserSessions` - Active sessions management
- `UserApiTokens` - Generate API tokens

#### Activity Log
- `ActivityFeed` - Display recent activities
- `ActivityFilters` - Filter by type, date, user

## 🗄️ Database Schema

### Organizations
```php
- id
- name
- slug
- owner_id
- subscription_status
- trial_ends_at
- settings (json)
- timestamps
```

### Teams
```php
- id
- organization_id
- name
- description
- settings (json)
- timestamps
```

### Team Members
```php
- id
- team_id
- user_id
- role (owner, admin, member)
- invited_by
- joined_at
- timestamps
```

### Team Invitations
```php
- id
- team_id
- email
- role
- token
- expires_at
- timestamps
```

### Subscriptions (via Cashier)
Managed by Laravel Cashier

### Activity Log
```php
- id
- organization_id
- user_id
- type
- description
- properties (json)
- timestamps
```

## 🎨 Customization

### 1. Subscription Plans

Edit `config/saas.php`:

```php
'plans' => [
    'starter' => [
        'name' => 'Starter',
        'price' => 9.99,
        'stripe_id' => 'price_starter',
        'features' => [
            'users' => 5,
            'projects' => 10,
            'storage' => '10 GB',
        ],
    ],
    'professional' => [
        'name' => 'Professional',
        'price' => 29.99,
        'stripe_id' => 'price_pro',
        'features' => [
            'users' => 25,
            'projects' => 'unlimited',
            'storage' => '100 GB',
        ],
    ],
],
```

### 2. User Roles and Permissions

Edit `app/Enums/Role.php`:

```php
enum Role: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case MEMBER = 'member';
    case VIEWER = 'viewer'; // Add custom roles

    public function permissions(): array
    {
        return match($this) {
            self::OWNER => ['*'], // All permissions
            self::ADMIN => ['manage_team', 'manage_projects'],
            self::MEMBER => ['view', 'create', 'edit'],
            self::VIEWER => ['view'],
        };
    }
}
```

### 3. Branding

Update branding in `resources/views/components/`:
- Logo: `components/logo.blade.php`
- Colors: `tailwind.config.js`
- Fonts: `resources/css/app.css`

### 4. Email Templates

Customize emails in `resources/views/emails/`:
- `team-invitation.blade.php`
- `subscription-created.blade.php`
- `subscription-cancelled.blade.php`
- `trial-ending.blade.php`

## 🔒 Security Features

### Multi-tenancy Isolation
```php
// Automatically scoped to current organization
class Post extends Model
{
    use BelongsToOrganization;

    // All queries automatically filtered
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new OrganizationScope);
    }
}
```

### Authorization
```php
// Policy-based authorization
class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $user->isMemberOf($project->organization);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasRole(['owner', 'admin'], $project->organization);
    }
}
```

### Rate Limiting
```php
// API routes are rate limited per team
Route::middleware(['auth:sanctum', 'throttle:60,1,team'])
    ->group(function () {
        // API routes
    });
```

## 🧪 Testing

Run the test suite:

```bash
# All tests
php artisan test

# Specific feature
php artisan test --filter=SubscriptionTest

# With coverage
php artisan test --coverage
```

Example tests included:
- Organization CRUD operations
- Team member management
- Subscription lifecycle
- Permission enforcement
- Data isolation (multi-tenancy)

## 📊 Usage Examples

### Creating an Organization
```php
$organization = Organization::create([
    'name' => 'Acme Corp',
    'owner_id' => auth()->id(),
]);

// Add current user as owner
$organization->members()->create([
    'user_id' => auth()->id(),
    'role' => Role::OWNER,
]);
```

### Inviting Team Members
```php
use App\Models\TeamInvitation;
use App\Notifications\TeamInvitationNotification;

$invitation = TeamInvitation::create([
    'team_id' => $team->id,
    'email' => 'newmember@example.com',
    'role' => Role::MEMBER,
    'token' => Str::random(32),
    'expires_at' => now()->addDays(7),
]);

Notification::route('mail', $invitation->email)
    ->notify(new TeamInvitationNotification($invitation));
```

### Checking Subscription
```php
if ($organization->subscribed('default')) {
    // Organization has active subscription
}

if ($organization->onTrial()) {
    // Organization is on trial
}

if ($organization->onGracePeriod()) {
    // Subscription cancelled but still active
}
```

### Managing Permissions
```php
// Check if user can perform action
if (auth()->user()->can('update', $project)) {
    // Allowed
}

// Check role
if (auth()->user()->hasRole('admin', $organization)) {
    // User is admin
}

// Check specific permission
if (auth()->user()->hasPermission('manage_billing', $organization)) {
    // Has permission
}
```

## 🚀 Deployment

See the main deployment guide with `/tall-deploy` or refer to `DEPLOYMENT.md`.

### Environment Variables

Required for production:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your_database

# Redis (for caching and queues)
REDIS_HOST=your-redis-host

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your-mail-host

# Stripe/Paddle
STRIPE_KEY=your-stripe-key
STRIPE_SECRET=your-stripe-secret

# Queue
QUEUE_CONNECTION=redis
```

## 📚 Additional Resources

- [Laravel Cashier Documentation](https://laravel.com/docs/billing)
- [Multi-tenancy Package](https://spatie.be/docs/laravel-multitenancy)
- [Permission Package](https://spatie.be/docs/laravel-permission)

## 💡 Common Customizations

### Add Custom Features

1. **Custom Organization Settings**
```php
// In Organization model
protected $casts = [
    'settings' => 'array',
];

// Usage
$organization->settings = [
    'timezone' => 'America/New_York',
    'date_format' => 'Y-m-d',
    'custom_domain' => 'acme.com',
];
```

2. **Webhooks for Subscriptions**
```php
// In routes/api.php
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

// Handle subscription events
public function handleSubscriptionUpdated($payload)
{
    $organization = Organization::findOrFail($payload['data']['object']['metadata']['organization_id']);

    $organization->update([
        'subscription_status' => $payload['data']['object']['status'],
    ]);
}
```

3. **Usage-based Billing**
```php
// Track usage
$organization->recordUsage('api_calls', 100);

// Bill for usage
if ($organization->usage('api_calls') > $organization->plan_limit) {
    $organization->charge('additional_api_calls', [
        'quantity' => $organization->usage('api_calls') - $organization->plan_limit,
    ]);
}
```

## 🆘 Troubleshooting

### Common Issues

**Issue**: "Organization not found"
- Ensure user is logged in
- Check organization middleware is applied
- Verify user has organization membership

**Issue**: Subscription not updating
- Check webhook URL is configured in Stripe/Paddle
- Verify webhook secret is correct
- Check queue workers are running: `php artisan queue:work`

**Issue**: Permissions not working
- Clear cache: `php artisan cache:clear`
- Check role is correctly assigned
- Verify policy is registered in `AuthServiceProvider`

## 📞 Support

Need help? Ask Claude Code:
```
Help me customize the SaaS starter for [your specific use case]
```

Or check the main documentation and community resources.

---

**Happy Building! 🚀**
