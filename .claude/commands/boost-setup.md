# Laravel Boost Setup for TALL Stack

Complete setup and integration of Laravel Boost MCP server for optimal TALL Stack development with Claude Code.

## What This Command Does

This command guides you through:
1. Installing Laravel Boost
2. Configuring MCP server for Claude Code
3. Setting up TALL Stack specific guidelines
4. Testing the integration
5. Optimizing for your project

## Prerequisites Check

Before starting, verify:
- [ ] Laravel 10+ project
- [ ] Composer installed
- [ ] Claude Code installed and configured
- [ ] TALL Stack components (Livewire, Tailwind, Alpine) installed

## Step 1: Install Laravel Boost

Run the installation command:

```bash
composer require laravel/boost --dev
```

**Expected output**: Laravel Boost package installed as dev dependency

## Step 2: Run Boost Installation Wizard

```bash
php artisan boost:install
```

This will:
- Create `.ai/` directory structure
- Set up MCP server configuration
- Configure IDE integration (Claude Code auto-detected)
- Install base guidelines

**Expected structure created:**
```
.ai/
├── guidelines/
├── resources/
└── boost.json
```

## Step 3: Verify Installation

Check that Boost is properly installed:

```bash
php artisan boost:status
```

**Expected**: Status shows MCP server running and tools available

## Step 4: Set Up TALL Stack Guidelines

Copy the TALL Stack guidelines from your `.claude/` repository:

```bash
# From project root
cp .claude/.ai-guidelines-examples/tall-stack.blade.php .ai/guidelines/
```

**What this guideline provides:**
- Livewire 3.x component patterns
- Tailwind CSS utility-first conventions
- Alpine.js integration best practices
- Form handling patterns
- File upload strategies
- Performance optimization rules

## Step 5: Customize Guidelines for Your Project

Edit `.ai/guidelines/tall-stack.blade.php` to add:

### Project-Specific Patterns

Add your team's conventions:
```blade
## Our Project Patterns

### Component Naming
- List components: `[Model]Table` (e.g., ProductTable)
- Form components: `[Model]Form` (e.g., ProductForm)
- Modal components: `[Purpose]Modal` (e.g., DeleteConfirmationModal)

### File Organization
- Components in: `app/Http/Livewire/[Feature]/`
- Views in: `resources/views/livewire/[feature]/`
```

### Business Rules
```blade
## Business Logic

### User Roles
We use Spatie Laravel Permission with roles:
- Admin: Full access
- Manager: Can manage products and orders
- Staff: Can view and update orders
- Customer: Customer portal access

When generating authorization code, use these role names.
```

### Database Conventions
```blade
## Database

### Naming Conventions
- Tables: plural snake_case (e.g., `user_products`)
- Foreign keys: `[singular_table]_id` (e.g., `user_id`)
- Pivot tables: alphabetical order (e.g., `category_product` not `product_category`)

### Soft Deletes
All user-facing models use soft deletes. Include in migrations:
```php
$table->softDeletes();
```
```

## Step 6: Test Boost Integration

Ask Claude Code a context-aware question to test Boost MCP:

```
What Livewire version is installed in this project?
```

**Claude should respond with**: Your actual Livewire version (using Boost MCP tool)

```
Show me the database schema for the users table
```

**Claude should respond with**: Actual table structure from your database

```
Generate a Livewire component for managing products
```

**Claude should generate**: Code matching your Livewire version and project conventions

## Step 7: Configure Additional Guidelines (Optional)

Create specialized guidelines for different aspects:

### Authentication Patterns
`.ai/guidelines/authentication.blade.php`
```blade
# Authentication Patterns

## Login Flow
We use Laravel Breeze with Livewire for authentication.

## Password Requirements
- Minimum 12 characters
- Must include: uppercase, lowercase, number, special char
- Validated using: `Password::min(12)->mixedCase()->numbers()->symbols()`
```

### API Development
`.ai/guidelines/api.blade.php`
```blade
# API Development

## API Version
Current API version: v1

## Authentication
We use Laravel Sanctum for API authentication.

## Response Format
All API responses follow JSON:API specification.
```

### Testing Standards
`.ai/guidelines/testing.blade.php`
```blade
# Testing Standards

## Test Framework
We use Pest PHP for all tests.

## Coverage Requirements
- Livewire components: 100% coverage
- Business logic: 90%+ coverage
- Models: Relationship tests required

## Test Organization
- Feature tests: `tests/Feature/Livewire/[Component]Test.php`
- Unit tests: `tests/Unit/[Class]Test.php`
```

## Step 8: Verify MCP Tools Access

Test that Claude Code can access Boost MCP tools:

### Application Context
```
What packages are installed in this Laravel project?
```

### Database Schema
```
List all database tables and their columns
```

### Routes
```
What routes are defined in this application?
```

### Configuration
```
What is the Livewire configuration?
```

If Claude can answer these accurately, Boost MCP is working correctly!

## Step 9: Optimize for Performance

### Enable Selective Guidelines

Edit `.ai/boost.json` to enable only needed guidelines:

```json
{
  "guidelines": {
    "laravel": true,
    "livewire-3": true,
    "tailwind-3": true,
    "pest-3": true,
    "custom": {
      "tall-stack": true,
      "authentication": true,
      "testing": true
    }
  }
}
```

### Configure Documentation Search

Boost includes semantic search for Laravel docs. Configure priorities:

```json
{
  "documentation": {
    "priority": [
      "livewire",
      "laravel",
      "tailwind",
      "alpine"
    ]
  }
}
```

## Step 10: Team Onboarding

Share setup with your team:

1. **Add to repository**: Commit `.ai/` directory
   ```bash
   git add .ai/
   git commit -m "Add Laravel Boost TALL Stack guidelines"
   ```

2. **Update README**: Document Boost requirement
   ```markdown
   ## Development Setup

   1. Install dependencies: `composer install`
   2. Install Boost: Already configured, run `php artisan boost:status` to verify
   3. Open project in Claude Code: Guidelines automatically loaded
   ```

3. **Team Guidelines**: Document how to update guidelines
   ```markdown
   ## Updating AI Guidelines

   To add project patterns:
   1. Edit `.ai/guidelines/tall-stack.blade.php`
   2. Commit changes
   3. Team members: restart Claude Code to reload
   ```

## Troubleshooting

### Boost Not Detected by Claude Code

**Solution 1**: Restart Claude Code
```bash
# Close Claude Code completely and reopen
```

**Solution 2**: Verify MCP configuration
```bash
php artisan boost:config
```

**Solution 3**: Check Boost status
```bash
php artisan boost:status
```

### Guidelines Not Applied

**Check file location**:
```bash
ls -la .ai/guidelines/
# Should show: tall-stack.blade.php
```

**Check file syntax**:
```bash
php artisan view:clear
php artisan boost:validate-guidelines
```

**Restart Claude Code** to reload guidelines

### MCP Tools Not Working

**Test tools manually**:
```bash
php artisan boost:test-tools
```

**Check Laravel version compatibility**:
```bash
composer show laravel/boost
```

**Verify dev environment**:
```bash
php artisan boost:env
```

## Advanced Configuration

### Custom MCP Tools

You can add custom tools for your TALL Stack project:

**Create tool class**:
```php
// app/Boost/TallStackAnalyzer.php
namespace App\Boost;

use Laravel\Boost\Contracts\McpTool;

class TallStackAnalyzer implements McpTool
{
    public function analyze(): array
    {
        return [
            'livewire_components' => $this->getLivewireComponents(),
            'alpine_usage' => $this->getAlpineUsage(),
            'tailwind_config' => $this->getTailwindConfig(),
        ];
    }

    private function getLivewireComponents(): array
    {
        // Custom logic to scan Livewire components
    }
}
```

**Register in service provider**:
```php
use Laravel\Boost\Boost;

Boost::registerTool(TallStackAnalyzer::class);
```

### Environment-Specific Guidelines

Use Blade conditionals for environment-specific rules:

```blade
@if(app()->environment('production'))
## Production Environment

- All queries must be optimized
- N+1 queries are not acceptable
- Use caching for expensive operations
@else
## Development Environment

- Query logging enabled
- Debugging tools available
@endif
```

## Success Checklist

After completing setup, verify:

- [ ] `composer show laravel/boost` shows package installed
- [ ] `php artisan boost:status` shows MCP server running
- [ ] `.ai/guidelines/tall-stack.blade.php` exists
- [ ] Claude Code can answer context questions about your project
- [ ] Generated code follows your TALL Stack patterns
- [ ] Team members can access same guidelines from repository

## What's Next?

Now that Boost is configured:

1. **Start Developing**: Ask Claude Code to generate TALL Stack components
   ```
   Create a Livewire component for user registration with Tailwind styling
   ```

2. **Leverage MCP Tools**: Claude now has context about your project
   ```
   Generate a CRUD for products using my existing database schema
   ```

3. **Iterate on Guidelines**: As you discover patterns, add them to guidelines

4. **Share with Team**: Everyone benefits from the same AI assistance

## Resources

- **Laravel Boost**: [github.com/laravel/boost](https://github.com/laravel/boost)
- **MCP Protocol**: [modelcontextprotocol.io](https://modelcontextprotocol.io)
- **Your .claude/ Agents**: Already configured and complementary!

## Summary

Laravel Boost + Your .claude/ Setup = **Perfect TALL Stack AI Development**

**Boost provides**:
- Real-time application context (database, routes, config)
- Version-specific guidelines (Livewire 3, Tailwind 3, etc.)
- 15+ MCP tools for code generation
- Semantic documentation search

**Your .claude/ setup provides**:
- High-level architectural patterns
- Scaffolding commands (/tall-crud, etc.)
- Team conventions and workflows
- Project structure templates

**Together**: Context-aware, version-correct, pattern-following, production-ready code generated by AI that understands your TALL Stack project like a senior developer.

Happy coding! 🚀
