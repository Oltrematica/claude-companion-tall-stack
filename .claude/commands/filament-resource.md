---
description: Generate a complete CRUD resource for Filament 4
---

# Generate Filament Resource

You are tasked with creating a complete **Filament 4.x Resource** with all necessary components for a full CRUD interface.

## Information Gathering

Ask the user for the following information (if not already provided):

1. **Model Name** (e.g., `Product`, `BlogPost`, `Customer`)
2. **Fields and Types**:
   - Field name
   - Field type (text, textarea, select, boolean, date, etc.)
   - Validation rules
   - Relationships (if any)

3. **Additional Options**:
   - Soft deletes? (yes/no)
   - Timestamps? (yes/no, default yes)
   - Media uploads? (yes/no)
   - Rich text editor? (yes/no)
   - Generate form sections? (yes/no)

4. **Table Configuration**:
   - Searchable columns
   - Sortable columns
   - Filters needed
   - Bulk actions

5. **Authorization**:
   - Generate policy? (yes/no)
   - Use Filament Shield? (yes/no)

## Step 1: Generate Base Resource

```bash
# Basic resource
php artisan make:filament-resource ModelName

# With model and migration
php artisan make:filament-resource ModelName --generate

# With soft deletes
php artisan make:filament-resource ModelName --soft-deletes

# With view page
php artisan make:filament-resource ModelName --view

# Complete with everything
php artisan make:filament-resource ModelName --generate --view --soft-deletes
```

## Step 2: Implement the Resource

Based on user input, create the resource file at `app/Filament/Resources/ModelNameResource.php`:

### Example: Product Resource

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    // Enable global search
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'description'];
    }

    // Global search result details
    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Category' => $record->category?->name,
            'SKU' => $record->sku,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->description('Enter the basic product details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'slug', ignoreRecord: true)
                            ->helperText('Auto-generated from product name'),

                        Forms\Components\MarkdownEditor::make('description')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'heading',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'table',
                                'undo',
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing & Inventory')
                    ->schema([
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->unique(Product::class, 'sku', ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->maxValue(999999.99)
                            ->step(0.01),

                        Forms\Components\TextInput::make('compare_price')
                            ->numeric()
                            ->prefix('$')
                            ->maxValue(999999.99)
                            ->step(0.01)
                            ->helperText('Original price (shown as strikethrough)'),

                        Forms\Components\TextInput::make('cost')
                            ->numeric()
                            ->prefix('$')
                            ->maxValue(999999.99)
                            ->step(0.01)
                            ->helperText('Cost per item (not shown to customers)'),

                        Forms\Components\TextInput::make('stock')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Available quantity'),

                        Forms\Components\Toggle::make('track_stock')
                            ->label('Track inventory')
                            ->default(true)
                            ->helperText('Enable stock tracking for this product'),
                    ])->columns(3),

                Forms\Components\Section::make('Organization')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('slug')
                                    ->required(),
                            ]),

                        Forms\Components\Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                            ]),

                        Forms\Components\Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                            ]),
                    ])->columns(3),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->directory('products/images')
                            ->visibility('public')
                            ->maxSize(5120),

                        Forms\Components\FileUpload::make('gallery')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->imageEditor()
                            ->directory('products/gallery')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->maxFiles(10)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('SEO')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->maxLength(60)
                            ->helperText('Recommended: 50-60 characters'),

                        Forms\Components\Textarea::make('meta_description')
                            ->rows(3)
                            ->maxLength(160)
                            ->helperText('Recommended: 150-160 characters'),

                        Forms\Components\TagsInput::make('meta_keywords')
                            ->separator(','),
                    ])->columns(1)->collapsed(),

                Forms\Components\Section::make('Status & Publishing')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive products won\'t be visible on the website'),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false)
                            ->helperText('Show this product in featured sections'),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->native(false)
                            ->default(now())
                            ->helperText('Product will be visible from this date'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Product $record): string => $record->sku),

                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state > 100 => 'success',
                        $state > 10 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => $state . ' units')
                    ->alignEnd(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueLabel('Featured only')
                    ->falseLabel('Not featured')
                    ->native(false),

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Category'),

                Tables\Filters\Filter::make('price')
                    ->form([
                        Forms\Components\TextInput::make('price_from')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('price_to')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $price): Builder =>
                                    $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (Builder $query, $price): Builder =>
                                    $query->where('price', '<=', $price),
                            );
                    }),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Low stock')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '<=', 10))
                    ->toggle(),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ReplicateAction::make()
                        ->excludeAttributes(['slug', 'sku'])
                        ->beforeReplicaSaved(function (Product $replica): void {
                            $replica->name = $replica->name . ' (Copy)';
                            $replica->slug = Str::slug($replica->name);
                            $replica->sku = 'COPY-' . $replica->sku;
                        }),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) =>
                            $records->each->update(['is_active' => true])
                        )
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) =>
                            $records->each->update(['is_active' => false])
                        )
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('feature')
                        ->label('Mark as Featured')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(fn (Collection $records) =>
                            $records->each->update(['is_featured' => true])
                        )
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getRelations(): array
    {
        return [
            // Add relation managers here
            // RelationManagers\ReviewsRelationManager::class,
            // RelationManagers\VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 100 ? 'warning' : 'primary';
    }
}
```

## Step 3: Create Model (if not exists)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sku',
        'price',
        'compare_price',
        'cost',
        'stock',
        'track_stock',
        'category_id',
        'brand_id',
        'featured_image',
        'gallery',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
        'is_featured',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock' => 'integer',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'gallery' => 'array',
        'meta_keywords' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    // Accessor for profit margin
    public function getProfitMarginAttribute(): float
    {
        if ($this->cost && $this->price) {
            return (($this->price - $this->cost) / $this->price) * 100;
        }
        return 0;
    }

    // Scope for active products
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for featured products
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Scope for in stock products
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
}
```

## Step 4: Create Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('track_stock')->default(true);
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->json('meta_keywords')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active']);
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

Run migration:

```bash
php artisan migrate
```

## Step 5: Create Policy (Optional)

```bash
php artisan make:policy ProductPolicy --model=Product
```

```php
<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_product');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('view_product');
    }

    public function create(User $user): bool
    {
        return $user->can('create_product');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('update_product');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('delete_product');
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->can('restore_product');
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->can('force_delete_product');
    }
}
```

If using Filament Shield:

```bash
php artisan shield:generate --resource=ProductResource
```

## Step 6: Create Tests

```php
<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    public function test_can_render_list_page(): void
    {
        $this->get(ProductResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_render_create_page(): void
    {
        $this->get(ProductResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_can_create_product(): void
    {
        $product = Product::factory()->make();

        Livewire::test(ProductResource\Pages\CreateProduct::class)
            ->fillForm([
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'price' => $product->price,
                'stock' => $product->stock,
                'category_id' => $product->category_id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'name' => $product->name,
            'sku' => $product->sku,
        ]);
    }

    public function test_can_edit_product(): void
    {
        $product = Product::factory()->create();

        $this->get(ProductResource::getUrl('edit', ['record' => $product]))
            ->assertSuccessful();
    }
}
```

## Completion Checklist

- [ ] Resource file created
- [ ] Model created/updated
- [ ] Migration created and run
- [ ] Policy created (if needed)
- [ ] Tests written
- [ ] Resource registered in panel
- [ ] Navigation icon set
- [ ] Form validation configured
- [ ] Table columns optimized
- [ ] Filters implemented
- [ ] Bulk actions added

## Next Steps

Suggest to the user:

1. **Add Relation Managers**
   - Use `/filament-relation-manager` if needed

2. **Customize Further**
   - Add custom actions
   - Create custom widgets
   - Implement advanced filters

3. **Test the Resource**
   - Run `php artisan test`
   - Manually test CRUD operations

4. **Optimize**
   - Add database indexes
   - Implement eager loading
   - Use caching where appropriate

---

**Resource created successfully!** Visit `/admin/products` to manage your products.
