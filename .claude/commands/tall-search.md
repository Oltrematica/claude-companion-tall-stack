---
description: Add full-text search functionality to TALL Stack models
---

# TALL Stack Search Implementation

You are an expert in implementing search functionality for TALL Stack (Tailwind, Alpine.js, Laravel, Livewire) applications. Your goal is to add powerful, performant search capabilities using Laravel Scout, database full-text search, or custom solutions.

## Your Task

Guide the user through implementing search functionality:

1. **Gather Requirements**
   Ask the user:
   - Which model(s) need search?
   - What fields should be searchable?
   - Expected search volume? (helps choose solution)
   - Real-time search needed? (as-you-type)
   - Search features needed? (filters, facets, typo tolerance)
   - Budget for search service? (Algolia, Meilisearch, etc.)
   - Performance requirements?

2. **Choose Search Solution**

   Based on requirements, recommend:

   ### A. Laravel Scout + Meilisearch (Recommended)
   **Best for:** Most applications, real-time search, typo tolerance
   **Pros:** Fast, typo-tolerant, faceting, free/open-source
   **Cons:** Requires separate service

   ### B. Laravel Scout + Algolia
   **Best for:** Large scale, advanced features, managed service
   **Pros:** Extremely fast, powerful features, managed
   **Cons:** Paid service, costs scale with usage

   ### C. Laravel Scout + Typesense
   **Best for:** Open-source alternative to Algolia
   **Pros:** Fast, typo-tolerant, self-hosted
   **Cons:** Requires server setup

   ### D. Database Full-Text Search
   **Best for:** Simple search, small datasets, no external dependencies
   **Pros:** No extra services, simple
   **Cons:** Limited features, slower for large datasets

   ### E. TNTSearch (Pure PHP)
   **Best for:** Small apps, no external dependencies
   **Pros:** Pure PHP, no services needed
   **Cons:** Slower, limited features

3. **Implementation: Laravel Scout + Meilisearch**

   ### Installation
   ```bash
   # Install Scout
   composer require laravel/scout

   # Publish configuration
   php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"

   # Install Meilisearch driver
   composer require meilisearch/meilisearch-php http-interop/http-factory-guzzle

   # Install Meilisearch (via Docker)
   docker run -d -p 7700:7700 getmeili/meilisearch:latest
   ```

   ### Configuration
   ```php
   // .env
   SCOUT_DRIVER=meilisearch
   MEILISEARCH_HOST=http://localhost:7700
   MEILISEARCH_KEY=your-master-key
   ```

   ### Make Model Searchable
   ```php
   <?php

   namespace App\Models;

   use Illuminate\Database\Eloquent\Model;
   use Laravel\Scout\Searchable;

   class Product extends Model
   {
       use Searchable;

       /**
        * Get the indexable data array for the model.
        */
       public function toSearchableArray(): array
       {
           return [
               'id' => $this->id,
               'name' => $this->name,
               'description' => $this->description,
               'sku' => $this->sku,
               'category' => $this->category?->name,
               'brand' => $this->brand?->name,
               'price' => $this->price,
               'in_stock' => $this->stock > 0,
               'tags' => $this->tags->pluck('name')->toArray(),
           ];
       }

       /**
        * Configure searchable options.
        */
       public function searchableOptions(): array
       {
           return [
               'filterableAttributes' => ['category', 'brand', 'in_stock', 'price'],
               'sortableAttributes' => ['name', 'price', 'created_at'],
               'rankingRules' => [
                   'words',
                   'typo',
                   'proximity',
                   'attribute',
                   'sort',
                   'exactness',
                   'price:asc',
               ],
           ];
       }

       /**
        * Determine if the model should be searchable.
        */
       public function shouldBeSearchable(): bool
       {
           return $this->is_published && !$this->is_deleted;
       }
   }
   ```

   ### Import Existing Records
   ```bash
   # Import all records
   php artisan scout:import "App\Models\Product"

   # Delete index
   php artisan scout:flush "App\Models\Product"
   ```

4. **Create Livewire Search Component**

   ```bash
   php artisan make:livewire ProductSearch
   ```

   ### Component Class
   ```php
   <?php

   namespace App\Livewire;

   use App\Models\Product;
   use Livewire\Component;
   use Livewire\Attributes\Url;
   use Livewire\WithPagination;

   class ProductSearch extends Component
   {
       use WithPagination;

       #[Url(as: 'q')]
       public string $search = '';

       #[Url]
       public array $filters = [];

       #[Url]
       public string $sortBy = 'relevance';

       public int $perPage = 12;

       public function updatedSearch(): void
       {
           $this->resetPage();
       }

       public function updatedFilters(): void
       {
           $this->resetPage();
       }

       public function clearFilters(): void
       {
           $this->filters = [];
           $this->resetPage();
       }

       public function clearSearch(): void
       {
           $this->search = '';
           $this->resetPage();
       }

       public function render()
       {
           $results = $this->search !== ''
               ? $this->performSearch()
               : Product::query()->paginate($this->perPage);

           return view('livewire.product-search', [
               'products' => $results,
               'hasSearch' => $this->search !== '',
           ]);
       }

       protected function performSearch()
       {
           $query = Product::search($this->search);

           // Apply filters
           if (!empty($this->filters['category'])) {
               $query->where('category', $this->filters['category']);
           }

           if (!empty($this->filters['brand'])) {
               $query->where('brand', $this->filters['brand']);
           }

           if (!empty($this->filters['in_stock'])) {
               $query->where('in_stock', true);
           }

           if (!empty($this->filters['price_min'])) {
               $query->where('price', '>=', $this->filters['price_min']);
           }

           if (!empty($this->filters['price_max'])) {
               $query->where('price', '<=', $this->filters['price_max']);
           }

           // Apply sorting
           if ($this->sortBy !== 'relevance') {
               $query->orderBy($this->sortBy);
           }

           return $query->paginate($this->perPage);
       }
   }
   ```

   ### Component View
   ```blade
   <div class="space-y-6">
       {{-- Search Input --}}
       <div class="relative">
           <input
               type="search"
               wire:model.live.debounce.300ms="search"
               placeholder="Search products..."
               class="w-full px-4 py-3 pl-10 pr-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
           >
           <svg class="absolute left-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
           </svg>

           @if($search)
               <button
                   wire:click="clearSearch"
                   class="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600"
               >
                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                   </svg>
               </button>
           @endif

           {{-- Loading indicator --}}
           <div wire:loading class="absolute right-3 top-3.5">
               <svg class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24">
                   <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                   <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
               </svg>
           </div>
       </div>

       {{-- Filters --}}
       <div class="flex flex-wrap gap-4">
           <select wire:model.live="filters.category" class="px-4 py-2 border border-gray-300 rounded-lg">
               <option value="">All Categories</option>
               <option value="electronics">Electronics</option>
               <option value="clothing">Clothing</option>
           </select>

           <select wire:model.live="filters.brand" class="px-4 py-2 border border-gray-300 rounded-lg">
               <option value="">All Brands</option>
               <option value="brand-a">Brand A</option>
               <option value="brand-b">Brand B</option>
           </select>

           <label class="flex items-center gap-2">
               <input type="checkbox" wire:model.live="filters.in_stock" class="rounded">
               <span class="text-sm text-gray-700">In Stock Only</span>
           </label>

           @if(!empty(array_filter($filters)))
               <button wire:click="clearFilters" class="text-sm text-blue-600 hover:text-blue-800">
                   Clear filters
               </button>
           @endif
       </div>

       {{-- Sort --}}
       <div class="flex justify-between items-center">
           <div class="text-sm text-gray-600">
               @if($hasSearch)
                   Found {{ $products->total() }} results for "{{ $search }}"
               @else
                   Showing {{ $products->total() }} products
               @endif
           </div>

           <select wire:model.live="sortBy" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
               <option value="relevance">Most Relevant</option>
               <option value="name">Name</option>
               <option value="price">Price: Low to High</option>
               <option value="created_at">Newest First</option>
           </select>
       </div>

       {{-- Results --}}
       <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
           @forelse($products as $product)
               <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition">
                   <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-48 object-cover rounded">
                   <h3 class="mt-4 font-semibold text-gray-900">{{ $product->name }}</h3>
                   <p class="text-sm text-gray-600 mt-1">{{ $product->category }}</p>
                   <div class="mt-4 flex justify-between items-center">
                       <span class="text-lg font-bold text-gray-900">€{{ number_format($product->price, 2) }}</span>
                       <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                           Add to Cart
                       </button>
                   </div>
               </div>
           @empty
               <div class="col-span-full text-center py-12">
                   <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                   </svg>
                   <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                   <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters</p>
               </div>
           @endforelse
       </div>

       {{-- Pagination --}}
       <div class="mt-6">
           {{ $products->links() }}
       </div>
   </div>
   ```

5. **Advanced Features**

   ### Faceted Search (with counts)
   ```php
   public function getFacets()
   {
       $results = Product::search($this->search)->raw();

       return [
           'categories' => $results['facetDistribution']['category'] ?? [],
           'brands' => $results['facetDistribution']['brand'] ?? [],
       ];
   }
   ```

   ### Autocomplete/Instant Search
   ```php
   public function getSuggestions()
   {
       if (strlen($this->search) < 2) {
           return [];
       }

       return Product::search($this->search)
           ->take(5)
           ->get()
           ->map(fn($product) => [
               'id' => $product->id,
               'name' => $product->name,
               'url' => route('products.show', $product),
           ]);
   }
   ```

   ### Search Analytics
   ```php
   // Track searches
   SearchLog::create([
       'query' => $this->search,
       'results_count' => $products->count(),
       'user_id' => auth()->id(),
       'ip_address' => request()->ip(),
   ]);
   ```

6. **Database Full-Text Search Alternative**

   If Scout is not needed:

   ```php
   // Migration
   $table->fullText(['name', 'description']);

   // Model
   public function scopeSearch($query, $search)
   {
       return $query->whereFullText(
           ['name', 'description'],
           $search
       );
   }

   // Usage in Livewire
   Product::search($this->search)->paginate();
   ```

7. **Performance Optimization**

   - Use debouncing for live search (`.debounce.300ms`)
   - Implement lazy loading for results
   - Cache popular searches
   - Index only necessary fields
   - Use pagination
   - Add search result caching

8. **Testing**

   ```php
   public function test_can_search_products()
   {
       Product::factory()->create(['name' => 'Blue Widget']);
       Product::factory()->create(['name' => 'Red Widget']);

       $response = Livewire::test(ProductSearch::class)
           ->set('search', 'Blue')
           ->assertSee('Blue Widget')
           ->assertDontSee('Red Widget');
   }
   ```

## Checklist

- [ ] Scout installed and configured
- [ ] Models made searchable
- [ ] Existing data imported
- [ ] Livewire component created
- [ ] Filters implemented
- [ ] Sorting implemented
- [ ] Pagination added
- [ ] Loading states added
- [ ] Empty states designed
- [ ] Tests written
- [ ] Performance optimized

## Start

Ask the user:
1. Which model(s) need search functionality?
2. What's your expected search volume?
3. Do you need real-time (as-you-type) search?
4. Any budget constraints? (helps choose between Algolia/Meilisearch/database)
5. What fields should be searchable?

Then proceed with the implementation.
