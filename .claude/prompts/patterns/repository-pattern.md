# Repository Pattern

Use this pattern to abstract data access logic and provide a clean interface for querying data.

## When to Use

✅ **USE Repositories when:**
- Complex query logic needs to be reused
- You want to swap data sources (database, API, cache)
- Testing requires mock data sources
- Need centralized query building logic
- Working with large, complex models

❌ **DON'T use Repositories when:**
- Simple CRUD operations (Eloquent is enough)
- Small applications with straightforward queries
- Queries are specific to a single use case

## Basic Structure

```php
<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class PostRepository
{
    /**
     * Find post by ID with relationships
     */
    public function findWithRelations(int $id): ?Post
    {
        return Post::with(['author', 'category', 'tags'])
            ->findOrFail($id);
    }

    /**
     * Get published posts
     */
    public function getPublished(int $perPage = 15)
    {
        return Post::with('author')
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate($perPage);
    }

    /**
     * Get posts by category
     */
    public function getByCategory(string $categorySlug, int $perPage = 15)
    {
        return Post::whereHas('category', function ($query) use ($categorySlug) {
                $query->where('slug', $categorySlug);
            })
            ->where('is_published', true)
            ->latest('published_at')
            ->paginate($perPage);
    }

    /**
     * Search posts
     */
    public function search(string $query, int $perPage = 15)
    {
        return Post::where('is_published', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->with('author')
            ->latest('published_at')
            ->paginate($perPage);
    }

    /**
     * Get popular posts
     */
    public function getPopular(int $limit = 10): Collection
    {
        return Post::where('is_published', true)
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get related posts
     */
    public function getRelated(Post $post, int $limit = 5): Collection
    {
        return Post::where('is_published', true)
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}
```

## With Interface (for swapping implementations)

```php
<?php

namespace App\Contracts;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface
{
    public function findWithRelations(int $id): ?Post;
    public function getPublished(int $perPage = 15);
    public function getByCategory(string $categorySlug, int $perPage = 15);
    public function search(string $query, int $perPage = 15);
    public function getPopular(int $limit = 10): Collection;
}
```

```php
<?php

namespace App\Repositories;

use App\Contracts\PostRepositoryInterface;

class EloquentPostRepository implements PostRepositoryInterface
{
    // Implementation using Eloquent
}

class CachePostRepository implements PostRepositoryInterface
{
    public function __construct(
        private PostRepositoryInterface $repository,
        private CacheService $cache
    ) {}

    public function getPublished(int $perPage = 15)
    {
        return $this->cache->remember(
            "posts.published.{$perPage}",
            3600,
            fn() => $this->repository->getPublished($perPage)
        );
    }

    // Other methods wrap repository with caching
}
```

## Binding in Service Provider

```php
<?php

namespace App\Providers;

use App\Contracts\PostRepositoryInterface;
use App\Repositories\EloquentPostRepository;
use App\Repositories\CachePostRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind interface to implementation
        $this->app->bind(
            PostRepositoryInterface::class,
            EloquentPostRepository::class
        );

        // Or with caching decorator
        $this->app->singleton(PostRepositoryInterface::class, function ($app) {
            return new CachePostRepository(
                new EloquentPostRepository(),
                $app->make(CacheService::class)
            );
        });
    }
}
```

## Usage in Service

```php
<?php

namespace App\Services;

use App\Contracts\PostRepositoryInterface;

class PostService
{
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {}

    public function getHomepagePosts()
    {
        return [
            'latest' => $this->postRepository->getPublished(5),
            'popular' => $this->postRepository->getPopular(5),
        ];
    }

    public function searchPosts(string $query)
    {
        if (strlen($query) < 3) {
            return collect();
        }

        return $this->postRepository->search($query);
    }
}
```

## Usage in Livewire

```php
<?php

namespace App\Livewire;

use App\Contracts\PostRepositoryInterface;
use Livewire\Component;
use Livewire\WithPagination;

class PostList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = '';

    public function render(PostRepositoryInterface $repository)
    {
        $posts = match(true) {
            $this->search !== '' => $repository->search($this->search),
            $this->category !== '' => $repository->getByCategory($this->category),
            default => $repository->getPublished(),
        };

        return view('livewire.post-list', [
            'posts' => $posts,
        ]);
    }
}
```

## Advanced: Criteria Pattern

```php
<?php

namespace App\Repositories\Criteria;

interface CriteriaInterface
{
    public function apply($query);
}

class PublishedCriteria implements CriteriaInterface
{
    public function apply($query)
    {
        return $query->where('is_published', true);
    }
}

class CategoryCriteria implements CriteriaInterface
{
    public function __construct(private string $categorySlug) {}

    public function apply($query)
    {
        return $query->whereHas('category', function ($q) {
            $q->where('slug', $this->categorySlug);
        });
    }
}

// Repository with criteria
class PostRepository
{
    private array $criteria = [];

    public function withCriteria(CriteriaInterface ...$criteria): self
    {
        $this->criteria = $criteria;
        return $this;
    }

    public function get()
    {
        $query = Post::query();

        foreach ($this->criteria as $criterion) {
            $query = $criterion->apply($query);
        }

        return $query->get();
    }
}

// Usage
$posts = $repository
    ->withCriteria(
        new PublishedCriteria(),
        new CategoryCriteria('technology')
    )
    ->get();
```

## Testing Repositories

```php
<?php

use App\Repositories\PostRepository;
use App\Models\Post;

test('gets published posts only', function () {
    Post::factory()->published()->count(5)->create();
    Post::factory()->draft()->count(3)->create();

    $repository = new PostRepository();
    $posts = $repository->getPublished();

    expect($posts)->toHaveCount(5);
});

test('searches posts by title and content', function () {
    Post::factory()->create(['title' => 'Laravel Tips']);
    Post::factory()->create(['content' => 'Laravel is great']);
    Post::factory()->create(['title' => 'PHP Basics']);

    $repository = new PostRepository();
    $results = $repository->search('Laravel');

    expect($results)->toHaveCount(2);
});
```

## Best Practices

### ✅ DO
- Keep repositories focused on data access
- Use interfaces for flexibility
- Cache expensive queries
- Use query scopes for reusable conditions
- Type hint return types

### ❌ DON'T
- Don't put business logic in repositories
- Don't handle HTTP responses
- Don't perform data transformations (use Resources)
- Don't make repositories too generic
