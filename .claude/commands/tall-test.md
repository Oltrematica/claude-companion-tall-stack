---
description: Generate comprehensive tests for TALL Stack components and features
---

You are tasked with creating tests for a TALL Stack application. Follow this approach:

## 1. Determine What to Test

Ask the user what they want to test:
- Specific Livewire component
- Complete feature (CRUD operations)
- Model relationships and methods
- API endpoints
- Full user workflow

## 2. Test Types

### Feature Tests (Primary focus)
Test complete user workflows and Livewire components:

```php
<?php

use App\Livewire\PostForm;
use App\Models\Post;
use Livewire\Livewire;

test('can create a post', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(PostForm::class)
        ->set('title', 'New Post')
        ->set('content', 'Post content here')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('post-created');

    $this->assertDatabaseHas('posts', [
        'title' => 'New Post',
        'user_id' => $user->id,
    ]);
});

test('validates required fields', function () {
    Livewire::test(PostForm::class)
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['title' => 'required']);
});
```

### Unit Tests
Test model methods, services, and business logic:

```php
<?php

use App\Models\Post;

test('post belongs to user', function () {
    $post = Post::factory()->create();

    expect($post->user)->toBeInstanceOf(User::class);
});

test('post slug is generated from title', function () {
    $post = Post::factory()->create([
        'title' => 'Test Post'
    ]);

    expect($post->slug)->toBe('test-post');
});
```

### Browser Tests (Dusk)
For complex user interactions (optional):

```php
<?php

test('user can complete checkout process', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/products')
                ->click('@add-to-cart')
                ->visit('/cart')
                ->click('@checkout')
                ->type('email', 'test@example.com')
                ->press('Complete Order')
                ->assertSee('Order completed!');
    });
});
```

## 3. Test Structure

For each feature/component, create tests for:

### Happy Paths
- Successful operations
- Valid data submission
- Correct state changes
- Proper redirects/responses

### Validation
- Required fields
- Field formats (email, URL, etc.)
- Min/max lengths
- Custom validation rules
- Unique constraints

### Authorization
- Authenticated users only
- Proper permissions
- Policy enforcement
- Forbidden access attempts

### Edge Cases
- Empty states
- Null values
- Boundary conditions
- Concurrent updates
- Race conditions

### Error Handling
- Invalid data
- Database errors
- External service failures
- Network timeouts

## 4. Livewire-Specific Testing

### Component Rendering
```php
test('component renders correctly', function () {
    Livewire::test(PostList::class)
        ->assertSee('Posts')
        ->assertViewHas('posts');
});
```

### Property Binding
```php
test('search updates results', function () {
    Post::factory()->create(['title' => 'Laravel']);
    Post::factory()->create(['title' => 'React']);

    Livewire::test(PostList::class)
        ->set('search', 'Laravel')
        ->assertSee('Laravel')
        ->assertDontSee('React');
});
```

### Method Calls
```php
test('can delete post', function () {
    $post = Post::factory()->create();

    Livewire::test(PostList::class)
        ->call('delete', $post->id)
        ->assertDispatched('post-deleted');

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});
```

### Events
```php
test('dispatches event on save', function () {
    Livewire::test(PostForm::class)
        ->set('title', 'New Post')
        ->call('save')
        ->assertDispatched('post-created');
});

test('listens to refresh event', function () {
    Livewire::test(PostList::class)
        ->dispatch('post-created')
        ->assertSet('refreshKey', fn($value) => $value > 0);
});
```

### File Uploads
```php
use Illuminate\Http\UploadedFile;

test('can upload image', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('photo.jpg');

    Livewire::test(UploadPhoto::class)
        ->set('photo', $file)
        ->call('save')
        ->assertHasNoErrors();

    Storage::disk('public')->assertExists('photos/' . $file->hashName());
});
```

## 5. Database Testing

### Use Factories
```php
// Create test data easily
$user = User::factory()->create();
$posts = Post::factory()->count(3)->create();
$post = Post::factory()->published()->create();
```

### Test Relationships
```php
test('user has many posts', function () {
    $user = User::factory()
        ->has(Post::factory()->count(3))
        ->create();

    expect($user->posts)->toHaveCount(3);
});
```

### Test Scopes
```php
test('published scope returns only published posts', function () {
    Post::factory()->count(2)->published()->create();
    Post::factory()->count(3)->draft()->create();

    expect(Post::published()->get())->toHaveCount(2);
});
```

## 6. Test Organization

Organize tests by feature:
```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   └── RegistrationTest.php
│   ├── Posts/
│   │   ├── CreatePostTest.php
│   │   ├── UpdatePostTest.php
│   │   └── DeletePostTest.php
│   └── Livewire/
│       ├── PostListTest.php
│       └── PostFormTest.php
└── Unit/
    ├── Models/
    │   └── PostTest.php
    └── Services/
        └── PostServiceTest.php
```

## 7. Test Execution

After creating tests, show the user:

1. How to run tests:
   ```bash
   php artisan test
   php artisan test --filter PostForm
   php artisan test --coverage
   ```

2. Test summary with coverage

3. Any failing tests with explanations

4. Suggestions for additional test cases

## 8. Best Practices

- Use descriptive test names
- Test one thing per test
- Use factories for test data
- Keep tests independent
- Mock external services
- Use database transactions
- Test both success and failure cases
- Include edge cases
- Maintain test speed
- Update tests with code changes

Ask the user which component/feature to test first!
