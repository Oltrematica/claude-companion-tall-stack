# Coding Standards for TALL Stack

Best practices and coding standards for writing clean, maintainable TALL Stack code.

## PHP Standards (PSR-12)

### Code Formatting

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private NotificationService $notifications
    ) {}

    public function createUser(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $this->notifications->sendWelcome($user);

        return $user;
    }
}
```

### Type Declarations

```php
// ✅ Always use type hints
public function createPost(string $title, string $content): Post
{
    return Post::create([
        'title' => $title,
        'content' => $content,
    ]);
}

// ❌ Avoid untyped parameters
public function createPost($title, $content)
{
    //...
}
```

### Return Types

```php
// ✅ Explicit return types
public function getUser(int $id): ?User
{
    return User::find($id);
}

public function isActive(): bool
{
    return $this->status === 'active';
}

public function getItems(): Collection
{
    return $this->items;
}

// Void for no return
public function notify(): void
{
    // Send notification
}
```

## Laravel Best Practices

### Eloquent Usage

```php
// ✅ Use Eloquent methods
$users = User::where('active', true)->get();
$user = User::find($id);

// ❌ Avoid raw queries unless necessary
$users = DB::select("SELECT * FROM users WHERE active = 1");

// Use query builder with bindings for complex queries
$users = DB::table('users')
    ->where('votes', '>', 100)
    ->get();
```

### Avoid N+1 Queries

```php
// ❌ N+1 Problem
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // N queries
}

// ✅ Eager Loading
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name; // 2 queries total
}
```

### Use Form Requests

```php
// ✅ Validation in Form Request
class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ];
    }
}

// In controller
public function store(StorePostRequest $request)
{
    Post::create($request->validated());
}

// ❌ Validation in controller
public function store(Request $request)
{
    $request->validate([...]);
}
```

### Use Policies for Authorization

```php
// ✅ Policy
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}

// In controller
public function update(Post $post)
{
    $this->authorize('update', $post);
}

// ❌ Authorization logic in controller
public function update(Post $post)
{
    if (auth()->id() !== $post->user_id) {
        abort(403);
    }
}
```

## Livewire Best Practices

### Component Structure

```php
class CreatePost extends Component
{
    // 1. Attributes
    #[Rule('required|min:3')]
    public string $title = '';

    #[Rule('required')]
    public string $content = '';

    #[Locked]
    public int $userId;

    // 2. Lifecycle hooks
    public function mount(): void
    {
        $this->userId = auth()->id();
    }

    // 3. Computed properties
    #[Computed]
    public function categories()
    {
        return Category::all();
    }

    // 4. Action methods
    public function save(): void
    {
        $this->validate();

        Post::create([
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => $this->userId,
        ]);

        $this->dispatch('post-created');
        $this->redirectRoute('posts.index');
    }

    // 5. Render method (always last)
    public function render()
    {
        return view('livewire.create-post');
    }
}
```

### Protect Sensitive Data

```php
// ✅ Use #[Locked] for IDs
#[Locked]
public int $userId;

// ✅ Use private for sensitive data
private string $apiKey;

// ❌ Don't expose sensitive data
public string $creditCard; // User can modify from browser!
```

### Use Computed Properties

```php
// ❌ Query in every render
public function render()
{
    return view('livewire.posts', [
        'posts' => Post::all(), // Runs every time
    ]);
}

// ✅ Use computed property
#[Computed]
public function posts()
{
    return Post::all(); // Cached
}

public function render()
{
    return view('livewire.posts');
}
```

## Blade Templates

### Component Organization

```blade
{{-- ✅ Organized template --}}
<div class="container">
    {{-- Header --}}
    <header class="mb-4">
        <h1>{{ $title }}</h1>
    </header>

    {{-- Main Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="mt-4">
        @include('partials.footer')
    </footer>
</div>
```

### Avoid Logic in Views

```blade
{{-- ❌ Complex logic in view --}}
@php
    $total = 0;
    foreach ($items as $item) {
        $total += $item->price * $item->quantity;
    }
@endphp

{{-- ✅ Logic in component/controller --}}
{{ $this->total }}
```

### Use Components

```blade
{{-- ❌ Repeated code --}}
<div class="alert alert-success">Success!</div>
<div class="alert alert-error">Error!</div>

{{-- ✅ Component --}}
<x-alert type="success">Success!</x-alert>
<x-alert type="error">Error!</x-alert>
```

## Security Best Practices

### Always Validate

```php
// ✅ Validate user input
$validated = $request->validate([
    'email' => 'required|email',
    'password' => 'required|min:8',
]);

// ❌ Never trust user input
User::create($request->all());
```

### Use Mass Assignment Protection

```php
class User extends Model
{
    // ✅ Define fillable
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // ✅ Or guard sensitive
    protected $guarded = [
        'is_admin',
        'email_verified_at',
    ];
}
```

### Escape Output

```blade
{{-- ✅ Auto-escaped --}}
{{ $userInput }}

{{-- ❌ Unescaped (only for trusted content) --}}
{!! $userInput !!}

{{-- ✅ If HTML needed, sanitize first --}}
{!! Purifier::clean($userInput) !!}
```

### Use Policies

```php
// ✅ Check permissions
$this->authorize('update', $post);

// ❌ Manual checks
if (auth()->id() === $post->user_id) {
    // ...
}
```

## Testing Standards

### Test Structure (AAA Pattern)

```php
test('user can create post', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->post('/posts', [
            'title' => 'Test Post',
            'content' => 'Content',
        ]);

    // Assert
    $response->assertCreated();
    $this->assertDatabaseHas('posts', [
        'title' => 'Test Post',
    ]);
});
```

### Descriptive Test Names

```php
// ✅ Clear test names
test('user can create post when authenticated')
test('guest cannot create post')
test('validates required fields')

// ❌ Unclear names
test('test1')
test('it works')
```

## Documentation

### PHPDoc Blocks

```php
/**
 * Create a new user account
 *
 * @param  array{name: string, email: string, password: string}  $data
 * @return User
 * @throws ValidationException
 */
public function createUser(array $data): User
{
    // Implementation
}
```

### Inline Comments

```php
// ✅ Explain WHY, not WHAT
// Using raw query for performance on large dataset
$results = DB::select('SELECT ...');

// ❌ Don't state the obvious
// Loop through users
foreach ($users as $user) {
    // ...
}
```

## Error Handling

```php
// ✅ Specific exceptions
try {
    $payment = $this->processPayment($order);
} catch (PaymentFailedException $e) {
    Log::error('Payment failed', ['order' => $order->id]);
    throw $e;
}

// ✅ Provide context
throw new PaymentFailedException(
    "Payment failed for order {$order->id}",
    previous: $e
);
```

## Code Organization

### Single Responsibility

```php
// ✅ Focused class
class UserService
{
    public function createUser(array $data): User
    {
        // Only user creation logic
    }
}

// ❌ Too many responsibilities
class UserService
{
    public function createUser() {}
    public function sendEmail() {}
    public function processPayment() {}
    public function generateReport() {}
}
```

### Dependency Injection

```php
// ✅ Inject dependencies
public function __construct(
    private UserRepository $users,
    private NotificationService $notifications
) {}

// ❌ Create dependencies inside
public function __construct()
{
    $this->users = new UserRepository();
}
```

## Performance

### Database Optimization

```php
// ✅ Select only needed columns
$users = User::select('id', 'name', 'email')->get();

// ✅ Limit results
$posts = Post::latest()->limit(10)->get();

// ✅ Use cursor for large datasets
foreach (User::cursor() as $user) {
    // Process one at a time
}
```

### Caching

```php
// ✅ Cache expensive operations
$stats = Cache::remember('dashboard-stats', 3600, function () {
    return DB::table('orders')->count();
});

// ✅ Tag-based cache invalidation
Cache::tags(['posts'])->flush();
```

## Tools

- **Laravel Pint**: Code style fixer
- **PHPStan**: Static analysis
- **Larastan**: Laravel-specific PHPStan
- **Pest**: Modern testing
- **Laravel Telescope**: Debugging
- **Laravel Debugbar**: Development debugging
