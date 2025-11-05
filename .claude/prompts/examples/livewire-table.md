# Livewire Table with Filters - Complete Example

---
Ultimo aggiornamento: 2025-01-05
Versione: 1.0.0
---

## Pattern Completo

Questo è il pattern standard per tabelle con:
- ✅ Paginazione
- ✅ Ricerca in tempo reale
- ✅ Filtri multipli
- ✅ Sorting bidirezionale
- ✅ URL state (bookmark-able)
- ✅ Loading states
- ✅ Empty states
- ✅ Bulk actions (optional)

## Component Class

```php
<?php

namespace App\Livewire;

use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class PostTable extends Component
{
    use WithPagination;

    // Search & Filters (persisted in URL)
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $sortField = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    // UI State (not persisted)
    public array $selectedIds = [];
    public bool $selectAll = false;

    // Pagination
    public int $perPage = 15;

    /**
     * Reset pagination when search/filters change
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
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
     * Clear all filters
     */
    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'category']);
        $this->resetPage();
    }

    /**
     * Toggle select all
     */
    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedIds = $this->posts->pluck('id')->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    /**
     * Delete post
     */
    public function delete(int $postId): void
    {
        $post = Post::findOrFail($postId);

        $this->authorize('delete', $post);

        $post->delete();

        $this->dispatch('post-deleted');

        session()->flash('message', 'Post deleted successfully.');
    }

    /**
     * Bulk delete
     */
    public function bulkDelete(): void
    {
        if (empty($this->selectedIds)) {
            return;
        }

        Post::whereIn('id', $this->selectedIds)
            ->each(function ($post) {
                $this->authorize('delete', $post);
                $post->delete();
            });

        $this->selectedIds = [];
        $this->selectAll = false;

        session()->flash('message', 'Posts deleted successfully.');
    }

    /**
     * Listen to events
     */
    #[On('post-created')]
    #[On('post-updated')]
    public function refresh(): void
    {
        // Refresh component
    }

    /**
     * Get posts (computed for caching)
     */
    #[Computed]
    public function posts()
    {
        return Post::query()
            ->with(['author', 'category'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                      ->orWhere('content', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, fn($query) => $query->where('status', $this->status))
            ->when($this->category, fn($query) => $query->where('category_id', $this->category))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    /**
     * Get available categories for filter
     */
    #[Computed]
    public function categories()
    {
        return \App\Models\Category::pluck('name', 'id');
    }

    public function render()
    {
        return view('livewire.post-table');
    }
}
```

## Blade View

```blade
<div>
    {{-- Header with Actions --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Posts</h2>
            <p class="mt-1 text-sm text-gray-600">
                Manage your blog posts
            </p>
        </div>

        <a
            href="{{ route('posts.create') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Post
        </a>
    </div>

    {{-- Filters Bar --}}
    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Search --}}
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                    Search
                </label>
                <input
                    type="text"
                    id="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search posts..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>

            {{-- Status Filter --}}
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                    Status
                </label>
                <select
                    id="status"
                    wire:model.live="status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            {{-- Category Filter --}}
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                    Category
                </label>
                <select
                    id="category"
                    wire:model.live="category"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">All Categories</option>
                    @foreach ($this->categories as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Active Filters & Clear Button --}}
        @if ($search || $status || $category)
            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <span>Active filters:</span>
                    @if ($search)
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">
                            Search: "{{ $search }}"
                        </span>
                    @endif
                    @if ($status)
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">
                            Status: {{ ucfirst($status) }}
                        </span>
                    @endif
                    @if ($category)
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">
                            Category: {{ $this->categories[$category] }}
                        </span>
                    @endif
                </div>

                <button
                    wire:click="clearFilters"
                    class="text-sm text-gray-600 hover:text-gray-900 underline"
                >
                    Clear all filters
                </button>
            </div>
        @endif
    </div>

    {{-- Bulk Actions --}}
    @if (count($selectedIds) > 0)
        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center justify-between">
            <span class="text-sm text-blue-900">
                {{ count($selectedIds) }} {{ Str::plural('post', count($selectedIds)) }} selected
            </span>

            <button
                wire:click="bulkDelete"
                wire:confirm="Are you sure you want to delete the selected posts?"
                class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition"
            >
                Delete Selected
            </button>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        {{-- Loading Overlay --}}
        <div wire:loading.flex class="absolute inset-0 bg-white bg-opacity-75 z-10 items-center justify-center">
            <div class="flex items-center space-x-2">
                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm text-gray-600">Loading...</span>
            </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    {{-- Select All Checkbox --}}
                    <th class="px-6 py-3 text-left w-12">
                        <input
                            type="checkbox"
                            wire:model.live="selectAll"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                        >
                    </th>

                    {{-- Sortable Headers --}}
                    <th class="px-6 py-3 text-left">
                        <button
                            wire:click="sortBy('title')"
                            class="group inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700"
                        >
                            Title
                            @if ($sortField === 'title')
                                <svg class="ml-2 w-4 h-4 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                </svg>
                            @endif
                        </button>
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Author
                    </th>

                    <th class="px-6 py-3 text-left">
                        <button
                            wire:click="sortBy('status')"
                            class="group inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700"
                        >
                            Status
                            @if ($sortField === 'status')
                                <svg class="ml-2 w-4 h-4 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                </svg>
                            @endif
                        </button>
                    </th>

                    <th class="px-6 py-3 text-left">
                        <button
                            wire:click="sortBy('created_at')"
                            class="group inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700"
                        >
                            Created
                            @if ($sortField === 'created_at')
                                <svg class="ml-2 w-4 h-4 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                </svg>
                            @endif
                        </button>
                    </th>

                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($this->posts as $post)
                    <tr wire:key="post-{{ $post->id }}" class="hover:bg-gray-50">
                        {{-- Checkbox --}}
                        <td class="px-6 py-4">
                            <input
                                type="checkbox"
                                wire:model.live="selectedIds"
                                value="{{ $post->id }}"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                            >
                        </td>

                        {{-- Title --}}
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $post->title }}
                            </div>
                            @if ($post->category)
                                <div class="text-xs text-gray-500">
                                    {{ $post->category->name }}
                                </div>
                            @endif
                        </td>

                        {{-- Author --}}
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                {{ $post->author->name }}
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4">
                            @if ($post->status === 'published')
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    Published
                                </span>
                            @elseif ($post->status === 'draft')
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                    Draft
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                    Archived
                                </span>
                            @endif
                        </td>

                        {{-- Created --}}
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $post->created_at->diffForHumans() }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                            <a
                                href="{{ route('posts.edit', $post) }}"
                                class="text-blue-600 hover:text-blue-900"
                            >
                                Edit
                            </a>

                            <button
                                wire:click="delete({{ $post->id }})"
                                wire:confirm="Are you sure you want to delete this post?"
                                class="text-red-600 hover:text-red-900"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No posts found</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if ($search || $status || $category)
                                    Try adjusting your filters.
                                @else
                                    Get started by creating a new post.
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if ($this->posts->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $this->posts->links() }}
            </div>
        @endif
    </div>

    {{-- Success Message --}}
    @if (session()->has('message'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            class="fixed bottom-4 right-4 bg-green-50 border border-green-200 rounded-lg p-4 shadow-lg"
        >
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-green-800">{{ session('message') }}</p>
            </div>
        </div>
    @endif
</div>
```

## Uso nel Layout

```blade
{{-- resources/views/posts/index.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:post-table />
        </div>
    </div>
</x-app-layout>
```

## Route

```php
// routes/web.php
Route::get('/posts', \App\Livewire\PostTable::class)->name('posts.index');
```

## Features Implementate

✅ **Search** - Real-time con debounce 300ms
✅ **Filters** - Status e Category
✅ **Sorting** - Click su headers per ordinare
✅ **Pagination** - Con Livewire WithPagination
✅ **URL State** - Filtri persistiti nell'URL (bookmarkable)
✅ **Bulk Actions** - Select all e bulk delete
✅ **Loading States** - Overlay durante le operazioni
✅ **Empty States** - Messaggio quando non ci sono risultati
✅ **Success Messages** - Toast notifications
✅ **Responsive** - Mobile-friendly
✅ **Accessibility** - Semantic HTML e ARIA

## Customization Tips

### Cambiare il numero di risultati per pagina

```php
public int $perPage = 25; // Default 15
```

### Aggiungere più filtri

```php
#[Url]
public string $author = '';

// Nel query
->when($this->author, fn($query) => $query->where('author_id', $this->author))
```

### Aggiungere date range filter

```php
#[Url]
public ?string $dateFrom = null;

#[Url]
public ?string $dateTo = null;

// Nel query
->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
```

---

**Pro Tip**: Questo pattern è riutilizzabile per qualsiasi entity. Basta cambiare `Post` con il tuo model!
