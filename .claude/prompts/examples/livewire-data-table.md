# Livewire Data Table with Filters, Sorting, and Pagination

Complete example of a production-ready data table component with all common features.

## Component Class

```php
<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;

class ProductTable extends Component
{
    use WithPagination;

    // Search
    #[Url(as: 'q')]
    public string $search = '';

    // Filters
    #[Url]
    public array $filters = [];

    // Sorting
    #[Url]
    public string $sortField = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    // Pagination
    public int $perPage = 15;

    // Bulk actions
    public array $selected = [];
    public bool $selectAll = false;

    // Actions
    public bool $showFilters = false;

    /**
     * Reset pagination when search/filters change
     */
    public function updated($property): void
    {
        if (in_array($property, ['search', 'filters', 'perPage'])) {
            $this->resetPage();
        }
    }

    /**
     * Sort by field
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Toggle select all
     */
    public function updatedSelectAll($value): void
    {
        $this->selected = $value
            ? $this->products->pluck('id')->toArray()
            : [];
    }

    /**
     * Clear all filters
     */
    public function clearFilters(): void
    {
        $this->filters = [];
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Bulk delete selected
     */
    public function bulkDelete(): void
    {
        $this->authorize('bulkDelete', Product::class);

        Product::whereIn('id', $this->selected)->delete();

        $this->selected = [];
        $this->selectAll = false;

        session()->flash('message', count($this->selected) . ' products deleted.');
    }

    /**
     * Export to CSV
     */
    public function export(): void
    {
        return Excel::download(
            new ProductsExport($this->getQuery()),
            'products-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Get filtered/sorted query
     */
    protected function getQuery()
    {
        return Product::query()
            ->with(['category', 'brand'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['category'] ?? null, function ($query, $category) {
                $query->where('category_id', $category);
            })
            ->when($this->filters['brand'] ?? null, function ($query, $brand) {
                $query->where('brand_id', $brand);
            })
            ->when($this->filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($this->filters['price_min'] ?? null, function ($query, $min) {
                $query->where('price', '>=', $min);
            })
            ->when($this->filters['price_max'] ?? null, function ($query, $max) {
                $query->where('price', '<=', $max);
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    /**
     * Get products (computed property for caching)
     */
    #[Computed]
    public function products()
    {
        return $this->getQuery()->paginate($this->perPage);
    }

    /**
     * Get filter counts
     */
    #[Computed]
    public function categories()
    {
        return Category::withCount('products')->get();
    }

    #[Computed]
    public function brands()
    {
        return Brand::withCount('products')->get();
    }

    public function render()
    {
        return view('livewire.product-table');
    }
}
```

## Component View

```blade
<div>
    {{-- Header with Search and Actions --}}
    <div class="mb-6 flex items-center justify-between gap-4">
        {{-- Search --}}
        <div class="flex-1 max-w-md">
            <div class="relative">
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search products..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
                <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>

                @if($search)
                    <button
                        wire:click="$set('search', '')"
                        class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif

                <div wire:loading class="absolute right-3 top-2.5">
                    <svg class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            {{-- Filters Toggle --}}
            <button
                wire:click="$toggle('showFilters')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filters
                @if(count(array_filter($filters)))
                    <span class="ml-2 px-2 py-0.5 bg-blue-600 text-white text-xs rounded-full">
                        {{ count(array_filter($filters)) }}
                    </span>
                @endif
            </button>

            {{-- Export --}}
            <button
                wire:click="export"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export
            </button>

            {{-- Create New --}}
            <a
                href="{{ route('products.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Product
            </a>
        </div>
    </div>

    {{-- Filters Panel --}}
    @if($showFilters)
        <div class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Category Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select wire:model.live="filters.category" class="w-full border-gray-300 rounded-lg">
                        <option value="">All Categories</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }} ({{ $category->products_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Brand Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                    <select wire:model.live="filters.brand" class="w-full border-gray-300 rounded-lg">
                        <option value="">All Brands</option>
                        @foreach($this->brands as $brand)
                            <option value="{{ $brand->id }}">
                                {{ $brand->name }} ({{ $brand->products_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select wire:model.live="filters.status" class="w-full border-gray-300 rounded-lg">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>

                {{-- Price Range --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price Range</label>
                    <div class="flex gap-2">
                        <input
                            type="number"
                            wire:model.live.debounce="filters.price_min"
                            placeholder="Min"
                            class="w-1/2 border-gray-300 rounded-lg"
                        >
                        <input
                            type="number"
                            wire:model.live.debounce="filters.price_max"
                            placeholder="Max"
                            class="w-1/2 border-gray-300 rounded-lg"
                        >
                    </div>
                </div>
            </div>

            {{-- Clear Filters --}}
            @if(count(array_filter($filters)) || $search)
                <div class="mt-4">
                    <button
                        wire:click="clearFilters"
                        class="text-sm text-blue-600 hover:text-blue-800"
                    >
                        Clear all filters
                    </button>
                </div>
            @endif
        </div>
    @endif

    {{-- Bulk Actions --}}
    @if(count($selected))
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-blue-900">
                    {{ count($selected) }} items selected
                </span>
                <div class="flex gap-2">
                    <button
                        wire:click="bulkDelete"
                        wire:confirm="Are you sure you want to delete {{ count($selected) }} items?"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                    >
                        Delete Selected
                    </button>
                    <button
                        wire:click="$set('selected', [])"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    {{-- Select All --}}
                    <th class="px-6 py-3 w-12">
                        <input
                            type="checkbox"
                            wire:model.live="selectAll"
                            class="rounded border-gray-300"
                        >
                    </th>

                    {{-- Sortable Headers --}}
                    <th wire:click="sortBy('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                        <div class="flex items-center gap-1">
                            Name
                            @if($sortField === 'name')
                                <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'transform rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                </svg>
                            @endif
                        </div>
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>

                    <th wire:click="sortBy('price')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                        <div class="flex items-center gap-1">
                            Price
                            @if($sortField === 'price')
                                <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'transform rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                </svg>
                            @endif
                        </div>
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($this->products as $product)
                    <tr wire:key="product-{{ $product->id }}" class="hover:bg-gray-50">
                        {{-- Checkbox --}}
                        <td class="px-6 py-4">
                            <input
                                type="checkbox"
                                wire:model.live="selected"
                                value="{{ $product->id }}"
                                class="rounded border-gray-300"
                            >
                        </td>

                        {{-- Name --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-10 h-10 rounded object-cover mr-3">
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                    <div class="text-sm text-gray-500">SKU: {{ $product->sku }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Category --}}
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $product->category?->name }}
                        </td>

                        {{-- Price --}}
                        <td class="px-6 py-4 text-sm text-gray-900">
                            ${{ number_format($product->price, 2) }}
                        </td>

                        {{-- Stock --}}
                        <td class="px-6 py-4">
                            <span class="text-sm {{ $product->stock > 10 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $product->stock }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($product->status) }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <a href="{{ route('products.edit', $product) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                            <button
                                wire:click="delete({{ $product->id }})"
                                wire:confirm="Delete this product?"
                                class="text-red-600 hover:text-red-900"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                            <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $this->products->links() }}
    </div>
</div>
```

## Features Included

- ✅ Real-time search with debounce
- ✅ Multiple filters (category, brand, status, price range)
- ✅ Sortable columns
- ✅ Pagination
- ✅ Bulk selection and actions
- ✅ Export functionality
- ✅ Loading states
- ✅ Empty states
- ✅ URL query parameters (shareable filters)
- ✅ Responsive design
- ✅ Computed properties for performance

## Usage

```blade
<livewire:product-table />
```
