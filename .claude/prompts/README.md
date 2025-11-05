# Prompts Directory

This folder contains **reusable prompts** for common TALL Stack operations.

## 📝 What Are Prompts?

Prompts are reusable text snippets that you can reference during conversations with Claude Code to:
- Define project-specific context
- Establish coding conventions
- Provide examples of recurring patterns
- Document architectural decisions

## 🎯 Difference Between Prompts and Commands

| Prompts | Slash Commands |
|---------|----------------|
| Reusable context fragments | Complete automated actions |
| Combine with your requests | Execute autonomously |
| Provide guidelines | Generate code |
| Flexible and composable | Structured and complete |

**Example:**
- **Prompt**: "Follow the service pattern from `.claude/prompts/patterns/service-pattern.md`"
- **Command**: `/tall-crud Product` (generates complete CRUD)

## 📁 Structure

```
prompts/
├── patterns/              # 4 Architectural patterns
│   ├── service-pattern.md
│   ├── repository-pattern.md
│   ├── action-pattern.md
│   └── dto-pattern.md
├── conventions/           # 3 Coding conventions
│   ├── naming.md
│   ├── coding-standards.md
│   └── git-workflow.md
└── examples/              # 2 Complete examples
    ├── livewire-data-table.md
    └── livewire-modal-form.md
```

**11 comprehensive prompts included** - Ready to use and customize!

## 🔧 How to Use

### 1. Reference in Conversation

```
Create a new ProductService following the pattern in
.claude/prompts/patterns/service-pattern.md
```

### 2. Combine Multiple Prompts

```
Create a User management system following:
- Service pattern from prompts/patterns/service-pattern.md
- Naming conventions from prompts/conventions/naming.md
```

### 3. Extend with Project Context

```
Refactor OrderController to use the service pattern.
Our project-specific requirements:
- All services must log operations
- Use transactions for multi-step operations
```

## ✍️ Creating Custom Prompts

### Pattern Prompt Template

```markdown
# [Pattern Name] Pattern

## When to Use
Description of when this pattern is appropriate.

## Structure
```php
// Code structure example
```

## Example Implementation
```php
// Complete example
```

## Best Practices
- Point 1
- Point 2

## Common Pitfalls
- What to avoid
```

### Convention Prompt Template

```markdown
# [Convention Name] Conventions

## Rules
1. Rule description with examples
2. Another rule

## Examples

### ✅ Good
```php
// Correct example
```

### ❌ Bad
```php
// Incorrect example
```

## 🎨 Available Prompts

### Patterns (`patterns/`) - 4 Files

1. **[service-pattern.md](patterns/service-pattern.md)** - Service layer for business logic
   - When to use services vs models
   - Dependency injection
   - Testing services
   - Best practices

2. **[repository-pattern.md](patterns/repository-pattern.md)** - Data access abstraction
   - Repository interfaces
   - Caching decorators
   - Criteria pattern
   - Testing repositories

3. **[action-pattern.md](patterns/action-pattern.md)** - Single-purpose actions
   - Invokable actions
   - Chainable actions
   - Result objects
   - Usage examples

4. **[dto-pattern.md](patterns/dto-pattern.md)** - Data transfer objects
   - Immutable DTOs
   - Validation in DTOs
   - Type safety
   - Laravel Data package

### Conventions (`conventions/`) - 3 Files

1. **[naming.md](conventions/naming.md)** - Naming conventions
   - PHP classes and methods
   - Database tables and columns
   - Livewire components
   - Routes and URLs
   - Complete quick reference

2. **[coding-standards.md](conventions/coding-standards.md)** - Code quality standards
   - PSR-12 compliance
   - Laravel best practices
   - Livewire patterns
   - Security guidelines
   - Testing standards

3. **[git-workflow.md](conventions/git-workflow.md)** - Git and collaboration
   - Git Flow branching
   - Commit message format
   - Pull request workflow
   - Code review checklist
   - CI/CD integration

### Examples (`examples/`) - 2 Files

1. **[livewire-data-table.md](examples/livewire-data-table.md)** - Complete data table
   - Real-time search and filters
   - Sortable columns
   - Pagination
   - Bulk actions
   - Export functionality
   - 400+ lines of production-ready code

2. **[livewire-modal-form.md](examples/livewire-modal-form.md)** - Modal form component
   - Alpine.js animations
   - Form validation
   - File upload with preview
   - Event handling
   - Reusable modal component

## 💡 Tips

### Combine with AI Guidelines

If using Laravel Boost, prompts complement `.ai/guidelines/`:
- **Prompts**: General patterns and conventions
- **Guidelines**: Project-specific, context-aware patterns

### Version Control

Keep prompts in version control so the team shares:
- Coding standards
- Architectural patterns
- Project conventions

### Keep Updated

Review and update prompts as the project evolves:
- New patterns emerge
- Conventions change
- Team feedback

## 📚 Related

- **Slash Commands** (`../commands/`): For automated operations
- **Agents** (`../agents/`): For specialized assistance
- **AI Guidelines** (`.ai/guidelines/`): For Boost integration

## 🤝 Contributing

To add a new prompt:

1. Create markdown file in appropriate directory
2. Follow the template above
3. Include practical examples
4. Document when to use/not use
5. Add to this README

---

**Need help?** Ask Claude Code:
```
Help me create a prompt for [your pattern]
```
