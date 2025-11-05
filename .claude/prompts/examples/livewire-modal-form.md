# Livewire Modal Form Component

Complete example of a reusable modal component with form validation and Alpine.js integration.

## Component Class

```php
<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\On;

class CreateCategory extends Component
{
    public bool $open = false;

    #[Rule('required|string|min:3|max:255')]
    public string $name = '';

    #[Rule('nullable|string|max:500')]
    public string $description = '';

    #[Rule('nullable|string|max:7')]
    public string $color = '#3B82F6';

    #[Rule('nullable|image|max:1024')]
    public $icon;

    #[On('open-modal')]
    public function openModal(): void
    {
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        $this->reset(['name', 'description', 'color', 'icon']);
        $this->resetValidation();
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->icon) {
            $validated['icon'] = $this->icon->store('categories', 'public');
        }

        $category = Category::create($validated);

        $this->dispatch('category-created', categoryId: $category->id);
        $this->dispatch('notify', message: 'Category created successfully!');

        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.create-category');
    }
}
```

## Component View

```blade
<div>
    {{-- Trigger Button --}}
    <button
        wire:click="openModal"
        type="button"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
    >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create Category
    </button>

    {{-- Modal --}}
    <div
        x-data="{ show: @entangle('open') }"
        x-show="show"
        x-on:keydown.escape.window="show = false"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        {{-- Backdrop --}}
        <div
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            x-on:click="show = false"
        ></div>

        {{-- Modal Panel --}}
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
                x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
            >
                {{-- Header --}}
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900" id="modal-title">
                            Create New Category
                        </h3>
                        <button
                            wire:click="closeModal"
                            type="button"
                            class="text-gray-400 hover:text-gray-500"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Form --}}
                    <form wire:submit="save" class="space-y-4">
                        {{-- Name Field --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                wire:model="name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('name') border-red-500 @enderror"
                                placeholder="Enter category name"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Description Field --}}
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Description
                            </label>
                            <textarea
                                id="description"
                                wire:model="description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('description') border-red-500 @enderror"
                                placeholder="Enter category description"
                            ></textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Color Picker --}}
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700">
                                Color
                            </label>
                            <div class="mt-1 flex items-center gap-2">
                                <input
                                    type="color"
                                    id="color"
                                    wire:model.live="color"
                                    class="h-10 w-20 rounded border border-gray-300"
                                >
                                <input
                                    type="text"
                                    wire:model="color"
                                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="#3B82F6"
                                >
                            </div>
                            @error('color')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Icon Upload --}}
                        <div>
                            <label for="icon" class="block text-sm font-medium text-gray-700">
                                Icon
                            </label>
                            <div class="mt-1 flex items-center gap-4">
                                @if($icon)
                                    <img src="{{ $icon->temporaryUrl() }}" alt="Preview" class="h-16 w-16 rounded object-cover">
                                @endif
                                <input
                                    type="file"
                                    id="icon"
                                    wire:model="icon"
                                    accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                >
                            </div>
                            <div wire:loading wire:target="icon" class="mt-1 text-sm text-gray-500">
                                Uploading...
                            </div>
                            @error('icon')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </form>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button
                        wire:click="save"
                        wire:loading.attr="disabled"
                        type="button"
                        class="inline-flex w-full justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="save">Create Category</span>
                        <span wire:loading wire:target="save" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
                        </span>
                    </button>
                    <button
                        wire:click="closeModal"
                        type="button"
                        class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush
```

## Usage

### In Blade View

```blade
<livewire:create-category />
```

### Trigger from Another Component

```php
// In another component
$this->dispatch('open-modal');
```

### Listen for Created Event

```php
// In parent component
#[On('category-created')]
public function categoryCreated($categoryId)
{
    $this->categories = Category::all(); // Refresh list
}
```

## Features

- ✅ Alpine.js animations
- ✅ ESC key to close
- ✅ Click outside to close
- ✅ Form validation with error messages
- ✅ File upload with preview
- ✅ Color picker
- ✅ Loading states
- ✅ Event dispatching
- ✅ Keyboard accessibility
- ✅ Responsive design

## Reusable Modal Component

For a more reusable approach:

```php
// app/Livewire/Modal.php
class Modal extends Component
{
    public bool $open = false;
    public string $title = '';
    public string $size = 'sm:max-w-lg'; // sm, md, lg, xl

    #[On('open-modal')]
    public function openModal(string $title = ''): void
    {
        $this->title = $title;
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
    }

    public function render()
    {
        return view('livewire.modal');
    }
}
```

```blade
{{-- livewire/modal.blade.php --}}
<div x-data="{ show: @entangle('open') }" ...>
    {{-- Backdrop --}}
    <div x-show="show" ...></div>

    {{-- Modal --}}
    <div class="flex min-h-full items-end justify-center ...">
        <div class="{{ $size }}">
            {{-- Header --}}
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="flex items-start justify-between mb-4">
                    <h3>{{ $title }}</h3>
                    <button wire:click="closeModal">×</button>
                </div>

                {{-- Content --}}
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @if(isset($footer))
                <div class="bg-gray-50 px-4 py-3">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
```

### Usage of Reusable Modal

```blade
<livewire:modal title="Create Category">
    <form wire:submit="save">
        {{-- Form fields --}}
    </form>

    <x-slot:footer>
        <button wire:click="save">Save</button>
        <button wire:click="$dispatch('close-modal')">Cancel</button>
    </x-slot:footer>
</livewire:modal>
```
