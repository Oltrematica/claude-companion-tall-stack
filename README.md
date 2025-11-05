# TALL Stack AI Assistant

Un sistema completo di agenti AI per Claude Code, ottimizzato per lo sviluppo di applicazioni **TALL Stack** (Tailwind CSS, Alpine.js, Laravel, Livewire) con integrazione nativa per **Laravel Boost MCP**.

## 🎯 Caratteristiche

- **Agenti Specializzati**: Esperti dedicati per Laravel, Livewire, Tailwind e Alpine.js
- **Laravel Boost MCP Integration**: Sfrutta gli strumenti MCP di Boost per sviluppo context-aware
- **Comandi Rapidi**: Slash commands per operazioni comuni TALL Stack
- **AI Guidelines**: Template per Boost guidelines personalizzate
- **Best Practices**: Seguono le convenzioni ufficiali e le best practices della community
- **Completo**: Copre tutto il ciclo di sviluppo, dal setup al deployment
- **Modulare**: Facilmente estendibile e personalizzabile

## 📋 Prerequisiti

- [Claude Code](https://claude.ai/claude-code) installato
- Progetto Laravel 10+ con Livewire 3+
- Node.js e NPM per la gestione degli asset
- **(Opzionale)** [Laravel Boost](https://github.com/laravel/boost) per MCP context-aware development

## 🚀 Quick Start

### 1. Installazione Base

Clona o copia la cartella `.claude` nella root del tuo progetto Laravel:

```bash
# Se questo è un repository separato
cp -r /path/to/tall-stack-ai-assistant/.claude /path/to/your-laravel-project/

# Oppure inizializza direttamente nel tuo progetto
cd /path/to/your-laravel-project
mkdir -p .claude/{agents,commands,prompts}
```

### 2. (Opzionale) Setup Laravel Boost

Per un'esperienza AI potenziata con context awareness:

```bash
# Installa Laravel Boost
composer require laravel/boost --dev
php artisan boost:install

# Configura con slash command
/boost-setup
```

### 3. Struttura del Progetto

**Setup Base (.claude/):**
```
your-laravel-project/
├── .claude/
│   ├── agents/
│   │   ├── tall-stack.md                    # Main coordinator
│   │   ├── tall-stack-laravel.md            # Laravel expert
│   │   ├── tall-stack-livewire.md           # Livewire expert
│   │   ├── tall-stack-frontend.md           # UI/UX expert
│   │   └── boost-mcp-integration.md         # Boost MCP guide
│   ├── commands/
│   │   ├── tall-new-component.md
│   │   ├── tall-crud.md
│   │   ├── tall-optimize.md
│   │   ├── tall-test.md
│   │   ├── tall-deploy.md
│   │   └── boost-setup.md                   # Boost setup wizard
│   └── .ai-guidelines-examples/             # Boost guidelines templates
│       ├── tall-stack.blade.php
│       └── README.md
├── app/
├── resources/
└── ...
```

**Con Laravel Boost (.ai/):**
```
your-laravel-project/
├── .ai/                                      # Created by boost:install
│   ├── guidelines/
│   │   └── tall-stack.blade.php             # Your TALL patterns
│   └── boost.json
├── .claude/                                  # Your prompts
└── ...
```

### 4. Primo Utilizzo

**Setup standard:**
```
/tall-crud
```

**Con Laravel Boost:**
```bash
# 1. Setup Boost
/boost-setup

# 2. Verifica context awareness
What Livewire version is installed?

# 3. Usa comandi TALL con context
/tall-crud
```

## 📚 Documentazione

### Agenti Disponibili

#### 🎯 Agente Principale: `tall-stack`

L'agente coordinatore per decisioni architetturali e domande generali.

**Esempio di utilizzo:**
```
Qual è il modo migliore per strutturare un'applicazione multi-tenant nel TALL Stack?
```

#### 🔧 Sub-Agents Specializzati

1. **`tall-stack-laravel`** - Esperto Backend
   - Database design e Eloquent
   - Query optimization
   - Jobs, queues, events
   - API development
   - Testing

2. **`tall-stack-livewire`** - Esperto Componenti Reattivi
   - Componenti Livewire 3.x
   - Data binding e validation
   - Event handling
   - File uploads
   - Performance optimization

3. **`tall-stack-frontend`** - Esperto UI/UX
   - Tailwind CSS patterns
   - Alpine.js interactivity
   - Responsive design
   - Accessibility
   - Component styling

4. **`boost-mcp-integration`** - Esperto Laravel Boost
   - MCP server configuration
   - AI Guidelines setup
   - Context-aware development
   - Tool integration
   - Best practices

### Slash Commands

#### `/tall-new-component`

Crea un nuovo componente Livewire con styling Tailwind.

**Genera:**
- Classe PHP del componente
- Vista Blade con Tailwind
- Validation rules
- Event handling
- Loading states

---

#### `/tall-crud`

Genera un'interfaccia CRUD completa per un model.

**Genera:**
- Model con migration
- Factory e seeder
- Componenti Livewire (List, Create/Edit)
- Routes
- Views con Tailwind
- Validation e authorization

---

#### `/tall-optimize`

Analizza e ottimizza l'applicazione per performance.

**Analizza:**
- Database queries (N+1 problems)
- Livewire component performance
- Frontend optimization
- Caching opportunities
- Code quality

---

#### `/tall-test`

Genera test completi per componenti e features.

**Crea:**
- Feature tests per Livewire
- Unit tests per models
- Test per validation
- Test per authorization
- Browser tests (optional)

---

#### `/tall-deploy`

Guida completa al deployment in produzione.

**Include:**
- Pre-deployment checklist
- Server configuration
- Queue workers setup
- SSL certificates
- Monitoring
- Rollback plan

---

#### `/boost-setup`

Wizard completo per configurare Laravel Boost MCP con TALL Stack.

**Esegue:**
- Installa Laravel Boost
- Configura MCP server per Claude Code
- Setup TALL Stack AI guidelines
- Testa integrazione
- Documenta per il team

## 🔋 Laravel Boost Integration

### Cos'è Laravel Boost?

Laravel Boost è un **MCP (Model Context Protocol) server** che fornisce a Claude Code oltre 15 strumenti specializzati per comprendere il tuo progetto Laravel in tempo reale.

### Perché Usarlo con TALL Stack?

**Context Awareness:**
- Claude conosce la tua versione di Livewire
- Legge il database schema reale
- Accede alla configurazione attuale
- Cerca documentazione versioned

**Strumenti MCP Disponibili:**
1. **Application Context**: PHP/Laravel versions, packages, models
2. **Database Operations**: Schema inspection, query execution
3. **Code Discovery**: Routes, commands, config
4. **Development Utils**: Logs, Tinker REPL, URL generation
5. **Documentation API**: 17,000+ Laravel docs con semantic search

### Setup Rapido

```bash
# 1. Installa
composer require laravel/boost --dev
php artisan boost:install

# 2. Configura per TALL Stack
/boost-setup

# 3. Copia guidelines
cp .claude/.ai-guidelines-examples/tall-stack.blade.php .ai/guidelines/
```

### Workflow con Boost

```
User: "Create a product CRUD with image upload"

1. Claude usa .claude/commands/tall-crud
   ↓ Scaffolding pattern

2. Boost MCP fornisce context
   ↓ Livewire 3.x, database schema

3. Claude legge .ai/guidelines/tall-stack.blade.php
   ↓ File upload pattern, validations

4. Codice generato
   ✅ Version-correct
   ✅ Schema-aware
   ✅ Pattern-following
   ✅ Production-ready
```

## 💡 Esempi Pratici

### Creare un Blog Post Manager

```
/tall-crud

# Quando richiesto:
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

### Con Laravel Boost Context

```
# Prima: Boost analizza database
What tables exist in the database?

# Claude risponde con schema reale

# Poi: Genera CRUD context-aware
/tall-crud Post

# Result: CRUD perfettamente integrato con schema esistente
```

### Creare un Modal per Conferma Eliminazione

```
/tall-new-component

# Quando richiesto:
Name: DeleteConfirmation
Type: modal
Features:
  - Accept item ID
  - Show item details
  - Confirm/Cancel buttons
  - Emit event on delete
```

### Ottimizzare un Componente Lento

```
/tall-optimize

# Claude analizzerà il progetto e suggerirà:
- Eager loading mancante
- Computed properties da aggiungere
- Lazy loading per componenti pesanti
- Caching strategies
```

## 🎨 Workflow Consigliato

### Senza Laravel Boost

```bash
# 1. Setup
composer create-project laravel/laravel my-app
cd my-app
composer require livewire/livewire
npm install -D tailwindcss
cp -r /path/to/.claude .

# 2. Sviluppo
/tall-crud Product
/tall-new-component ProductCard
/tall-test

# 3. Deploy
/tall-optimize
/tall-deploy
```

### Con Laravel Boost

```bash
# 1. Setup Enhanced
composer create-project laravel/laravel my-app
cd my-app
composer require livewire/livewire
npm install -D tailwindcss
cp -r /path/to/.claude .
/boost-setup

# 2. Sviluppo Context-Aware
# Claude ora conosce tutto del tuo progetto
Create a product management system with categories

# 3. Deploy
/tall-optimize
/tall-deploy
```

## 🔧 Personalizzazione

### Aggiungere Pattern del Tuo Progetto

**Senza Boost** - Edita [.claude/agents/tall-stack.md](.claude/agents/tall-stack.md):

```markdown
## My Project Patterns

### Authentication
We use Laravel Sanctum with custom guards...
```

**Con Boost** - Edita `.ai/guidelines/tall-stack.blade.php`:

```blade
## My Project Patterns

### Current Setup
- Laravel: {{ app()->version() }}
- Livewire: @if(class_exists('Livewire\Livewire')) 3.x @endif

### Our Conventions
- Components: PascalCase
- Methods: camelCase
```

### Creare Comandi Custom

Crea `.claude/commands/my-custom-command.md`:

```markdown
---
description: La mia operazione custom
---

Istruzioni dettagliate per Claude su cosa fare...
```

## 📦 Stack Tecnologico

Questo sistema è ottimizzato per:

- **Laravel** 10+
- **Livewire** 3+
- **Tailwind CSS** 3+
- **Alpine.js** 3+
- **PHP** 8.1+

### Laravel Boost (Opzionale ma Raccomandato)

**Cosa Aggiunge:**
- **MCP Server**: 15+ tools per context awareness
- **AI Guidelines**: Blade templates per pattern custom
- **Documentation API**: Semantic search in 17K+ docs
- **Version Aware**: Codice specifico per le tue versioni

**Quando Usarlo:**
- ✅ Progetti di media/grande dimensione
- ✅ Team che condivide convenzioni
- ✅ Need for version-specific code generation
- ✅ Complex database schemas
- ❌ Progetti tiny/prototype (overkill)

**Benefici:**
1. **Faster Development**: Context eliminates guesswork
2. **Better Code Quality**: Version-correct, schema-aware
3. **Team Consistency**: Shared AI guidelines
4. **Learning Curve**: AI understands your codebase

## 📄 Licenza

MIT License - Sentiti libero di usare e modificare per i tuoi progetti!

## 🌟 Crediti

Creato per semplificare lo sviluppo TALL Stack con l'aiuto di Claude AI.

### Link Utili

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Boost](https://github.com/laravel/boost) - MCP server for AI development
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev)
- [Claude Code Documentation](https://docs.claude.com/claude-code)
- [Model Context Protocol](https://modelcontextprotocol.io)

## 💬 Supporto

Hai domande? Chiedi direttamente a Claude Code usando gli agenti!

```
Come posso implementare un sistema di notifiche real-time nel TALL Stack?
```

**Con Boost MCP**: Claude può analizzare il tuo progetto e dare risposte specifiche!

---

**Happy Coding! 🚀**

Ultimo aggiornamento: 2025-01-05 | Versione: 2.0.0 (Laravel Boost Integration)
