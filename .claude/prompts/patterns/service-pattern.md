# Service Layer Pattern

---
Ultimo aggiornamento: 2025-01-05
Versione: 1.0.0
---

## Contesto

Nel TALL Stack usiamo il **Service Layer Pattern** per incapsulare la logica business complessa e mantenere i controller/componenti Livewire snelli e focalizzati.

## Quando Usare Services

✅ **USA Services quando:**
- La logica coinvolge più di un model
- L'operazione richiede più step coordinati
- Devi riutilizzare la stessa logica in più punti
- La logica è complessa e merita di essere testata isolatamente
- Devi interagire con servizi esterni (API, file system, etc.)

❌ **NON usare Services quando:**
- Operazioni CRUD semplici
- Query dirette su un singolo model
- Logica che appartiene al model stesso (usa Model methods/scopes)

## Struttura Directory

```
app/
├── Services/
│   ├── UserService.php
│   ├── PostService.php
│   ├── NotificationService.php
│   └── Payment/
│       ├── PaymentService.php
│       └── StripeService.php
```

## Template Base

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Events\UserCreated;
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
            // 1. Create the user
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
     * Deactivate user account
     */
    public function deactivateUser(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            // Cancel subscriptions
            $user->subscriptions()->active()->each->cancel();

            // Revoke API tokens
            $user->tokens()->delete();

            // Soft delete
            $user->delete();

            return true;
        });
    }

    /**
     * Private helper method
     */
    private function updateAvatar(User $user, $avatar): void
    {
        // Delete old avatar
        if ($user->avatar_path) {
            Storage::delete($user->avatar_path);
        }

        // Store new avatar
        $path = $avatar->store('avatars', 'public');
        $user->update(['avatar_path' => $path]);
    }
}
```

## Con Dependency Injection

```php
<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\PostRepository;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Cache;

class PostService
{
    public function __construct(
        private PostRepository $postRepository,
        private NotificationService $notificationService
    ) {}

    public function publishPost(Post $post): Post
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Clear cache
        Cache::tags(['posts'])->flush();

        // Notify subscribers
        $this->notificationService->notifySubscribers($post);

        return $post;
    }

    public function getPopularPosts(int $limit = 10): Collection
    {
        return Cache::remember(
            "popular_posts_{$limit}",
            now()->addHour(),
            fn () => $this->postRepository->getMostViewed($limit)
        );
    }
}
```

## Uso nei Livewire Components

```php
<?php

namespace App\Livewire;

use App\Services\UserService;
use Livewire\Component;
use Livewire\Attributes\Rule;

class UserRegistration extends Component
{
    #[Rule('required|string|min:3')]
    public string $name = '';

    #[Rule('required|email|unique:users')]
    public string $email = '';

    #[Rule('required|min:8')]
    public string $password = '';

    public function register(UserService $userService)
    {
        $validated = $this->validate();

        try {
            $user = $userService->createUser($validated);

            auth()->login($user);

            session()->flash('message', 'Welcome! Your account has been created.');

            $this->redirectRoute('dashboard');
        } catch (\Exception $e) {
            $this->addError('registration', 'An error occurred during registration.');
        }
    }

    public function render()
    {
        return view('livewire.user-registration');
    }
}
```

## Uso nei Controllers

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PostService;
use App\Http\Requests\CreatePostRequest;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    public function store(CreatePostRequest $request): JsonResponse
    {
        $post = $this->postService->createPost($request->validated());

        return response()->json([
            'data' => $post,
            'message' => 'Post created successfully'
        ], 201);
    }

    public function publish(Post $post): JsonResponse
    {
        $this->authorize('publish', $post);

        $post = $this->postService->publishPost($post);

        return response()->json([
            'data' => $post,
            'message' => 'Post published successfully'
        ]);
    }
}
```

## Testing Services

```php
<?php

use App\Services\UserService;
use App\Models\User;
use App\Events\UserCreated;
use Illuminate\Support\Facades\Event;

test('creates user with all setup', function () {
    Event::fake();

    $service = app(UserService::class);

    $user = $service->createUser([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('John Doe');
    expect($user->preferences)->not->toBeNull();
    expect($user->hasRole('user'))->toBeTrue();

    Event::assertDispatched(UserCreated::class);
});

test('deactivates user account completely', function () {
    $user = User::factory()
        ->hasSubscriptions(2)
        ->hasTokens(3)
        ->create();

    $service = app(UserService::class);

    $result = $service->deactivateUser($user);

    expect($result)->toBeTrue();
    expect($user->fresh()->deleted_at)->not->toBeNull();
    expect($user->subscriptions()->active()->count())->toBe(0);
    expect($user->tokens()->count())->toBe(0);
});
```

## Service con Interface (Opzionale)

Per progetti grandi o quando serve swap implementations:

```php
<?php

namespace App\Contracts;

use App\Models\User;

interface UserServiceInterface
{
    public function createUser(array $data): User;
    public function updateProfile(User $user, array $data): User;
    public function deactivateUser(User $user): bool;
}
```

```php
<?php

namespace App\Services;

use App\Contracts\UserServiceInterface;

class UserService implements UserServiceInterface
{
    // Implementation
}
```

Register in `AppServiceProvider`:

```php
public function register(): void
{
    $this->app->bind(
        UserServiceInterface::class,
        UserService::class
    );
}
```

## Best Practices

### ✅ DO

1. **Usa Type Hints**
   ```php
   public function createUser(array $data): User
   ```

2. **Usa Transactions per operazioni multiple**
   ```php
   return DB::transaction(function () use ($data) {
       // Multiple operations
   });
   ```

3. **Gestisci gli errori appropriatamente**
   ```php
   try {
       // Service logic
   } catch (SpecificException $e) {
       Log::error('User creation failed', ['error' => $e->getMessage()]);
       throw new ServiceException('Could not create user');
   }
   ```

4. **Ritorna i dati utili**
   ```php
   // Good: ritorna il model aggiornato
   return $user->fresh();
   ```

5. **Usa Dependency Injection**
   ```php
   public function __construct(
       private NotificationService $notifications,
       private CacheService $cache
   ) {}
   ```

### ❌ DON'T

1. **Non mettere validazione nei Services**
   - Usa Form Requests o valida nel Livewire component

2. **Non fare query dirette in Services senza Repository**
   - Per logica complessa, usa Repository pattern

3. **Non gestire Response/View nei Services**
   - Services ritornano dati, non response HTTP

4. **Non fare Services troppo grandi**
   - Splitta in Services più piccoli e focalizzati

5. **Non usare Services per logica semplice**
   - `User::create($data)` nel controller va bene

## Esempi Pratici

### Newsletter Subscription Service

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Newsletter;
use Illuminate\Support\Facades\Http;

class NewsletterService
{
    public function subscribe(User $user, Newsletter $newsletter): bool
    {
        // 1. Check if already subscribed
        if ($user->newsletters()->where('id', $newsletter->id)->exists()) {
            return false;
        }

        // 2. Subscribe in database
        $user->newsletters()->attach($newsletter->id, [
            'subscribed_at' => now()
        ]);

        // 3. Subscribe to external service (e.g., Mailchimp)
        $this->subscribeToMailchimp($user->email, $newsletter->list_id);

        // 4. Send confirmation email
        $user->sendNewsletterConfirmation($newsletter);

        return true;
    }

    public function unsubscribe(User $user, Newsletter $newsletter): bool
    {
        $user->newsletters()->detach($newsletter->id);

        $this->unsubscribeFromMailchimp($user->email, $newsletter->list_id);

        return true;
    }

    private function subscribeToMailchimp(string $email, string $listId): void
    {
        Http::post("https://api.mailchimp.com/3.0/lists/{$listId}/members", [
            'email_address' => $email,
            'status' => 'subscribed',
        ]);
    }

    private function unsubscribeFromMailchimp(string $email, string $listId): void
    {
        // Implementation
    }
}
```

## Risorse

- [Laravel Service Container](https://laravel.com/docs/container)
- [Dependency Injection](https://laravel.com/docs/container#dependency-injection)
- [Database Transactions](https://laravel.com/docs/database#database-transactions)

---

**Ricorda**: Services organizzano la logica business, ma non devono diventare "God Classes". Mantienili focalizzati e single-purpose!
