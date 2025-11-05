# Prompts Directory

Questa cartella contiene **prompt riutilizzabili** per operazioni comuni nel TALL Stack.

## 📝 Cosa Sono i Prompts?

I prompts sono snippet di testo riutilizzabili che puoi richiamare durante le conversazioni con Claude Code per:
- Definire contesto specifico del progetto
- Stabilire convenzioni di codice
- Fornire esempi di pattern ricorrenti
- Documentare decisioni architetturali

## 🎯 Differenza tra Prompts e Comandi

| Prompts | Slash Commands |
|---------|----------------|
| Frammenti di contesto riutilizzabili | Azioni complete automatizzate |
| Si combinano con le tue richieste | Si eseguono autonomamente |
| Forniscono linee guida | Generano codice |
| Flessibili e componibili | Strutturati e completi |

## 📂 Struttura Consigliata

```
.claude/prompts/
├── README.md                    # Questa guida
├── conventions/
│   ├── naming.md               # Convenzioni nomenclatura
│   ├── code-style.md           # Stile di codice
│   └── git-workflow.md         # Workflow Git
├── patterns/
│   ├── service-pattern.md      # Pattern Service Layer
│   ├── repository-pattern.md   # Pattern Repository
│   ├── action-pattern.md       # Pattern Action
│   └── dto-pattern.md          # Pattern Data Transfer Object
├── architecture/
│   ├── project-structure.md    # Struttura progetto
│   ├── module-design.md        # Design dei moduli
│   └── api-design.md           # Design API
└── examples/
    ├── livewire-form.md        # Esempio form Livewire
    ├── livewire-table.md       # Esempio tabella con filtri
    └── alpine-components.md    # Componenti Alpine comuni
```

## 💡 Esempi d'Uso

### Scenario 1: Definire Convenzioni del Progetto

Crea `conventions/naming.md`:
```markdown
# Naming Conventions

## Livewire Components
- Nomi: `PascalCase` (es. `UserProfile`, `PostList`)
- File: `kebab-case` (es. `user-profile.blade.php`)

## Database
- Tabelle: `snake_case` plurale (es. `user_posts`)
- Colonne: `snake_case` (es. `created_at`)

## Metodi
- Actions: verbo + sostantivo (es. `createPost`, `deleteUser`)
- Queries: sostantivo descrittivo (es. `activeUsers`, `publishedPosts`)
```

### Scenario 2: Documentare Pattern Architetturali

Crea `patterns/service-pattern.md`:
```markdown
# Service Layer Pattern

Nel nostro progetto usiamo Services per logica business complessa.

## Struttura
\`\`\`
app/Services/
├── UserService.php
├── PostService.php
└── NotificationService.php
\`\`\`

## Template
\`\`\`php
namespace App\Services;

class UserService
{
    public function __construct(
        private UserRepository $repository,
        private EventDispatcher $events
    ) {}

    public function createUser(array $data): User
    {
        // Business logic here
    }
}
\`\`\`

## Quando Usare
- Logica business complessa
- Operazioni multi-step
- Coordinamento tra più models
```

### Scenario 3: Esempi di Codice Ricorrenti

Crea `examples/livewire-table.md`:
```markdown
# Livewire Table with Filters Pattern

Pattern standard per tabelle con filtri, sorting e paginazione.

\`\`\`php
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class PostTable extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $status = '';

    #[Url]
    public $sortField = 'created_at';

    #[Url]
    public $sortDirection = 'desc';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        return view('livewire.post-table', [
            'posts' => Post::query()
                ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->when($this->status, fn($q) => $q->where('status', $this->status))
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(15)
        ]);
    }
}
\`\`\`
```

## 🚀 Come Usare i Prompts

### Metodo 1: Riferimento Diretto

```
@.claude/prompts/patterns/service-pattern.md

Crea un service per gestire le notifiche utente seguendo il nostro pattern.
```

### Metodo 2: Copia-Incolla del Contesto

Copia il contenuto del prompt nella conversazione prima di fare la richiesta.

### Metodo 3: Combinazione

```
@.claude/prompts/conventions/naming.md
@.claude/prompts/patterns/service-pattern.md

Crea un NotificationService seguendo le nostre convenzioni.
```

## 📋 Template per Nuovi Prompts

### Template Base

```markdown
# [Titolo del Prompt]

## Contesto
Breve descrizione di quando e perché usare questo pattern/convenzione.

## Esempio
\`\`\`php
// Codice di esempio
\`\`\`

## Best Practices
- Punto 1
- Punto 2

## Anti-Patterns
Cosa evitare e perché.

## Link Utili
- [Documentazione](url)
```

## 🎨 Prompts Consigliati da Creare

### 1. Conventions (Convenzioni)

- **naming.md** - Nomenclatura uniforme
- **code-style.md** - Stile di codice
- **comment-style.md** - Come commentare
- **git-workflow.md** - Branching e commits
- **pr-template.md** - Template pull request

### 2. Patterns (Pattern Architetturali)

- **service-pattern.md** - Service layer
- **repository-pattern.md** - Repository pattern
- **action-pattern.md** - Single action classes
- **dto-pattern.md** - Data transfer objects
- **factory-pattern.md** - Factory pattern
- **observer-pattern.md** - Model observers
- **policy-pattern.md** - Authorization policies

### 3. Architecture (Architettura)

- **project-structure.md** - Organizzazione cartelle
- **module-design.md** - Design moduli
- **api-design.md** - Design API RESTful
- **event-driven.md** - Event-driven architecture
- **caching-strategy.md** - Strategia di caching

### 4. Examples (Esempi)

- **livewire-form.md** - Form completi
- **livewire-table.md** - Tabelle con filtri
- **livewire-modal.md** - Modal patterns
- **alpine-components.md** - Componenti Alpine comuni
- **tailwind-components.md** - Componenti Tailwind riutilizzabili

### 5. Testing (Testing)

- **test-structure.md** - Organizzazione test
- **test-naming.md** - Nomenclatura test
- **test-data.md** - Gestione test data
- **mock-patterns.md** - Pattern per mocking

### 6. Security (Sicurezza)

- **validation-rules.md** - Regole validazione comuni
- **authorization.md** - Pattern autorizzazione
- **secure-queries.md** - Query sicure
- **input-sanitization.md** - Sanitizzazione input

### 7. Performance (Performance)

- **query-optimization.md** - Ottimizzazione query
- **caching-examples.md** - Esempi di caching
- **eager-loading.md** - Eager loading patterns
- **lazy-loading.md** - Lazy loading strategies

## 🔄 Manutenzione dei Prompts

### Quando Aggiornare

- ✅ Dopo decisioni architetturali importanti
- ✅ Quando si identifica un pattern ricorrente
- ✅ Dopo code review con feedback comune
- ✅ Quando si adottano nuove librerie/tool

### Versioning

Aggiungi la data di ultimo aggiornamento in ogni prompt:

```markdown
---
Ultimo aggiornamento: 2025-01-05
Versione: 1.0.0
---
```

### Review

- Rivedi i prompts trimestralmente
- Rimuovi pattern obsoleti
- Aggiorna con nuove best practices

## 💡 Pro Tips

1. **Mantienili Concisi**: Prompts troppo lunghi sono difficili da usare
2. **Usa Esempi Reali**: Codice funzionante dal tuo progetto
3. **Documenta il Perché**: Non solo il "come" ma anche il "perché"
4. **Versionali**: Come il codice, i prompts evolvono
5. **Condividi con il Team**: Assicurati che tutti usino gli stessi pattern

## 📚 Risorse

- [Claude Code Prompts Guide](https://docs.claude.com)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [Livewire Best Practices](https://livewire.laravel.com/docs/best-practices)

---

**Suggerimento**: Inizia con 3-5 prompts essenziali e espandi man mano che identifichi pattern ricorrenti nel tuo progetto!
