# Laravel Boost AI Guidelines Examples

Questi file sono **esempi** di guidelines da copiare nella cartella `.ai/guidelines/` del tuo progetto Laravel dopo aver installato Laravel Boost.

## Come usarli

### 1. Installa Laravel Boost

```bash
composer require laravel/boost --dev
php artisan boost:install
```

### 2. Copia i file nella cartella `.ai/guidelines/`

```bash
# Dalla root del tuo progetto TALL Stack
cp .claude/.ai-guidelines-examples/tall-stack.blade.php .ai/guidelines/
```

### 3. Personalizza per il tuo progetto

Edita `.ai/guidelines/tall-stack.blade.php` e aggiungi:
- Pattern specifici del tuo team
- Convenzioni di naming custom
- Business logic patterns
- Regole di validazione comuni

## File Disponibili

### `tall-stack.blade.php`

Guidelines complete per sviluppo TALL Stack:
- Livewire component patterns
- Tailwind CSS conventions
- Alpine.js integration
- Form handling
- File uploads
- Performance optimization
- Testing patterns

**Quando usarlo**: Sempre! Questo è il file base per ogni progetto TALL Stack.

## Creare Guidelines Custom

### Struttura Base

```blade
# My Custom Guidelines

## Section Title

### Subsection

Pattern or rule here...

## Laravel Dynamic Content

You can use Laravel helpers and Blade:
- App version: {{ app()->version() }}
- Environment: {{ app()->environment() }}
- Installed packages: @foreach(...)
```

### Best Practices

1. **Organizza per Topic**: Crea file separati per concerns diversi
   - `tall-stack.blade.php` - Generale TALL
   - `authentication.blade.php` - Auth patterns
   - `api.blade.php` - API development
   - `testing.blade.php` - Testing strategies

2. **Usa Blade per Context Dinamico**: Boost può leggere configurazione Laravel
   ```blade
   @if(config('app.debug'))
   ## Development Mode
   Include detailed error messages...
   @endif
   ```

3. **Includi Esempi di Codice**: Claude Code impara meglio con esempi
   ```blade
   ## Good Pattern
   ````php
   // Code example
   ````

   ## Anti-Pattern
   ````php
   // What NOT to do
   ````
   ```

4. **Sii Specifico**: Più dettagli = codice migliore generato
   ```blade
   ❌ "Use Livewire for components"
   ✅ "Use Livewire full-page components for routes, nested components for reusable UI blocks. Keep components thin with business logic in service classes."
   ```

## Integrazione con .claude/

Laravel Boost (`.ai/`) e Claude Code prompts (`.claude/`) lavorano insieme:

**.claude/**
- High-level architectural patterns
- Scaffolding commands
- Team workflows
- Project structure

**.ai/guidelines/**
- Code-level conventions
- Framework-specific patterns
- Best practices
- Version-specific syntax

## Esempio: Workflow Completo

```
User: "Create a product CRUD with image upload"

┌─────────────────────────────────────┐
│ 1. Claude legge .claude/commands/   │
│    /tall-crud per scaffolding       │
└─────────────────────────────────────┘
           ↓
┌─────────────────────────────────────┐
│ 2. Boost MCP fornisce:              │
│    - Livewire version (3.x)         │
│    - Database schema                │
│    - Installed packages             │
└─────────────────────────────────────┘
           ↓
┌─────────────────────────────────────┐
│ 3. Claude legge .ai/guidelines/     │
│    tall-stack.blade.php per:        │
│    - File upload pattern            │
│    - Validation rules               │
│    - Tailwind form styling          │
└─────────────────────────────────────┘
           ↓
┌─────────────────────────────────────┐
│ 4. Codice generato è:               │
│    ✅ Version-correct                │
│    ✅ Follows your patterns          │
│    ✅ Uses correct schema            │
│    ✅ Production-ready               │
└─────────────────────────────────────┘
```

## Troubleshooting

### Guidelines non applicate

1. **Verifica posizione file**: Deve essere in `.ai/guidelines/`
2. **Estensione corretta**: Usa `.blade.php`
3. **Riavvia Claude Code**: Per ricaricare configurazione
4. **Controlla sintassi Blade**: Errori prevengono il parsing

### Conflitti tra Guidelines

L'ordine di precedenza è:
1. `.ai/guidelines/` (custom - massima priorità)
2. Boost built-in guidelines
3. `.claude/` prompts (high-level)

Se hai conflitti, la tua custom guideline vince.

## Risorse

- [Laravel Boost Documentation](https://github.com/laravel/boost)
- [Model Context Protocol](https://modelcontextprotocol.io)
- [Blade Templates](https://laravel.com/docs/blade)

## Contribuire

Se crei guidelines utili per TALL Stack, considera di condividerle con la community!
