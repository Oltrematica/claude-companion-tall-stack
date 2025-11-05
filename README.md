# TALL Stack AI Assistant

Un sistema completo di agenti AI per Claude Code, ottimizzato per lo sviluppo di applicazioni **TALL Stack** (Tailwind CSS, Alpine.js, Laravel, Livewire).

## 🎯 Caratteristiche

- **Agenti Specializzati**: Esperti dedicati per Laravel, Livewire, Tailwind e Alpine.js
- **Comandi Rapidi**: Slash commands per operazioni comuni
- **Best Practices**: Seguono le convenzioni ufficiali e le best practices della community
- **Completo**: Copre tutto il ciclo di sviluppo, dal setup al deployment
- **Modulare**: Facilmente estendibile e personalizzabile

## 📋 Prerequisiti

- [Claude Code](https://claude.ai/claude-code) installato
- Progetto Laravel 10+ con Livewire 3+
- Node.js e NPM per la gestione degli asset

## 🚀 Quick Start

### 1. Installazione

Clona o copia la cartella `.claude` nella root del tuo progetto Laravel:

```bash
# Se questo è un repository separato
cp -r /path/to/tall-stack-ai-assistant/.claude /path/to/your-laravel-project/

# Oppure inizializza direttamente nel tuo progetto
cd /path/to/your-laravel-project
mkdir -p .claude/{agents,commands,prompts}
```

### 2. Struttura del Progetto

```
your-laravel-project/
├── .claude/
│   ├── agents/
│   │   ├── tall-stack.md
│   │   ├── tall-stack-laravel.md
│   │   ├── tall-stack-livewire.md
│   │   └── tall-stack-frontend.md
│   └── commands/
│       ├── tall-new-component.md
│       ├── tall-crud.md
│       ├── tall-optimize.md
│       ├── tall-test.md
│       └── tall-deploy.md
├── app/
├── resources/
└── ...
```

### 3. Primo Utilizzo

Apri Claude Code nel tuo progetto e prova:

```
/tall-crud
```

Questo comando ti guiderà nella creazione di un'interfaccia CRUD completa!

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

### Slash Commands

#### `/tall-new-component`

Crea un nuovo componente Livewire con styling Tailwind.

**Esempio:**
```
/tall-new-component
```

**Ti chiederà:**
- Nome del componente
- Tipo (form, list, modal, card, custom)
- Features specifiche

**Genera:**
- Classe PHP del componente
- Vista Blade con Tailwind
- Validation rules
- Event handling
- Loading states

---

#### `/tall-crud`

Genera un'interfaccia CRUD completa per un model.

**Esempio:**
```
/tall-crud
```

**Ti chiederà:**
- Nome del model
- Campi e tipi
- Relationships
- Se includere soft deletes

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

**Esempio:**
```
/tall-optimize
```

**Analizza:**
- Database queries (N+1 problems)
- Livewire component performance
- Frontend optimization
- Caching opportunities
- Code quality

**Output:**
Report dettagliato con priorità e soluzioni concrete.

---

#### `/tall-test`

Genera test completi per componenti e features.

**Esempio:**
```
/tall-test
```

**Crea:**
- Feature tests per Livewire
- Unit tests per models
- Test per validation
- Test per authorization
- Browser tests (optional)

---

#### `/tall-deploy`

Guida completa al deployment in produzione.

**Esempio:**
```
/tall-deploy
```

**Include:**
- Pre-deployment checklist
- Server configuration
- Queue workers setup
- SSL certificates
- Monitoring
- Rollback plan

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

### 1. Setup Iniziale

```bash
# Nuovo progetto Laravel
composer create-project laravel/laravel my-app
cd my-app

# Installa Livewire
composer require livewire/livewire

# Installa Tailwind
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p

# Copia la configurazione degli agenti
cp -r /path/to/.claude .
```

### 2. Sviluppo Feature

**Fase 1: Planning**
```
Voglio creare un sistema di gestione prodotti con categorie.
Qual è l'approccio migliore?
```

**Fase 2: Database**
```
/tall-crud
# Crea Model Product con categorie
```

**Fase 3: UI/UX**
```
/tall-new-component
# Crea componenti custom per UI specifica
```

**Fase 4: Testing**
```
/tall-test
# Genera test per i componenti
```

### 3. Optimization

```
/tall-optimize
# Ottimizza prima del deploy
```

### 4. Deployment

```
/tall-deploy
# Segui la checklist di deployment
```

## 🔧 Personalizzazione

### Aggiungere Pattern del Tuo Progetto

Edita [.claude/agents/tall-stack.md](.claude/agents/tall-stack.md):

```markdown
## My Project Patterns

### Authentication
We use Laravel Sanctum with custom guards...

### File Structure
Our Livewire components follow this structure...

### Naming Conventions
- Components: PascalCase
- Methods: camelCase
- ...
```

### Creare Comandi Custom

Crea `.claude/commands/my-custom-command.md`:

```markdown
---
description: La mia operazione custom
---

Istruzioni dettagliate per Claude su cosa fare...
```

Usa con:
```
/my-custom-command
```

### Aggiungere Tool Specifici

Edita gli agenti per includere tool del tuo stack:

```markdown
## Our Tools

### Spatie Packages
We use:
- spatie/laravel-permission for roles
- spatie/laravel-media-library for media
- ...

When implementing features, use these packages.
```

## 📖 Best Practices

### Convenzioni di Codice

Gli agenti seguono:
- **PSR-12** per PHP
- **Airbnb Style Guide** per JavaScript
- **Laravel Conventions** per structure
- **Livewire Best Practices** per components

### Sicurezza

Tutti i comandi includono:
- ✅ CSRF protection
- ✅ Input validation
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ Authorization checks

### Performance

Ottimizzazioni automatiche:
- ✅ Query eager loading
- ✅ Computed properties caching
- ✅ Lazy loading componenti
- ✅ Asset optimization
- ✅ Database indexing

## 🤝 Contribuire

### Feedback

Hai suggerimenti? Apri una issue o contribuisci direttamente:

1. Fork il repository
2. Crea un branch per la tua feature
3. Commit le modifiche
4. Push e apri una PR

### Condividere Miglioramenti

Se hai creato agenti o comandi utili, condividili con la community!

## 🐛 Troubleshooting

### Gli agenti non rispondono correttamente

1. Verifica che i file `.md` siano nella cartella corretta
2. Controlla la sintassi markdown
3. Assicurati che Claude Code sia aggiornato

### I comandi non vengono trovati

1. Verifica il formato del frontmatter:
   ```markdown
   ---
   description: Descrizione del comando
   ---
   ```
2. Riavvia Claude Code

### Comportamento inaspettato

1. Controlla i log di Claude Code
2. Verifica la compatibilità delle versioni
3. Prova a rigenerare la configurazione

## 📦 Stack Tecnologico

Questo sistema è ottimizzato per:

- **Laravel** 10+
- **Livewire** 3+
- **Tailwind CSS** 3+
- **Alpine.js** 3+
- **PHP** 8.1+

## 📄 Licenza

MIT License - Sentiti libero di usare e modificare per i tuoi progetti!

## 🌟 Crediti

Creato per semplificare lo sviluppo TALL Stack con l'aiuto di Claude AI.

### Link Utili

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev)
- [Claude Code Documentation](https://docs.claude.com/claude-code)

## 💬 Supporto

Hai domande? Chiedi direttamente a Claude Code usando gli agenti!

```
Come posso implementare un sistema di notifiche real-time nel TALL Stack?
```

---

**Happy Coding! 🚀**

Ultimo aggiornamento: 2025-01-05 | Versione: 1.0.0
