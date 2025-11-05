# TALL Stack Development Guidelines

This project uses the **TALL Stack** architecture:
- **T**ailwind CSS for styling
- **A**lpine.js for JavaScript interactivity
- **L**aravel {{ app()->version() }} for backend
- **L**ivewire for reactive components

## Core Principles

### Component Architecture
- Use Livewire for server-rendered reactive components
- Alpine.js for client-side interactions only
- Tailwind utilities for all styling
- Blade templates for views

### Code Organization
```
app/Http/Livewire/          # Livewire components
resources/views/
├── components/             # Blade components
├── livewire/              # Livewire views
└── layouts/               # App layouts
```

## Livewire Patterns

### Component Structure
```php
namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'name';

    protected $queryString = ['search', 'sortBy'];

    public function render()
    {
        return view('livewire.product-list', [
            'products' => Product::query()
                ->when($this->search, fn($q) => $q->search($this->search))
                ->orderBy($this->sortBy)
                ->paginate(15),
        ]);
    }
}
```

### Best Practices
- Keep components focused (single responsibility)
- Use computed properties for derived data
- Defer model binding with `wire:model.defer` for better performance
- Emit events for component communication
- Use `wire:loading` states for better UX

### Property Types
Always type-hint public properties:
```php
public string $name = '';
public int $quantity = 0;
public ?Carbon $publishedAt = null;
public array $selectedIds = [];
```

### Validation
```php
protected $rules = [
    'name' => 'required|string|max:255',
    'email' => 'required|email',
];

public function save()
{
    $this->validate();
    // Save logic
}

// Real-time validation
public function updated($propertyName)
{
    $this->validateOnly($propertyName);
}
```

## Tailwind CSS Patterns

### Utility-First Approach
Prefer Tailwind utilities over custom CSS:
```blade
{{-- Good --}}
<div class="flex items-center justify-between p-4 bg-white rounded-lg shadow">

{{-- Avoid --}}
<div class="custom-card">
```

### Responsive Design
Always mobile-first:
```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
```

### Component Classes
For repeated patterns, use Blade components:
```blade
{{-- resources/views/components/button.blade.php --}}
@props(['variant' => 'primary'])

<button {{ $attributes->merge(['class' => 'px-4 py-2 rounded font-semibold ' . match($variant) {
    'primary' => 'bg-blue-600 text-white hover:bg-blue-700',
    'secondary' => 'bg-gray-200 text-gray-800 hover:bg-gray-300',
    'danger' => 'bg-red-600 text-white hover:bg-red-700',
}]) }}>
    {{ $slot }}
</button>

{{-- Usage --}}
<x-button variant="primary">Save</x-button>
```

### Dark Mode
Use Tailwind's class-based dark mode:
```blade
<div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
```

## Alpine.js Integration

### When to Use Alpine
- Dropdowns and modals
- Tooltips and popovers
- Client-side filtering/sorting
- UI animations
- Form interactions (character counters, etc.)

### When NOT to Use Alpine
- Avoid duplicating Livewire state
- Don't use for data that needs server persistence
- Don't replace Livewire for reactive data

### Common Patterns

**Modal**
```blade
<div x-data="{ open: false }">
    <button @click="open = true">Open Modal</button>

    <div x-show="open"
         x-cloak
         @click.away="open = false"
         class="fixed inset-0 bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-lg">
            <h2>Modal Content</h2>
            <button @click="open = false">Close</button>
        </div>
    </div>
</div>
```

**Dropdown**
```blade
<div x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open">
        Toggle Dropdown
    </button>

    <div x-show="open" x-cloak x-transition>
        <a href="#">Link 1</a>
        <a href="#">Link 2</a>
    </div>
</div>
```

**Form Enhancement**
```blade
<div x-data="{ count: 0 }">
    <textarea
        x-model="count"
        maxlength="280"
        wire:model.defer="content"
    ></textarea>
    <span x-text="`${count.length}/280`"></span>
</div>
```

### x-cloak Prevention
Always include in your CSS:
```css
[x-cloak] { display: none !important; }
```

## Livewire + Alpine Integration

### Accessing Livewire from Alpine
```blade
<div
    x-data="{
        items: @entangle('selectedItems')
    }"
    wire:init="loadItems"
>
    <div x-show="items.length > 0">
        You have <span x-text="items.length"></span> items
    </div>
</div>
```

### Dispatching Livewire Events from Alpine
```blade
<button @click="$dispatch('item-selected', { id: 123 })">
    Select Item
</button>

{{-- In Livewire component --}}
protected $listeners = ['item-selected' => 'handleSelection'];
```

## Forms

### Livewire Form Pattern
```php
class ProductForm extends Component
{
    public ?Product $product = null;
    public string $name = '';
    public string $description = '';
    public float $price = 0;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
    ];

    public function mount(?Product $product = null)
    {
        if ($product) {
            $this->product = $product;
            $this->name = $product->name;
            $this->description = $product->description;
            $this->price = $product->price;
        }
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->product) {
            $this->product->update($validated);
        } else {
            $this->product = Product::create($validated);
        }

        session()->flash('message', 'Product saved successfully!');

        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.product-form');
    }
}
```

### Form View
```blade
<form wire:submit.prevent="save">
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">
                Name
            </label>
            <input
                type="text"
                wire:model.defer="name"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
            @error('name')
                <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">
                Price
            </label>
            <input
                type="number"
                step="0.01"
                wire:model.defer="price"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
            @error('price')
                <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('products.index') }}"
               class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                Cancel
            </a>
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
            >
                <span wire:loading.remove>Save</span>
                <span wire:loading>Saving...</span>
            </button>
        </div>
    </div>
</form>
```

## Tables & Lists

### Livewire Table with Search & Pagination
```php
class ProductTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatedSearch()
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
        return view('livewire.product-table', [
            'products' => Product::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(15),
        ]);
    }
}
```

## File Uploads

### Livewire 3 File Upload Pattern
```php
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use WithFileUploads;

    public $photo;
    public ?string $existingPhoto = null;

    protected $rules = [
        'photo' => 'nullable|image|max:2048',
    ];

    public function save()
    {
        $this->validate();

        $path = $this->existingPhoto;

        if ($this->photo) {
            $path = $this->photo->store('products', 'public');
        }

        Product::create([
            'photo' => $path,
        ]);
    }

    public function render()
    {
        return view('livewire.product-form');
    }
}
```

```blade
<div>
    @if ($photo)
        <img src="{{ $photo->temporaryUrl() }}" class="h-32 w-32 object-cover">
    @elseif ($existingPhoto)
        <img src="{{ Storage::url($existingPhoto) }}" class="h-32 w-32 object-cover">
    @endif

    <input type="file" wire:model="photo">

    @error('photo')
        <span class="text-red-600">{{ $message }}</span>
    @enderror

    <div wire:loading wire:target="photo">
        Uploading...
    </div>
</div>
```

## Performance Optimization

### Livewire Performance
- Use `wire:model.defer` for non-critical bindings
- Implement computed properties with `#[Computed]` attribute
- Lazy load heavy components with `wire:init`
- Use polling wisely: `wire:poll.5s` only when needed
- Implement `wire:loading` states

### Database Optimization
- Always eager load relationships to prevent N+1
- Use pagination for large datasets
- Index frequently queried columns
- Use database transactions for multiple operations

### Frontend Optimization
- Minimize Tailwind purging in production
- Use Alpine.js `x-cloak` to prevent flash
- Lazy load images with native `loading="lazy"`
- Minimize JavaScript dependencies

## Testing

### Livewire Component Tests
```php
use Livewire\Livewire;

test('can create product', function () {
    Livewire::test(ProductForm::class)
        ->set('name', 'Test Product')
        ->set('price', 99.99)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('products.index'));

    expect(Product::where('name', 'Test Product')->exists())->toBeTrue();
});

test('validates required fields', function () {
    Livewire::test(ProductForm::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});
```

## Common Gotchas

### Livewire
- Property names can't start with `$`
- Methods can't be named `render`, `mount`, `hydrate`, etc.
- Public properties are exposed to JavaScript (use protected for sensitive data)
- Collections/Models aren't reactive by default (use `refresh()`)

### Alpine.js
- Always use `x-cloak` to prevent flash of unstyled content
- Use `@click.away` for closing dropdowns/modals
- Remember `x-data` creates new scope
- Use `$dispatch` for custom events

### Tailwind
- Purge CSS carefully in production
- Don't use string concatenation for classes (breaks purging)
- Dark mode requires configuration in `tailwind.config.js`

## Resources

- [Livewire Documentation](https://livewire.laravel.com)
- [Alpine.js Documentation](https://alpinejs.dev)
- [Tailwind CSS Documentation](https://tailwindcss.com)
- [Laravel Documentation](https://laravel.com/docs)
