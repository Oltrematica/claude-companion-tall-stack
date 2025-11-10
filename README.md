# TALL Stack AI Assistant

A complete AI agent system for Claude Code, optimized for developing **TALL Stack** applications (Tailwind CSS, Alpine.js, Laravel, Livewire) with native **Laravel Boost MCP** integration.

## 🎯 Features

- **11 Specialized Agents**: Dedicated experts for Laravel, Livewire, Filament, Security, Testing, and more
- **17+ Slash Commands**: Quick commands for CRUD, Filament resources, widgets, optimization, and deployment
- **Filament 4.x Integration**: Complete support for the leading TALL Stack admin panel
- **Laravel Reverb Support**: Native WebSocket patterns for real-time features
- **Modern Testing**: Pest 3.x with architecture testing examples
- **Laravel Boost MCP Integration**: Leverage Boost's MCP tools for context-aware development
- **Reusable Patterns**: 11+ architectural patterns, conventions, and examples
- **4 Starter Kits**: SaaS, Blog, E-commerce, and Dashboard templates
- **Laravel 12 Ready**: Updated for the latest stable Laravel, Livewire, and ecosystem
- **Best Practices**: Follow official conventions and community best practices
- **Complete**: Covers the entire development cycle, from setup to deployment
- **Modular**: Easily extendable and customizable

## 📋 Prerequisites

- [Claude Code](https://claude.ai/claude-code) installed
- Laravel 11+ or 12+ project with Livewire 3.5+
- Node.js 20+ and NPM for asset management
- PHP 8.3+
- **(Optional)** [Laravel Boost](https://github.com/laravel/boost) for MCP context-aware development
- **(Optional)** [Filament 4.x](https://filamentphp.com) for advanced admin panels

## 🚀 Quick Start

### 1. Base Installation

Clone or copy the `.claude` folder to the root of your Laravel project:

```bash
# If this is a separate repository
cp -r /path/to/tall-stack-ai-assistant/.claude /path/to/your-laravel-project/

# Or initialize directly in your project
cd /path/to/your-laravel-project
mkdir -p .claude/{agents,commands,prompts}
```

### 2. (Optional) Laravel Boost Setup

For an enhanced AI experience with context awareness:

```bash
# Install Laravel Boost
composer require laravel/boost --dev
php artisan boost:install

# Configure with slash command
/boost-setup
```

### 3. Project Structure

**Base Setup (.claude/):**
```
your-laravel-project/
├── .claude/
│   ├── agents/                              # 11 specialized agents
│   │   ├── tall-stack.md                    # Main coordinator
│   │   ├── tall-stack-laravel.md            # Laravel expert
│   │   ├── tall-stack-livewire.md           # Livewire expert
│   │   ├── tall-stack-frontend.md           # UI/UX expert
│   │   ├── tall-stack-database.md           # Database expert
│   │   ├── tall-stack-security.md           # Security expert
│   │   ├── tall-stack-testing.md            # Testing expert
│   │   ├── filament-expert.md               # 🆕 Filament 4 specialist
│   │   └── boost-mcp-integration.md         # Boost MCP guide
│   ├── commands/                            # 17+ slash commands
│   │   ├── tall-new-component.md
│   │   ├── tall-crud.md
│   │   ├── tall-refactor.md
│   │   ├── tall-security.md
│   │   ├── tall-api.md
│   │   ├── tall-search.md
│   │   ├── tall-export.md
│   │   ├── tall-optimize.md
│   │   ├── tall-test.md
│   │   ├── tall-monitor.md
│   │   ├── tall-deploy.md
│   │   ├── boost-setup.md
│   │   ├── filament-setup.md                # 🆕 Filament installation wizard
│   │   ├── filament-resource.md             # 🆕 Generate Filament resources
│   │   └── filament-widget.md               # 🆕 Create dashboard widgets
│   ├── prompts/                             # Reusable patterns & conventions
│   │   ├── patterns/
│   │   │   ├── service-pattern.md
│   │   │   ├── repository-pattern.md
│   │   │   ├── action-pattern.md
│   │   │   ├── dto-pattern.md
│   │   │   └── reverb-broadcasting.md       # 🆕 WebSocket patterns
│   │   ├── conventions/
│   │   │   ├── naming.md
│   │   │   ├── coding-standards.md
│   │   │   └── git-workflow.md
│   │   └── examples/
│   │       ├── livewire-data-table.md
│   │       ├── livewire-modal-form.md
│   │       └── pest-architecture-tests.md   # 🆕 Pest 3.x testing
│   └── .ai-guidelines-examples/             # Boost guidelines templates
│       ├── tall-stack.blade.php
│       └── README.md
├── app/
├── resources/
└── ...
```

**With Laravel Boost (.ai/):**
```
your-laravel-project/
├── .ai/                                      # Created by boost:install
│   ├── guidelines/
│   │   └── tall-stack.blade.php             # Your TALL patterns
│   └── boost.json
├── .claude/                                  # Your prompts
└── ...
```

### 4. What's New in 3.0 🎉

**Major Updates:**

1. **Filament 4.x Integration** - Complete admin panel support
   ```bash
   /filament-setup    # Install and configure Filament 4
   /filament-resource # Generate CRUD resources
   /filament-widget   # Create dashboard widgets
   ```

2. **Laravel Reverb (WebSockets)** - Native real-time features
   - Real-time notifications
   - Live data tables
   - Chat applications
   - Presence channels

3. **Pest 3.x Architecture Testing** - Enforce code quality
   - Layer dependency tests
   - Naming convention tests
   - Security rule enforcement
   - TALL Stack specific patterns

4. **Laravel 12 & PHP 8.3** - Latest stable versions
   - Updated all examples
   - New Laravel 12 features
   - Performance improvements

5. **Enhanced Documentation** - More examples and patterns
   - 11+ reusable patterns
   - WebSocket implementation guides
   - Modern testing strategies
   - Production deployment patterns

See [CHANGELOG.md](CHANGELOG.md) for complete details and [EVOLUTION.md](EVOLUTION.md) for the roadmap.

### 5. First Use

**Standard setup:**
```
/tall-crud
```

**With Laravel Boost:**
```bash
# 1. Setup Boost
/boost-setup

# 2. Verify context awareness
What Livewire version is installed?

# 3. Use TALL commands with context
/tall-crud
```

## 📚 Documentation

### Available Agents

#### 🎯 Main Agent: `tall-stack`

The coordinator agent for architectural decisions and general questions.

**Example usage:**
```
What's the best way to structure a multi-tenant application in the TALL Stack?
```

#### 🔧 Specialized Sub-Agents

1. **`tall-stack-laravel`** - Backend Expert
   - Database design and Eloquent
   - Query optimization
   - Jobs, queues, events
   - API development
   - Testing

2. **`tall-stack-livewire`** - Reactive Components Expert
   - Livewire 3.x components
   - Data binding and validation
   - Event handling
   - File uploads
   - Performance optimization

3. **`tall-stack-frontend`** - UI/UX Expert
   - Tailwind CSS patterns
   - Alpine.js interactivity
   - Responsive design
   - Accessibility
   - Component styling

4. **`tall-stack-database`** - Database Specialist
   - Query optimization
   - N+1 problem solving
   - Indexing strategies
   - Complex relationships
   - Performance tuning

5. **`tall-stack-security`** - Security Expert
   - OWASP Top 10
   - Authentication & authorization
   - Input validation
   - Secure coding practices
   - Security audits

6. **`tall-stack-testing`** - Testing Expert
   - PHPUnit/Pest tests
   - Livewire testing
   - Browser testing (Dusk)
   - TDD/BDD practices
   - Test coverage

7. **`boost-mcp-integration`** - Laravel Boost Expert
   - MCP server configuration
   - AI Guidelines setup
   - Context-aware development
   - Tool integration
   - Best practices

8. **`filament-expert`** 🆕 - Filament Admin Panel Expert
   - Filament 4.x configuration
   - Resource generation (CRUD)
   - Form and Table builders
   - Custom widgets and pages
   - Theme customization
   - Plugin ecosystem integration

### Slash Commands

#### `/tall-new-component`

Create a new Livewire component with Tailwind styling.

**Generates:**
- PHP component class
- Blade view with Tailwind
- Validation rules
- Event handling
- Loading states

---

#### `/tall-crud`

Generate a complete CRUD interface for a model.

**Generates:**
- Model with migration
- Factory and seeder
- Livewire components (List, Create/Edit)
- Routes
- Views with Tailwind
- Validation and authorization

---

#### `/tall-refactor`

Refactor existing components following best practices.

**Analyzes:**
- Performance issues
- Code quality
- Security concerns
- Best practices violations
- Optimization opportunities

---

#### `/tall-security`

Comprehensive security audit for your application.

**Checks:**
- OWASP Top 10 vulnerabilities
- Authentication & authorization
- Input validation
- Data protection
- Configuration security
- Dependencies

---

#### `/tall-api`

Generate RESTful API resources for models.

**Creates:**
- API Resource classes
- API Controller
- Form Requests
- Routes
- Policy
- API tests

---

#### `/tall-search`

Add full-text search functionality to models.

**Implements:**
- Laravel Scout setup
- Meilisearch/Algolia integration
- Livewire search component
- Filters and facets
- Real-time search

---

#### `/tall-export`

Add data export functionality (CSV, Excel, PDF).

**Creates:**
- Export classes
- Livewire export component
- Multiple format support
- Queued exports for large datasets
- PDF templates

---

#### `/tall-refactor`

Refactor existing components following best practices.

**Analyze:**
- Performance issues
- Code quality
- Security concerns
- Best practices violations
- Optimization opportunities

---

#### `/tall-security`

Complete security audit for your application.

**Check:**
- OWASP Top 10 Vulnerabilities
- Authentication & authorization
- Input validation
- Data protection
- Configuration security

---

#### `/tall-api`

Generate RESTful API resources for models.

**Crea:**
- API Resource Classes
- API Controller
- Form Requests
- Routes
- Policy
- API tests

---

#### `/tall-search`

Add full-text search capabilities to your models.

**Implement:**
- Setup Laravel Scout
- Meilisearch/Algolia integration
- Livewire Component for search
- Facets and filters
- Real time search

---

#### `/tall-export`

Add data export functionality (CSV, Excel, PDF).

**Creates:**
- Export classes
- Livewire export component
- Support for multiple formats
- Queued export for large datasets
- PDF template

***

#### `/tall-monitor`

Set up performance monitoring and error tracking.

**Configures:**
- Laravel Telescope
- Laravel Pulse
- Sentry integration
- Health check endpoints
- Performance metrics

***

#### `/tall-optimize`

Analyze and optimize application performance.

**Analyzes:**
- Database queries (N+1 problems)
- Livewire component performance
- Frontend optimization
- Caching opportunities
- Code quality

***

#### `/tall-monitor`

Set up performance monitoring and error tracking.

**Configures:**
- Laravel Telescope
- Laravel Pulse
- Sentry integration
- Health check endpoints
- Performance metrics

***

#### `/tall-test`

Generate comprehensive tests for components and features.

**Creates:**
- Feature tests for Livewire
- Unit tests for models
- Validation tests
- Authorization tests
- Browser tests (optional)

***

#### `/tall-deploy`

Complete guide for production deployment.

**Includes:**
- Pre-deployment checklist
- Server configuration
- Queue workers setup
- SSL certificates
- Monitoring
- Rollback plan

***

#### `/boost-setup`

Complete wizard to configure Laravel Boost MCP with TALL Stack.

**Performs:**
- Install Laravel Boost
- Configure MCP server for Claude Code
- Set up TALL Stack AI guidelines
- Test integration
- Team documentation

---

#### `/filament-setup` 🆕

Complete Filament 4.x installation and configuration wizard.

**Performs:**
- Install Filament 4 via Composer
- Configure admin panel
- Create admin user
- Set up custom theme (optional)
- Install essential plugins (Shield, Breezy, Media Library)
- Generate first resource
- Configure security and optimization

---

#### `/filament-resource` 🆕

Generate a complete CRUD resource for Filament 4.

**Generates:**
- Resource class with form and table
- Model (if not exists)
- Migration with proper schema
- Policy for authorization
- Tests for the resource
- Advanced filters and bulk actions
- Media uploads support
- SEO fields

---

#### `/filament-widget` 🆕

Create custom dashboard widgets for Filament.

**Types:**
- Stats widgets (KPIs, metrics)
- Chart widgets (line, bar, pie, doughnut)
- Table widgets (recent data)
- Custom widgets (fully customizable)

**Features:**
- Real-time updates
- Filters
- Interactive elements
- Performance optimization

## 🔋 Laravel Boost Integration

### What is Laravel Boost?

Laravel Boost is a **MCP (Model Context Protocol) server** that equips Claude Code with over 15 specialized tools to understand your Laravel project in real-time.

### Why Use It with TALL Stack?

**Context Awareness:**
- Claude knows your Livewire version
- Reads your actual database schema
- Accesses current configuration
- Searches versioned documentation

**Available MCP Tools:**
1. **Application Context:** PHP/Laravel versions, packages, models
2. **Database Operations:** Schema inspection, query execution
3. **Code Discovery:** Routes, commands, configuration
4. **Development Utilities:** Logs, Tinker REPL, URL generation
5. **Documentation API:** 17,000+ Laravel docs with semantic search

### Quick Setup

```bash
# 1. Install
composer require laravel/boost --dev
php artisan boost:install

# 2. Configure for TALL Stack
/boost-setup

# 3. Copy guidelines
cp .claude/.ai-guidelines-examples/tall-stack.blade.php .ai/guidelines/
```

### Workflow with Boost

```
User: "Create a product CRUD with image upload"

1. Claude uses .claude/commands/tall-crud
   ↓ Scaffolding pattern

2. Boost MCP provides context
   ↓ Livewire 3.x, database schema

3. Claude reads .ai/guidelines/tall-stack.blade.php
   ↓ File upload pattern, validations

4. Generated code
   ✅ Version-correct
   ✅ Schema-aware
   ✅ Pattern-following
   ✅ Production-ready
```

## 💡 Practical Examples

### Creating a Blog Post Manager

```
/tall-crud

# When prompted:
Model: Post
Fields:
  - title: string, required, min:3
  - slug: string, unique
  - content: text, required
  - published_at: timestamp, nullable
Relationships:
  - belongsTo User
Soft deletes: Yes
```

### With Laravel Boost Context

```
# First: Boost analyzes database
What tables exist in the database?

# Claude responds with actual schema

# Then: Generate context-aware CRUD
/tall-crud Post

# Result: CRUD perfectly integrated with existing schema
```

### Creating a Delete Confirmation Modal

```
/tall-new-component

# When prompted:
Name: DeleteConfirmation
Type: modal
Features:
  - Accept item ID
  - Show item details
  - Confirm/Cancel buttons
  - Emit event on delete
```

### Optimizing a Slow Component

```
/tall-optimize

# Claude will analyze the project and suggest:
- Missing eager loading
- Computed properties to add
- Lazy loading for heavy components
- Caching strategies
```

## 🎨 Recommended Workflow

### Without Laravel Boost

```bash
# 1. Setup
composer create-project laravel/laravel my-app
cd my-app
composer require livewire/livewire
npm install -D tailwindcss
cp -r /path/to/.claude .

# 2. Development
/tall-crud Product
/tall-new-component ProductCard
/tall-test

# 3. Deploy
/tall-optimize
/tall-deploy
```

### With Laravel Boost

```bash
# 1. Enhanced Setup
composer create-project laravel/laravel my-app
cd my-app
composer require livewire/livewire
npm install -D tailwindcss
cp -r /path/to/.claude .
/boost-setup

# 2. Context-Aware Development
# Claude now knows everything about your project
Create a product management system with categories

# 3. Deploy
/tall-optimize
/tall-deploy
```

## 🔧 Customization

### Adding Your Project Patterns

**Without Boost** - Edit `.claude/agents/tall-stack.md`:

```markdown
## My Project Patterns

### Authentication
We use Laravel Sanctum with custom guards...
```

**With Boost** - Edit `.ai/guidelines/tall-stack.blade.php`:

```blade
## My Project Patterns

### Current Setup
- Laravel: {{ app()->version() }}
- Livewire: @if(class_exists('Livewire\Livewire')) 3.x @endif

### Our Conventions
- Components: PascalCase
- Methods: camelCase
```

### Creating Custom Commands

Create `.claude/commands/my-custom-command.md`:

```markdown
---
description: My custom operation
---

Detailed instructions for Claude on what to do...
```

## 📦 Tech Stack

This system is optimized for:

- **Laravel** 11+ / 12+ (latest stable)
- **Livewire** 3.5+
- **Tailwind CSS** 3.4+
- **Alpine.js** 3.14+
- **PHP** 8.3+
- **Pest** 3.x (recommended for testing)
- **Filament** 4.x (optional, for admin panels)

### Laravel Boost (Optional but Recommended)

**What It Adds:**
- **MCP Server:** 15+ tools for context awareness
- **AI Guidelines:** Blade templates for custom patterns
- **Documentation API:** Semantic search in 17K+ docs
- **Version Awareness:** Code specific to your versions

**When to Use:**
- ✅ Medium/large projects
- ✅ Teams sharing conventions
- ✅ Need for version-specific code generation
- ✅ Complex database schemas
- ❌ Tiny/prototype projects (overkill)

**Benefits:**
1. **Faster Development:** Eliminates guesswork
2. **Better Code Quality:** Version-correct, schema-aware
3. **Team Consistency:** Shared AI guidelines
4. **Learning Curve:** AI understands your codebase

## 📄 License

MIT License — Free to use and modify for your projects!

## 🌟 Credits

Created to streamline TALL Stack development with Claude AI assistance.

### Useful Links

#### Laravel Ecosystem
- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Laravel Boost](https://github.com/laravel/boost) - MCP server for AI development
- [Laravel Reverb](https://laravel.com/docs/12.x/reverb) - Native WebSocket server
- [Livewire 3.x Documentation](https://livewire.laravel.com/docs)
- [Filament 4.x Documentation](https://filamentphp.com/docs)

#### Frontend
- [Tailwind CSS 3.4 Documentation](https://tailwindcss.com/docs)
- [Alpine.js 3.14 Documentation](https://alpinejs.dev)

#### Testing
- [Pest 3.x Documentation](https://pestphp.com/docs)
- [Pest Architecture Plugin](https://pestphp.com/docs/arch-testing)

#### Tools & Resources
- [Claude Code Documentation](https://docs.claude.com/claude-code)
- [Model Context Protocol](https://modelcontextprotocol.io)

#### Community & Learning
- [Laravel News](https://laravel-news.com)
- [Laracasts](https://laracasts.com)
- [Filament Discord](https://filamentphp.com/discord)
- [Livewire Discord](https://discord.gg/livewire)

## 💬 Support

Have questions? Ask Claude Code directly using the agents!

```
How can I implement a real-time notification system in TALL Stack?
```

**With Boost MCP:** Claude can analyze your project and give specific answers!

***

**Happy Coding! 🚀**

Last updated: 2025-11-10 | Version: 3.0.0-dev (Laravel 12 & Filament 4 Ready)