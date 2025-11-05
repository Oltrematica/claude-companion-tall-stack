# Blog Starter Kit

A complete blogging platform built with the TALL Stack. Perfect for blogs, news sites, and content platforms.

## ✨ Features

- 📝 **Post Management**: Full CRUD with rich text editor
- 📁 **Categories & Tags**: Organize content efficiently
- 👤 **Author Profiles**: Multi-author support with profiles
- 💬 **Comments**: Nested comments with moderation
- 🔍 **Search**: Full-text search with filters
- 📰 **RSS Feeds**: Auto-generated RSS/Atom feeds
- 🔗 **Social Sharing**: Share buttons for major platforms
- 🎨 **Media Library**: Image upload and management
- 📊 **Analytics**: View counts and popular posts
- 🌐 **SEO Optimized**: Meta tags, sitemaps, structured data

## 🚀 Quick Start

```bash
# Copy starter files
cp -r starters/blog-starter/* your-project/

# Install dependencies
composer install
npm install

# Setup database
php artisan migrate:fresh --seed

# Start dev server
npm run dev
php artisan serve
```

## 📦 Sample Data

After seeding:
- 20 blog posts across multiple categories
- 5 authors with profiles
- Sample comments
- Categories: Technology, Lifestyle, Travel, etc.

**Admin Login:**
- Email: `editor@example.com`
- Password: `password`

## 🗄️ Database Schema

### Posts
```
- id, title, slug, excerpt, content
- author_id, category_id
- featured_image, meta_description
- is_published, published_at
- view_count
- timestamps, soft_deletes
```

### Categories
```
- id, name, slug, description
- parent_id (for hierarchical categories)
- timestamps
```

### Tags
```
- id, name, slug
- timestamps
```

### Comments
```
- id, post_id, user_id
- parent_id (for nested replies)
- content, is_approved
- timestamps
```

## 🎨 Key Components

- `PostList` - Display posts with filtering
- `PostEditor` - Rich text post editor (TipTap)
- `CommentSection` - Nested comments with replies
- `SearchPosts` - Full-text search with filters
- `CategoryManager` - Manage categories
- `MediaLibrary` - Upload and manage images

## 🔒 Authorization

```php
// Post policies included
Gate::define('publish-post', fn ($user) => $user->isEditor());
Gate::define('moderate-comments', fn ($user) => $user->isModerator());
```

## 🧪 Testing

```bash
php artisan test --filter=BlogTest
```

Tests include:
- Post CRUD operations
- Comment moderation
- Search functionality
- SEO meta generation
- RSS feed generation

## 💡 Customization

### Enable Comments Moderation
```php
// config/blog.php
'comments' => [
    'require_approval' => true,
    'allow_guests' => false,
    'max_depth' => 3,
],
```

### Configure Rich Text Editor
```php
// config/blog.php
'editor' => [
    'toolbar' => ['bold', 'italic', 'link', 'image'],
    'max_length' => 50000,
],
```

### SEO Settings
```php
// config/blog.php
'seo' => [
    'title_suffix' => ' | My Blog',
    'default_image' => '/images/og-image.jpg',
    'twitter_handle' => '@yourblog',
],
```

## 📚 Usage Examples

### Create a Post
```php
$post = Post::create([
    'title' => 'My First Post',
    'content' => 'Post content here...',
    'author_id' => auth()->id(),
    'category_id' => 1,
    'is_published' => true,
    'published_at' => now(),
]);

$post->tags()->attach([1, 2, 3]);
```

### Generate RSS Feed
```php
// Automatically available at /feed
Route::get('/feed', [RssFeedController::class, 'index']);
```

### Track Views
```php
// Automatically tracked
$post->incrementViews();
```

## 🚀 Going Live

- Configure mail for comment notifications
- Set up caching for popular posts
- Enable CDN for images
- Configure search index (Meilisearch)
- Set up sitemap generation

See `/tall-deploy` for complete deployment guide.

---

**Happy Blogging! 📝**
