# Service Layer Pattern

Use this pattern to encapsulate complex business logic and keep controllers/Livewire components thin and focused.

## When to Use

✅ **USE Services when:**
- Logic involves multiple models
- Operation requires multiple coordinated steps
- Logic needs to be reused across multiple controllers/components
- Logic is complex and deserves isolated testing
- Interacting with external services (APIs, file systems, etc.)

❌ **DON'T use Services when:**
- Simple CRUD operations
- Direct queries on a single model
- Logic belongs in the model itself (use Model methods/scopes)

## Basic Structure

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Create a new user with all related setup
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // 1. Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // 2. Setup default preferences
            $user->preferences()->create([
                'theme' => 'light',
                'notifications_enabled' => true,
            ]);

            // 3. Assign default role
            $user->assignRole('user');

            // 4. Send welcome email
            $user->sendWelcomeNotification();

            // 5. Dispatch event
            event(new UserCreated($user));

            return $user;
        });
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'bio' => $data['bio'] ?? null,
        ]);

        if (isset($data['avatar'])) {
            $this->updateAvatar($user, $data['avatar']);
        }

        return $user->fresh();
    }

    /**
     * Private helper method
     */
    private function updateAvatar(User $user, $avatar): void
    {
        if ($user->avatar_path) {
            Storage::delete($user->avatar_path);
        }

        $path = $avatar->store('avatars', 'public');
        $user->update(['avatar_path' => $path]);
    }
}
```

## With Dependency Injection

```php
<?php

namespace App\Services;

use App\Models\Post;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Cache;

class PostService
{
    public function __construct(
        private NotificationService $notificationService,
        private CacheService $cacheService
    ) {}

    public function publishPost(Post $post): Post
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Clear cache
        $this->cacheService->invalidatePostCache();

        // Notify subscribers
        $this->notificationService->notifySubscribers($post);

        return $post;
    }
}
```

## Usage in Livewire

```php
<?php

namespace App\Livewire;

use App\Services\UserService;
use Livewire\Component;

class UserRegistration extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';

    public function register(UserService $userService)
    {
        $validated = $this->validate([
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        try {
            $user = $userService->createUser($validated);
            auth()->login($user);
            $this->redirectRoute('dashboard');
        } catch (\Exception $e) {
            $this->addError('registration', 'Registration failed.');
        }
    }
}
```

## Testing Services

```php
<?php

use App\Services\UserService;
use App\Models\User;

test('creates user with all setup', function () {
    $service = app(UserService::class);

    $user = $service->createUser([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->preferences)->not->toBeNull();
    expect($user->hasRole('user'))->toBeTrue();
});
```

## Best Practices

### ✅ DO
- Use type hints for parameters and return types
- Use database transactions for multi-step operations
- Handle exceptions appropriately
- Return useful data (updated models)
- Use dependency injection

### ❌ DON'T
- Don't put validation in services (use Form Requests)
- Don't handle HTTP responses in services
- Don't make services too large (split into smaller, focused services)
- Don't use services for simple operations
