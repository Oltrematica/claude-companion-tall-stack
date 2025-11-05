---
description: Add data export functionality (CSV, Excel, PDF) to TALL Stack models
---

# TALL Stack Data Export

You are an expert in implementing data export functionality for TALL Stack (Tailwind, Alpine.js, Laravel, Livewire) applications. Your goal is to add robust, performant export capabilities for various formats.

## Your Task

Guide the user through implementing data export functionality:

1. **Gather Requirements**
   Ask the user:
   - Which model(s) need export?
   - What export formats? (CSV, Excel, PDF)
   - What data to export? (all fields, selected fields, relationships)
   - Export triggers? (button click, scheduled, API)
   - Data volume? (affects performance strategy)
   - Should exports be queued for large datasets?
   - Need for custom formatting or styling?

2. **Choose Export Solution**

   ### A. Laravel Excel (Recommended for CSV/Excel)
   **Best for:** CSV, Excel (XLSX, XLS), ODS
   **Pros:** Powerful, well-maintained, chunking support, queueable

   ### B. Spatie Laravel PDF
   **Best for:** PDF reports with custom styling
   **Pros:** Blade-based, easy styling, headers/footers

   ### C. Laravel Dompdf
   **Best for:** Simple PDF generation
   **Pros:** Easy to use, HTML to PDF

3. **Installation**

   ```bash
   # For CSV/Excel
   composer require maatwebsite/excel
   php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"

   # For PDF
   composer require barryvdh/laravel-dompdf
   # or
   composer require spatie/laravel-pdf
   ```

4. **Implementation: CSV/Excel Export**

   ### Create Export Class
   ```bash
   php artisan make:export ProductsExport --model=Product
   ```

   ### Export Class
   ```php
   <?php

   namespace App\Exports;

   use App\Models\Product;
   use Maatwebsite\Excel\Concerns\FromQuery;
   use Maatwebsite\Excel\Concerns\WithHeadings;
   use Maatwebsite\Excel\Concerns\WithMapping;
   use Maatwebsite\Excel\Concerns\WithStyles;
   use Maatwebsite\Excel\Concerns\WithColumnWidths;
   use Maatwebsite\Excel\Concerns\ShouldAutoSize;
   use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
   use Maatwebsite\Excel\Concerns\Exportable;

   class ProductsExport implements
       FromQuery,
       WithHeadings,
       WithMapping,
       WithStyles,
       ShouldAutoSize
   {
       use Exportable;

       protected $filters;

       public function __construct($filters = [])
       {
           $this->filters = $filters;
       }

       /**
        * Query for export
        */
       public function query()
       {
           $query = Product::query()
               ->with(['category', 'brand']);

           // Apply filters
           if (!empty($this->filters['category'])) {
               $query->where('category_id', $this->filters['category']);
           }

           if (!empty($this->filters['search'])) {
               $query->where('name', 'like', '%' . $this->filters['search'] . '%');
           }

           if (!empty($this->filters['date_from'])) {
               $query->where('created_at', '>=', $this->filters['date_from']);
           }

           return $query;
       }

       /**
        * Column headings
        */
       public function headings(): array
       {
           return [
               'ID',
               'SKU',
               'Name',
               'Description',
               'Category',
               'Brand',
               'Price',
               'Stock',
               'Status',
               'Created At',
               'Updated At',
           ];
       }

       /**
        * Map data for each row
        */
       public function map($product): array
       {
           return [
               $product->id,
               $product->sku,
               $product->name,
               $product->description,
               $product->category?->name,
               $product->brand?->name,
               number_format($product->price, 2),
               $product->stock,
               $product->is_active ? 'Active' : 'Inactive',
               $product->created_at->format('Y-m-d H:i:s'),
               $product->updated_at->format('Y-m-d H:i:s'),
           ];
       }

       /**
        * Styling
        */
       public function styles(Worksheet $sheet)
       {
           return [
               1 => [
                   'font' => ['bold' => true],
                   'fill' => [
                       'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                       'startColor' => ['rgb' => 'E2E8F0']
                   ]
               ],
           ];
       }
   }
   ```

   ### Queued Export (for large datasets)
   ```php
   <?php

   namespace App\Exports;

   use Maatwebsite\Excel\Concerns\FromQuery;
   use Maatwebsite\Excel\Concerns\WithHeadings;
   use Maatwebsite\Excel\Concerns\WithChunkReading;
   use Maatwebsite\Excel\Concerns\ShouldQueue;

   class ProductsExport implements
       FromQuery,
       WithHeadings,
       WithChunkReading,
       ShouldQueue
   {
       public function chunkSize(): int
       {
           return 1000;
       }

       // ... rest of the implementation
   }
   ```

5. **Create Livewire Export Component**

   ```bash
   php artisan make:livewire ExportProducts
   ```

   ### Component Class
   ```php
   <?php

   namespace App\Livewire;

   use App\Exports\ProductsExport;
   use Livewire\Component;
   use Maatwebsite\Excel\Facades\Excel;
   use Illuminate\Support\Facades\Storage;

   class ExportProducts extends Component
   {
       public $format = 'xlsx';
       public $filters = [];
       public $showModal = false;
       public $exporting = false;

       public function export()
       {
           $this->validate([
               'format' => 'required|in:csv,xlsx,pdf',
           ]);

           $this->exporting = true;

           try {
               $fileName = 'products-' . now()->format('Y-m-d-His') . '.' . $this->format;

               switch ($this->format) {
                   case 'csv':
                       return Excel::download(
                           new ProductsExport($this->filters),
                           $fileName,
                           \Maatwebsite\Excel\Excel::CSV
                       );

                   case 'xlsx':
                       return Excel::download(
                           new ProductsExport($this->filters),
                           $fileName,
                           \Maatwebsite\Excel\Excel::XLSX
                       );

                   case 'pdf':
                       return $this->exportPdf($fileName);
               }
           } catch (\Exception $e) {
               $this->dispatch('export-failed', message: $e->getMessage());
           } finally {
               $this->exporting = false;
               $this->showModal = false;
           }
       }

       public function exportQueued()
       {
           $fileName = 'products-' . now()->format('Y-m-d-His') . '.xlsx';

           // Store in storage/app/exports
           Excel::queue(new ProductsExport($this->filters), 'exports/' . $fileName)
               ->chain([
                   // Send notification when done
                   new \App\Jobs\NotifyUserOfCompletedExport(auth()->id(), $fileName)
               ]);

           $this->dispatch('export-queued', message: 'Export started! You\'ll be notified when ready.');
           $this->showModal = false;
       }

       protected function exportPdf($fileName)
       {
           $products = Product::with(['category', 'brand'])
               ->when($this->filters['category'] ?? null, function ($query, $category) {
                   $query->where('category_id', $category);
               })
               ->get();

           $pdf = \PDF::loadView('exports.products-pdf', [
               'products' => $products,
               'exportDate' => now(),
           ]);

           return response()->streamDownload(function () use ($pdf) {
               echo $pdf->stream();
           }, $fileName);
       }

       public function render()
       {
           return view('livewire.export-products');
       }
   }
   ```

   ### Component View
   ```blade
   <div>
       {{-- Export Button --}}
       <button
           wire:click="showModal = true"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
       >
           <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
           </svg>
           Export
       </button>

       {{-- Export Modal --}}
       @if($showModal)
           <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
               <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                   {{-- Background overlay --}}
                   <div
                       class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                       wire:click="showModal = false"
                   ></div>

                   {{-- Modal panel --}}
                   <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                       <div>
                           <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                               <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                               </svg>
                           </div>

                           <div class="mt-3 text-center sm:mt-5">
                               <h3 class="text-lg leading-6 font-medium text-gray-900">
                                   Export Products
                               </h3>

                               <div class="mt-4 space-y-4">
                                   {{-- Format Selection --}}
                                   <div class="text-left">
                                       <label class="block text-sm font-medium text-gray-700 mb-2">
                                           Export Format
                                       </label>
                                       <div class="space-y-2">
                                           <label class="flex items-center">
                                               <input type="radio" wire:model="format" value="xlsx" class="mr-2">
                                               <span class="text-sm">Excel (.xlsx) - Best for data analysis</span>
                                           </label>
                                           <label class="flex items-center">
                                               <input type="radio" wire:model="format" value="csv" class="mr-2">
                                               <span class="text-sm">CSV (.csv) - Universal format</span>
                                           </label>
                                           <label class="flex items-center">
                                               <input type="radio" wire:model="format" value="pdf" class="mr-2">
                                               <span class="text-sm">PDF (.pdf) - Printable format</span>
                                           </label>
                                       </div>
                                   </div>

                                   {{-- Filters Info --}}
                                   @if(!empty(array_filter($filters)))
                                       <div class="bg-blue-50 border border-blue-200 rounded p-3 text-left">
                                           <p class="text-sm text-blue-800 font-medium">Active Filters:</p>
                                           <ul class="mt-1 text-sm text-blue-700">
                                               @foreach(array_filter($filters) as $key => $value)
                                                   <li>{{ ucfirst($key) }}: {{ $value }}</li>
                                               @endforeach
                                           </ul>
                                       </div>
                                   @endif
                               </div>
                           </div>
                       </div>

                       <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                           <button
                               wire:click="export"
                               wire:loading.attr="disabled"
                               class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm disabled:opacity-50"
                           >
                               <span wire:loading.remove wire:target="export">Export Now</span>
                               <span wire:loading wire:target="export" class="flex items-center">
                                   <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                       <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                       <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                   </svg>
                                   Exporting...
                               </span>
                           </button>
                           <button
                               wire:click="showModal = false"
                               type="button"
                               class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                           >
                               Cancel
                           </button>
                       </div>

                       {{-- Large Dataset Option --}}
                       <div class="mt-4 pt-4 border-t border-gray-200">
                           <button
                               wire:click="exportQueued"
                               class="w-full text-center text-sm text-gray-600 hover:text-gray-800"
                           >
                               Large dataset? Queue export and get notified →
                           </button>
                       </div>
                   </div>
               </div>
           </div>
       @endif
   </div>
   ```

6. **PDF Export Template**

   Create `resources/views/exports/products-pdf.blade.php`:

   ```blade
   <!DOCTYPE html>
   <html>
   <head>
       <meta charset="utf-8">
       <title>Products Export</title>
       <style>
           body {
               font-family: Arial, sans-serif;
               font-size: 12px;
               color: #333;
           }
           .header {
               text-align: center;
               margin-bottom: 30px;
               border-bottom: 2px solid #333;
               padding-bottom: 10px;
           }
           .header h1 {
               margin: 0;
               font-size: 24px;
           }
           .header .date {
               color: #666;
               font-size: 10px;
           }
           table {
               width: 100%;
               border-collapse: collapse;
               margin-top: 20px;
           }
           th {
               background-color: #f3f4f6;
               font-weight: bold;
               padding: 8px;
               text-align: left;
               border-bottom: 2px solid #333;
           }
           td {
               padding: 6px 8px;
               border-bottom: 1px solid #e5e7eb;
           }
           tr:nth-child(even) {
               background-color: #f9fafb;
           }
           .footer {
               margin-top: 30px;
               text-align: center;
               font-size: 10px;
               color: #666;
           }
       </style>
   </head>
   <body>
       <div class="header">
           <h1>Products Export</h1>
           <p class="date">Generated on {{ $exportDate->format('F j, Y - H:i:s') }}</p>
       </div>

       <table>
           <thead>
               <tr>
                   <th>ID</th>
                   <th>SKU</th>
                   <th>Name</th>
                   <th>Category</th>
                   <th>Price</th>
                   <th>Stock</th>
                   <th>Status</th>
               </tr>
           </thead>
           <tbody>
               @foreach($products as $product)
                   <tr>
                       <td>{{ $product->id }}</td>
                       <td>{{ $product->sku }}</td>
                       <td>{{ $product->name }}</td>
                       <td>{{ $product->category?->name }}</td>
                       <td>€{{ number_format($product->price, 2) }}</td>
                       <td>{{ $product->stock }}</td>
                       <td>{{ $product->is_active ? 'Active' : 'Inactive' }}</td>
                   </tr>
               @endforeach
           </tbody>
       </table>

       <div class="footer">
           <p>Total products: {{ $products->count() }}</p>
           <p>This is an automated export from {{ config('app.name') }}</p>
       </div>
   </body>
   </html>
   ```

7. **Notification for Queued Exports**

   ```php
   <?php

   namespace App\Jobs;

   use App\Models\User;
   use App\Notifications\ExportReady;
   use Illuminate\Bus\Queueable;
   use Illuminate\Contracts\Queue\ShouldQueue;
   use Illuminate\Foundation\Bus\Dispatchable;
   use Illuminate\Queue\InteractsWithQueue;
   use Illuminate\Queue\SerializesModels;

   class NotifyUserOfCompletedExport implements ShouldQueue
   {
       use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

       public function __construct(
           protected int $userId,
           protected string $fileName
       ) {}

       public function handle(): void
       {
           $user = User::find($this->userId);

           $user->notify(new ExportReady($this->fileName));
       }
   }
   ```

## Advanced Features

### 1. Custom Columns Selection
Allow users to choose which columns to export

### 2. Scheduled Exports
```php
// In app/Console/Kernel.php
$schedule->call(function () {
    Excel::store(new ProductsExport, 'exports/daily-products.xlsx', 'public');
})->daily();
```

### 3. Email Export
```php
Excel::store(new ProductsExport, 'products.xlsx', 'public', function ($excel) {
    Mail::to('admin@example.com')->send(new ExportReady($excel));
});
```

### 4. Multiple Sheets
```php
public function sheets(): array
{
    return [
        'Products' => new ProductsExport,
        'Categories' => new CategoriesExport,
    ];
}
```

## Checklist

- [ ] Laravel Excel installed
- [ ] Export class created
- [ ] Livewire component created
- [ ] UI with format selection
- [ ] Filters applied to export
- [ ] Queued export for large datasets
- [ ] PDF template designed
- [ ] Notifications for completed exports
- [ ] Tests written
- [ ] Performance optimized

## Start

Ask the user:
1. Which model needs export functionality?
2. What formats are needed? (CSV, Excel, PDF)
3. Expected data volume? (affects queueing strategy)
4. Should filters be applied to exports?
5. Need for scheduled/automated exports?

Then proceed with the implementation.
