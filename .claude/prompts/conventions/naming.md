# Naming Conventions for TALL Stack

Consistent naming conventions improve code readability and maintainability across your TALL Stack application.

## PHP/Laravel Conventions

### Classes

```php
// PascalCase for class names
class UserController {}
class PostService {}
class OrderRepository {}

// Suffix indicates purpose
class CreateUserAction {}          // Action
class UserService {}                // Service
class PostRepository {}             // Repository
class OrderPolicy {}                // Policy
class UserCreatedEvent {}           // Event
class SendWelcomeEmailJob {}        // Job
class ProductNotFoundException {}   // Exception
```

### Methods and Functions

```php
// camelCase for methods
class UserService
{
    public function createUser() {}
    public function getUserById() {}
    public function updateUserProfile() {}
    public function deleteUser() {}
}

// Boolean methods start with is/has/can/should
public function isActive(): bool {}
public function hasPermission(): bool {}
public function canEdit(): bool {}
public function shouldNotify(): bool {}
```

### Variables and Properties

```php
// camelCase for variables
$userName = 'John';
$isActive = true;
$postCount = 10;

// Descriptive names
$user = User::find($id);              // ✅ Good
$u = User::find($id);                 // ❌ Bad

// Collections pluralized
$users = User::all();
$activePosts = Post::published()->get();
```

### Constants

```php
// UPPER_SNAKE_CASE for constants
class OrderStatus
{
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';
}

// Or use Enums (PHP 8.1+)
enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
```

## Database Conventions

### Table Names

```php
// Plural, snake_case
users
posts
order_items
product_categories

// Pivot tables: alphabetically ordered, singular
post_tag        // ✅ Correct
tag_post        // ❌ Wrong order

post_user       // ✅ Correct
user_post       // ❌ Wrong order
```

### Column Names

```php
// snake_case
first_name
email_address
created_at
is_active

// Boolean columns prefix with is/has
is_published
has_paid
is_verified

// Foreign keys: singular_id
user_id
category_id
author_id

// Polymorphic relationships
commentable_id
commentable_type
```

### Indexes and Constraints

```php
// Format: {table}_{columns}_{type}
users_email_unique
posts_user_id_foreign
posts_title_published_at_index

// Primary key: id
Schema::create('users', function (Blueprint $table) {
    $table->id(); // Creates 'id' column
});
```

## Livewire Conventions

### Component Classes

```php
// PascalCase, descriptive names
class UserProfile extends Component {}
class CreatePost extends Component {}
class EditProductForm extends Component {}
class ProductCard extends Component {}

// Nested components
class Posts\PostList extends Component {}
class Users\Profile\EditForm extends Component {}
```

### Component Views

```blade
{{-- kebab-case, matches component --}}
livewire/user-profile.blade.php
livewire/create-post.blade.php
livewire/edit-product-form.blade.php
livewire/product-card.blade.php

{{-- Nested --}}
livewire/posts/post-list.blade.php
livewire/users/profile/edit-form.blade.php
```

### Public Properties

```php
class CreatePost extends Component
{
    // camelCase for properties
    public string $title = '';
    public string $content = '';
    public int $categoryId;

    // Boolean properties
    public bool $isPublished = false;
    public bool $hasImages = false;
}
```

### Methods

```php
class ProductList extends Component
{
    // Action methods (user-triggered)
    public function create() {}
    public function edit($id) {}
    public function delete($id) {}

    // Lifecycle hooks (Livewire-specific)
    public function mount() {}
    public function updated($property) {}
    public function render() {}

    // Custom computed properties
    #[Computed]
    public function filteredProducts() {}

    // Event listeners
    protected $listeners = [
        'productCreated' => 'refreshList',
        'productDeleted' => 'removeProduct',
    ];
}
```

### Events

```php
// kebab-case for event names
$this->dispatch('post-created');
$this->dispatch('user-updated');
$this->dispatch('cart-item-added');

// In view
x-on:post-created="..."
@post-created="..."
```

## Blade/Views Conventions

### View Files

```php
// kebab-case
resources/views/posts/index.blade.php
resources/views/users/profile.blade.php
resources/views/components/product-card.blade.php

// Partials prefix with underscore
resources/views/posts/_form.blade.php
resources/views/layouts/_navigation.blade.php
```

### Blade Components

```php
// Component class: PascalCase
class ProductCard extends Component {}
class FormInput extends Component {}

// Component view: kebab-case
components/product-card.blade.php
components/form-input.blade.php

// Usage in Blade: kebab-case
<x-product-card :product="$product" />
<x-form-input name="email" label="Email Address" />
```

## Routes

```php
// Kebab-case URLs
Route::get('/user-profile', ...);
Route::get('/blog-posts', ...);
Route::get('/products/create', ...);

// RESTful routes (use Route::resource)
Route::resource('posts', PostController::class);
// Creates: posts, posts/create, posts/{id}, etc.

// API routes version prefix
Route::prefix('v1')->group(function () {
    Route::get('/users', ...);
});
```

## File Structure

```
app/
├── Actions/              # PascalCase
│   ├── CreateUser.php
│   └── ProcessOrder.php
├── DataTransferObjects/  # PascalCase
│   └── UserData.php
├── Enums/                # PascalCase
│   └── OrderStatus.php
├── Events/               # PascalCase, past tense
│   └── OrderCreated.php
├── Exceptions/           # PascalCase, Exception suffix
│   └── PaymentFailedException.php
├── Http/
│   ├── Controllers/      # PascalCase, Controller suffix
│   │   └── PostController.php
│   └── Requests/         # PascalCase, Request suffix
│       └── StorePostRequest.php
├── Jobs/                 # PascalCase, describes action
│   └── SendWelcomeEmail.php
├── Listeners/            # PascalCase
│   └── SendOrderConfirmation.php
├── Livewire/             # PascalCase
│   └── CreatePost.php
├── Models/               # PascalCase, singular
│   └── Post.php
├── Notifications/        # PascalCase, Notification suffix
│   └── OrderShipped.php
├── Policies/             # PascalCase, Policy suffix
│   └── PostPolicy.php
├── Repositories/         # PascalCase, Repository suffix
│   └── PostRepository.php
└── Services/             # PascalCase, Service suffix
    └── PaymentService.php
```

## Tailwind CSS Conventions

### Class Organization

```blade
{{-- Order: layout → spacing → sizing → colors → typography → effects --}}
<div class="
    flex items-center justify-between    {{-- Layout --}}
    px-4 py-2                            {{-- Spacing --}}
    w-full max-w-md                      {{-- Sizing --}}
    bg-white border border-gray-200     {{-- Colors --}}
    text-sm font-medium                  {{-- Typography --}}
    rounded-lg shadow-sm                 {{-- Effects --}}
">
```

### Component Classes

```blade
{{-- Extract repeated patterns to components --}}

{{-- ❌ Bad: Repeated classes --}}
<button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
<button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">

{{-- ✅ Good: Component --}}
<x-button>Save</x-button>
<x-button>Cancel</x-button>
```

## Alpine.js Conventions

### Directives

```html
<!-- Use x- prefix for Alpine directives -->
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open" x-transition>Content</div>
</div>

<!-- Data property names: camelCase -->
<div x-data="{
    isOpen: false,
    selectedId: null,
    itemCount: 0
}">
```

## Testing

### Test Files

```php
// tests/Feature/
CreatePostTest.php          // Test suffix
UserAuthenticationTest.php
OrderProcessingTest.php

// tests/Unit/
UserTest.php
PostTest.php
```

### Test Methods (Pest)

```php
// Descriptive test names
test('user can create a post', function () {});
test('guest cannot access dashboard', function () {});
test('order total calculates correctly', function () {});

// Or it() syntax
it('validates required fields', function () {});
it('sends email after order', function () {});
```

## Git Conventions

### Branch Names

```bash
# Format: type/description-in-kebab-case
feature/user-authentication
bugfix/fix-payment-processing
hotfix/security-patch
refactor/update-order-service
```

### Commit Messages

```bash
# Format: type: description
feat: add user registration
fix: resolve payment processing error
refactor: improve post service
test: add order tests
docs: update API documentation
style: format code with Pint
```

## Environment Variables

```bash
# UPPER_SNAKE_CASE
APP_NAME="My Application"
DB_CONNECTION=mysql
MAIL_FROM_ADDRESS=noreply@example.com
STRIPE_SECRET_KEY=sk_test_...
```

## Quick Reference

| Type | Convention | Example |
|------|-----------|---------|
| Class | PascalCase | `UserController` |
| Method | camelCase | `createUser()` |
| Variable | camelCase | `$userName` |
| Constant | UPPER_SNAKE_CASE | `ORDER_STATUS` |
| Table | plural_snake_case | `order_items` |
| Column | snake_case | `created_at` |
| Route | kebab-case | `/user-profile` |
| View | kebab-case | `user-profile.blade.php` |
| Component | PascalCase (class) | `UserProfile` |
| Component | kebab-case (view) | `user-profile.blade.php` |
| Event | kebab-case | `post-created` |

## Benefits of Consistent Naming

- ✅ **Readability**: Code is self-documenting
- ✅ **Maintainability**: Easy to find and update code
- ✅ **Collaboration**: Team understands code structure
- ✅ **Productivity**: Less time deciding on names
- ✅ **Professionalism**: Shows attention to detail

## Tools to Enforce

- **Laravel Pint**: PHP code style fixer
- **PHPStan**: Static analysis for type safety
- **ESLint**: JavaScript/Alpine.js linting
- **Prettier**: Blade template formatting
