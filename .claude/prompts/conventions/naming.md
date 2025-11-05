# Naming Conventions - TALL Stack Project

---
Ultimo aggiornamento: 2025-01-05
Versione: 1.0.0
---

## Livewire Components

### Classi PHP
- **Formato**: PascalCase
- **Convenzione**: `[Entità][Azione/Tipo]`

```
✅ CORRECT
UserProfile
PostList
CreatePostForm
DeleteConfirmationModal
StatsCard

❌ WRONG
userProfile
User_Profile
user-profile
create_post_form
```

### File Blade
- **Formato**: kebab-case
- **Posizione**: `resources/views/livewire/`

```
✅ CORRECT
user-profile.blade.php
post-list.blade.php
create-post-form.blade.php

❌ WRONG
UserProfile.blade.php
user_profile.blade.php
userProfile.blade.php
```

## Models

### Classi Model
- **Formato**: PascalCase
- **Singolare**

```php
✅ CORRECT
User
Post
Comment
ProductCategory

❌ WRONG
Users
Posts
product_category
```

### Tabelle Database
- **Formato**: snake_case
- **Plurale**

```sql
✅ CORRECT
users
posts
comments
product_categories

❌ WRONG
User
Posts
ProductCategories
product_category
```

### Colonne
- **Formato**: snake_case

```sql
✅ CORRECT
created_at
user_id
first_name
is_published

❌ WRONG
createdAt
userId
FirstName
isPublished
```

### Relazioni (Metodi)
- **Formato**: camelCase
- **Singolare per belongsTo/hasOne**
- **Plurale per hasMany/belongsToMany**

```php
✅ CORRECT
public function user()           // belongsTo
public function posts()          // hasMany
public function categories()     // belongsToMany
public function latestPost()     // hasOne

❌ WRONG
public function User()
public function post()           // hasMany dovrebbe essere plurale
public function category()       // belongsToMany dovrebbe essere plurale
```

## Controllers

- **Formato**: PascalCase
- **Suffisso**: `Controller`

```php
✅ CORRECT
UserController
PostController
Api\V1\UserController

❌ WRONG
userController
Usercontroller
User_Controller
Users_Controller
```

## Services

- **Formato**: PascalCase
- **Suffisso**: `Service`
- **Posizione**: `app/Services/`

```php
✅ CORRECT
UserService
NotificationService
PaymentProcessingService

❌ WRONG
UserSvc
NotificationServices
PaymentProcessing
```

## Actions

- **Formato**: PascalCase
- **Convenzione**: Verbo + sostantivo
- **Posizione**: `app/Actions/`

```php
✅ CORRECT
CreateUser
UpdateUserProfile
SendWelcomeEmail
ProcessPayment
GenerateInvoice

❌ WRONG
UserCreate
Create
UserUpdate
SendEmail (troppo generico)
```

## Jobs

- **Formato**: PascalCase
- **Convenzione**: Verbo + sostantivo
- **Posizione**: `app/Jobs/`

```php
✅ CORRECT
ProcessPodcast
SendEmailNotification
GenerateReport
SyncDataWithThirdParty

❌ WRONG
PodcastJob
EmailJob
Report
DataSync
```

## Events

- **Formato**: PascalCase
- **Convenzione**: Sostantivo + participio passato
- **Posizione**: `app/Events/`

```php
✅ CORRECT
UserCreated
OrderShipped
PaymentProcessed
PostPublished

❌ WRONG
CreateUser
ShipOrder
ProcessPayment
PublishPost
```

## Listeners

- **Formato**: PascalCase
- **Convenzione**: Verbo + sostantivo
- **Posizione**: `app/Listeners/`

```php
✅ CORRECT
SendWelcomeEmail
NotifyAdministrators
UpdateUserStatistics
LogActivity

❌ WRONG
WelcomeEmailSender
AdminNotifier
UserStatsUpdater
```

## Policies

- **Formato**: PascalCase
- **Suffisso**: `Policy`
- **Posizione**: `app/Policies/`

```php
✅ CORRECT
PostPolicy
UserPolicy
CommentPolicy

❌ WRONG
PostsPolicy
Post_Policy
PolicyPost
```

## Migrations

- **Formato**: snake_case
- **Convenzione**: `create_[table]_table` o `add_[column]_to_[table]_table`

```php
✅ CORRECT
2024_01_01_000000_create_users_table.php
2024_01_02_000000_add_email_verified_at_to_users_table.php
2024_01_03_000000_create_posts_table.php

❌ WRONG
create_user_table.php
CreateUsersTable.php
add_email_to_user.php
```

## Routes

### Route Names
- **Formato**: dot notation
- **Pattern**: `[resource].[action]`

```php
✅ CORRECT
Route::get('/posts', PostList::class)->name('posts.index');
Route::get('/posts/create', CreatePost::class)->name('posts.create');
Route::get('/posts/{post}', ShowPost::class)->name('posts.show');
Route::get('/posts/{post}/edit', EditPost::class)->name('posts.edit');

❌ WRONG
->name('post-index')
->name('createPost')
->name('PostShow')
```

### Route URIs
- **Formato**: kebab-case
- **Plurale per resources**

```php
✅ CORRECT
/posts
/user-profiles
/product-categories
/api/v1/users

❌ WRONG
/Posts
/user_profiles
/userProfiles
/ProductCategories
```

## Variables & Properties

### Variabili PHP
- **Formato**: camelCase

```php
✅ CORRECT
$userName
$isPublished
$createdAt
$postCount

❌ WRONG
$user_name
$UserName
$is_published
$PostCount
```

### Proprietà Livewire Pubbliche
- **Formato**: camelCase

```php
✅ CORRECT
public string $searchQuery = '';
public bool $showModal = false;
public int $userId;

❌ WRONG
public string $search_query = '';
public bool $ShowModal = false;
public int $user_id;
```

### Array Keys (per dati frontend)
- **Formato**: camelCase (per JavaScript interop)

```php
✅ CORRECT
[
    'userName' => 'John',
    'isActive' => true,
    'createdAt' => now()
]

// Per database: snake_case
[
    'user_name' => 'John',
    'is_active' => true,
    'created_at' => now()
]
```

## CSS Classes (Tailwind)

### Component Classes
- **Formato**: kebab-case per classi custom

```css
✅ CORRECT
.btn-primary
.card-header
.form-input

❌ WRONG
.btnPrimary
.CardHeader
.form_input
```

### Alpine.js Data
- **Formato**: camelCase

```html
✅ CORRECT
<div x-data="{ showModal: false, userName: '' }">

❌ WRONG
<div x-data="{ show_modal: false, UserName: '' }">
```

## Test Files & Methods

### File dei Test
- **Formato**: PascalCase
- **Suffisso**: `Test`

```php
✅ CORRECT
UserTest.php
PostListTest.php
CreateUserTest.php

❌ WRONG
user_test.php
test_user.php
TestUser.php
```

### Metodi Test (Pest)
- **Formato**: stringa descrittiva

```php
✅ CORRECT
test('can create a user');
test('validates required fields');
it('displays posts correctly');

❌ WRONG
test('test1');
test('createUser');
```

## Config Files

- **Formato**: kebab-case
- **Posizione**: `config/`

```
✅ CORRECT
app.php
database.php
mail-settings.php

❌ WRONG
App.php
data_base.php
mailSettings.php
```

## Environment Variables

- **Formato**: SCREAMING_SNAKE_CASE
- **File**: `.env`

```bash
✅ CORRECT
APP_NAME="My App"
DB_CONNECTION=mysql
MAIL_FROM_ADDRESS="hello@example.com"
CUSTOM_API_KEY="abc123"

❌ WRONG
appName="My App"
db-connection=mysql
mailFromAddress="hello@example.com"
```

## Riepilogo Rapido

| Elemento | Formato | Esempio |
|----------|---------|---------|
| Livewire Class | PascalCase | `UserProfile` |
| Blade File | kebab-case | `user-profile.blade.php` |
| Model | PascalCase (singolare) | `User` |
| Table | snake_case (plurale) | `users` |
| Column | snake_case | `created_at` |
| Variable | camelCase | `$userName` |
| Method | camelCase | `createUser()` |
| Route Name | dot.notation | `posts.index` |
| Route URI | kebab-case | `/user-profiles` |
| Service | PascalCase + Service | `UserService` |
| Action | Verb + Noun | `CreateUser` |
| Event | Noun + Past Tense | `UserCreated` |
| Job | Verb + Noun | `ProcessPayment` |

---

**Nota**: Queste convenzioni seguono gli standard Laravel e le best practices della community TALL Stack.
