# Laravel Boost AI Guidelines Examples

These files are **examples** of guidelines to copy into your Laravel project's `.ai/guidelines/` folder after installing Laravel Boost.

## How to Use

### 1. Install Laravel Boost

```bash
composer require laravel/boost --dev
php artisan boost:install
```

### 2. Copy Files to `.ai/guidelines/`

```bash
# From your TALL Stack project root
cp .claude/.ai-guidelines-examples/tall-stack.blade.php .ai/guidelines/
```

### 3. Customize for Your Project

Edit `.ai/guidelines/tall-stack.blade.php` to add:
- Your project-specific conventions
- Custom patterns you use
- Team standards
- Architectural decisions

## What Are AI Guidelines?

AI Guidelines are **dynamic templates** that Laravel Boost reads to provide context-aware assistance. They're Blade files, so they can:

- Access your application state: `{{ app()->version() }}`
- Check installed packages: `@if(class_exists('Livewire\Livewire'))`
- Read configuration: `{{ config('app.name') }}`
- Query your database schema
- Inspect your models and relationships

## Available Examples

### `tall-stack.blade.php`

Complete guidelines for TALL Stack development including:
- Laravel best practices
- Livewire component patterns
- Tailwind CSS conventions
- Alpine.js patterns
- Database design
- Security practices
- Testing strategies

## Creating Custom Guidelines

Create new `.blade.php` files in `.ai/guidelines/`:

```blade
{{-- .ai/guidelines/my-custom-patterns.blade.php --}}

# My Project Patterns

## Authentication
We use Laravel Sanctum with these specific flows:
- API authentication via tokens
- Session-based for web routes

Current setup:
- Laravel: {{ app()->version() }}
- Sanctum: {{ class_exists('Laravel\Sanctum\Sanctum') ? 'installed' : 'not installed' }}

## Database Conventions
@php
$tables = DB::select('SHOW TABLES');
@endphp

Current tables: {{ count($tables) }}

Our naming conventions:
- Use singular model names
- Pivot tables: alphabetical order (e.g., `post_user`)
- Timestamps always included
```

## Benefits

**Without Boost:**
```
User: "Create a user registration component"
Claude: Creates generic component
```

**With Boost + Guidelines:**
```
User: "Create a user registration component"
Claude:
  1. Checks your Livewire version (via Boost)
  2. Reads your guidelines
  3. Sees you use 2FA and custom validation
  4. Creates component matching your exact patterns
```

## Best Practices

### DO
- ✅ Keep guidelines updated as project evolves
- ✅ Document "why" behind patterns, not just "what"
- ✅ Use Blade for dynamic, context-aware guidelines
- ✅ Share guidelines with team via version control
- ✅ Include examples of correct and incorrect patterns

### DON'T
- ❌ Don't include sensitive data or secrets
- ❌ Don't make guidelines too verbose (Claude has token limits)
- ❌ Don't duplicate what's already in framework docs
- ❌ Don't forget to update when conventions change

## Integration with Claude Code

These guidelines work seamlessly with:
- **Agents** (`.claude/agents/`): Specialized expertise
- **Commands** (`.claude/commands/`): Automated operations
- **Prompts** (`.claude/prompts/`): Reusable snippets

The full stack:
```
User Request
    ↓
Claude Code reads:
    1. Slash command (what to do)
    2. Agent expertise (how to do it)
    3. Boost guidelines (your patterns)
    4. Boost MCP tools (your context)
    ↓
Perfect, project-specific code
```

## Examples in Action

### Before (Generic)
```php
class UserRegistration extends Component
{
    public $email;
    public $password;

    public function register()
    {
        User::create([...]);
    }
}
```

### After (Your Patterns)
```php
class UserRegistration extends Component
{
    use WithRateLimiting; // Your pattern

    #[Locked]
    public string $email = '';

    #[Locked]
    public string $password = '';

    public function register(UserService $service) // Your pattern
    {
        $this->rateLimit(5); // Your pattern

        $validated = $this->validate([
            'email' => ['required', 'email', Rules\Email::default()], // Your rule
            'password' => $this->passwordRules(), // Your method
        ]);

        $user = $service->createUser($validated); // Your service

        $this->dispatch('user-registered', $user->id); // Your event

        $this->redirectRoute('dashboard');
    }
}
```

## Resources

- [Laravel Boost Documentation](https://github.com/laravel/boost)
- [Model Context Protocol](https://modelcontextprotocol.io)
- [Claude Code Documentation](https://docs.claude.com/claude-code)

## Need Help?

Ask Claude Code:
```
Help me create AI guidelines for [your specific pattern]
```

Or use the setup wizard:
```
/boost-setup
```

---

**Pro Tip**: Start with the included `tall-stack.blade.php` example and gradually customize it as you establish your project's patterns.
