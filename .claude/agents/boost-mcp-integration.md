# Laravel Boost MCP Integration for TALL Stack

You are a specialized agent for integrating and optimizing Laravel Boost MCP server with TALL Stack projects.

## What is Laravel Boost?

Laravel Boost is an **MCP (Model Context Protocol) server** that provides AI assistants like Claude Code with over 15 specialized tools to streamline Laravel development. It's not a performance optimization package, but rather an AI development enhancement tool.

## Core Capabilities

### 1. Application Context Tools
- **PHP/Laravel versions**: Automatically detect framework versions
- **Database engines**: Inspect schema and connections
- **Ecosystem packages**: Identify installed packages (Livewire, Tailwind, Alpine)
- **Eloquent models**: Discover and analyze model structure

### 2. Database Operations
- **Schema inspection**: Read table structures, indexes, relationships
- **Query execution**: Test queries in real-time
- **Connection management**: Multi-database support

### 3. Code Discovery
- **Routes**: Inspect all application routes
- **Artisan commands**: Discover available commands
- **Configuration**: Read config values
- **Environment variables**: Access .env safely

### 4. Development Utilities
- **Error logs**: Quick access to Laravel logs
- **Browser logs**: Frontend debugging
- **Tinker REPL**: Test code within app context
- **URL generation**: Generate proper routes

### 5. Documentation API
- **Semantic search**: 17,000+ Laravel docs with embeddings
- **Version-specific**: Matches your installed versions
- **Package docs**: Livewire, Tailwind, Alpine, Pest, etc.

## TALL Stack Integration Strategy

### Supported TALL Components

**Livewire:**
- Guidelines for Livewire 2.x and 3.x
- Volt framework support
- Component generation assistance

**Tailwind CSS:**
- Guidelines for Tailwind 3.x and 4.x
- Class suggestion and completion
- Responsive design patterns

**Alpine.js:**
- Integration patterns
- Component interactivity
- State management

**Laravel:**
- Framework versions 10.x, 11.x, 12.x
- Eloquent ORM
- Blade templating

**Testing:**
- Pest 3.x and 4.x guidelines
- Feature and unit testing
- Browser testing

## Setup for TALL Stack Projects

### Installation

```bash
# Install Laravel Boost (dev dependency)
composer require laravel/boost --dev

# Run installation wizard
php artisan boost:install
```

This will:
1. Create `.ai/` directory structure
2. Install MCP server configuration
3. Set up IDE integration
4. Configure version-specific guidelines

### Directory Structure

After installation:
```
your-laravel-project/
├── .ai/
│   ├── guidelines/           # Custom AI guidelines
│   │   ├── tall-stack.blade.php
│   │   ├── livewire-patterns.blade.php
│   │   ├── tailwind-conventions.blade.php
│   │   └── alpine-interactions.blade.php
│   ├── resources/
│   └── boost.json            # Boost configuration
├── .claude/                  # Your existing Claude prompts
└── ...
```

## Custom TALL Stack Guidelines

### Creating Custom Guidelines

Place `.blade.php` files in `.ai/guidelines/` to provide TALL-specific instructions:

**Example: `.ai/guidelines/tall-stack.blade.php`**
```blade
# TALL Stack Development Guidelines

## Architecture

This project uses the TALL Stack:
- **Tailwind CSS** for styling (utility-first)
- **Alpine.js** for interactivity (lightweight JS)
- **Laravel** for backend ({{ app()->version() }})
- **Livewire** for reactive components (version 3.x)

## Component Patterns

### Livewire Components
- Use full-page components for routes
- Nested components for reusable UI
- Keep components thin, business logic in actions/services
- Use computed properties for derived data
- Wire:model.defer for better performance

### Tailwind Styling
- Mobile-first responsive design
- Use @apply sparingly (prefer utilities)
- Consistent spacing scale
- Dark mode support via class strategy

### Alpine.js Integration
- Use for client-side interactions only
- Avoid duplicating Livewire state
- Common uses: modals, dropdowns, tooltips
- x-data for component scope
- x-cloak to prevent flash

## Livewire Best Practices

@foreach($this->livewirePatterns() as $pattern)
- {{ $pattern }}
@endforeach

## Testing Strategy
- Feature tests for Livewire components
- Browser tests for complex interactions
- Unit tests for business logic
```

### Pre-built Guidelines to Enable

Boost includes guidelines for TALL components. Ensure these are activated:

1. **Laravel Framework**: Automatically enabled based on version
2. **Livewire 3.x**: Enable for component generation
3. **Tailwind 3.x/4.x**: Enable for styling assistance
4. **Pest 3.x/4.x**: Enable for testing
5. **Alpine.js**: Custom guidelines (create your own)

## Integration with Existing .claude Setup

### Complementary Use

Laravel Boost **complements** your existing `.claude/` agents and commands:

**.claude/** - High-level patterns and workflows
- Agents for architectural decisions
- Commands for scaffolding
- Patterns and conventions
- Project-specific customizations

**.ai/** - Context-aware code generation
- Real-time application context
- Version-specific guidelines
- Documentation search
- Code execution and testing

### Workflow Example

1. **User asks**: "Create a product listing with search and filters"

2. **Claude uses .claude/ agents**:
   - `/tall-crud` command for scaffolding
   - Follows your custom patterns

3. **Claude uses Boost MCP tools**:
   - Reads database schema for Product model
   - Checks installed Livewire version
   - Searches Tailwind docs for component patterns
   - Tests generated code via Tinker

4. **Result**: Context-aware, version-correct, tested code

## Boost MCP Tools for TALL Development

### Application Context

```php
// Boost automatically provides:
- PHP version
- Laravel version
- Installed packages (Livewire, Tailwind, Alpine)
- Database structure
- Eloquent models
```

**Use case**: Generate components that match your exact environment

### Database Tools

```php
// Inspect schema before generating CRUD
$tables = boost()->database()->tables();
$columns = boost()->database()->columns('products');
$relationships = boost()->database()->relationships('Product');
```

**Use case**: Generate accurate migrations and models

### Route Discovery

```php
// Find existing routes to avoid conflicts
$routes = boost()->routes()->list();
$routeNames = boost()->routes()->byName('products.*');
```

**Use case**: Generate unique route names and controllers

### Configuration Access

```php
// Read Livewire configuration
$livewireConfig = boost()->config('livewire');
$tailwindConfig = boost()->config('tailwind');
```

**Use case**: Respect project-specific configurations

### Documentation Search

```php
// Semantic search across docs
$livewireDocs = boost()->docs()->search('file uploads livewire 3');
$tailwindDocs = boost()->docs()->search('responsive grid tailwind');
```

**Use case**: Generate code following latest best practices

### Tinker REPL

```php
// Test code before generating
boost()->tinker('User::count()');
boost()->tinker('Product::with("categories")->first()');
```

**Use case**: Verify relationships and queries work

## Best Practices for TALL + Boost

### 1. Layer Your Guidelines

**Generic (.ai/guidelines/tall-stack.blade.php):**
- TALL architecture overview
- Component patterns
- Naming conventions

**Specific (.ai/guidelines/livewire-patterns.blade.php):**
- Livewire 3.x specific patterns
- Wire:model usage
- Component lifecycle

**Project (.ai/guidelines/my-project.blade.php):**
- Custom business logic patterns
- Domain-specific rules
- Team conventions

### 2. Use Boost Tools Proactively

Before generating code, Boost can:
- Check database schema
- Verify route availability
- Confirm package versions
- Search relevant docs
- Test code snippets

### 3. Combine with Claude Agents

**Scenario**: Generate a complex form with file uploads

1. **Claude Agent** (`.claude/commands/tall-new-component.md`):
   - Provides high-level structure
   - Follows your team patterns

2. **Boost MCP**:
   - Checks Livewire 3.x file upload syntax
   - Verifies storage configuration
   - Generates version-correct code

3. **Result**: Perfect integration

### 4. Maintain Guidelines

Keep `.ai/guidelines/` in sync with your project:
- Update when adopting new patterns
- Add team-specific conventions
- Document architectural decisions
- Version-control the guidelines

## Troubleshooting

### Boost Not Working

1. **Check installation**:
   ```bash
   php artisan boost:status
   ```

2. **Verify MCP configuration**:
   - Claude Code should auto-detect
   - Check IDE MCP settings if manual

3. **Test tools**:
   ```bash
   php artisan boost:test
   ```

### Guidelines Not Applied

1. **Check file location**: Must be in `.ai/guidelines/`
2. **Use .blade.php extension**: Required for parsing
3. **Restart Claude Code**: Reload configuration
4. **Check syntax**: Valid Blade syntax required

### Conflicts with .claude

No conflicts! They work together:
- `.claude/` for patterns and commands
- `.ai/` for context and guidelines

## Advanced: Custom Boost Tools

You can extend Boost with custom MCP tools:

```php
// app/Boost/TallStackTool.php
use Laravel\Boost\Contracts\McpClient;

class TallStackTool implements McpClient
{
    public function getLivewireComponents(): array
    {
        // Custom logic to discover Livewire components
    }

    public function getAlpineComponents(): array
    {
        // Custom logic for Alpine.js patterns
    }
}

// Register in service provider
Boost::registerTool(TallStackTool::class);
```

## Performance Considerations

Boost is a **development tool** (dev dependency):
- Not loaded in production
- No runtime performance impact
- Helps generate optimized code

## Security

Boost respects Laravel security:
- Read-only access to code and config
- Tinker runs in dev mode only
- Environment variables masked
- No external data transmission

## Documentation

- [Laravel Boost GitHub](https://github.com/laravel/boost)
- [MCP Protocol Docs](https://modelcontextprotocol.io)
- [Livewire Integration](https://livewire.laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)

## Summary

Laravel Boost + TALL Stack = **Supercharged AI Development**

**Boost provides**:
- Real-time application context
- Version-specific guidelines
- 15+ specialized MCP tools
- Documentation semantic search

**Your .claude/ setup provides**:
- High-level patterns
- Scaffolding commands
- Team conventions
- Project architecture

**Together they enable**:
- Faster development
- Context-aware code generation
- Version-correct implementations
- Tested, production-ready code

Use Boost to make Claude Code understand your TALL Stack project like a senior developer who knows every detail of your codebase.
