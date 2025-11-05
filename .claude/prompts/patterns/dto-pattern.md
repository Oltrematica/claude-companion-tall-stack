# Data Transfer Object (DTO) Pattern

DTOs are simple objects that carry data between processes. Use them to ensure type safety and data structure consistency.

## When to Use

✅ **USE DTOs when:**
- Passing data between layers (API → Service → Repository)
- Type safety is important
- Data structure is complex
- Need to transform/validate data
- Working with external APIs
- Building type-safe forms

❌ **DON'T use DTOs when:**
- Simple data passing (arrays are fine)
- Data structure is trivial
- Over-engineering simple operations

## Basic Structure

```php
<?php

namespace App\DataTransferObjects;

class UserData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $phone = null,
    ) {}

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            phone: $data['phone'] ?? null,
        );
    }

    /**
     * Create from Request
     */
    public static function fromRequest(Request $request): self
    {
        return self::fromArray($request->validated());
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'phone' => $this->phone,
        ];
    }
}
```

## With Spatie Laravel Data Package

```bash
composer require spatie/laravel-data
```

```php
<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;

class UserData extends Data
{
    public function __construct(
        #[Required, Min(3)]
        public string $name,

        #[Required, Email]
        public string $email,

        #[Required, Min(8)]
        public string $password,

        public ?string $phone = null,
    ) {}
}

// Automatic validation and creation from request
$userData = UserData::from($request);

// To array
$array = $userData->toArray();

// To JSON
$json = $userData->toJson();
```

## Complex DTO Example

```php
<?php

namespace App\DataTransferObjects;

use App\Enums\OrderStatus;
use Illuminate\Support\Collection;

class OrderData
{
    public function __construct(
        public readonly int $userId,
        public readonly Collection $items, // Collection of OrderItemData
        public readonly AddressData $shippingAddress,
        public readonly AddressData $billingAddress,
        public readonly ?string $discountCode = null,
        public readonly OrderStatus $status = OrderStatus::PENDING,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            userId: auth()->id(),
            items: collect($request->items)->map(
                fn($item) => OrderItemData::fromArray($item)
            ),
            shippingAddress: AddressData::fromArray($request->shipping_address),
            billingAddress: AddressData::fromArray($request->billing_address),
            discountCode: $request->discount_code,
        );
    }

    public function calculateTotal(): float
    {
        $subtotal = $this->items->sum(fn($item) => $item->total());

        if ($this->discountCode) {
            $discount = $this->calculateDiscount($subtotal);
            $subtotal -= $discount;
        }

        return $subtotal + $this->calculateTax($subtotal) + $this->calculateShipping();
    }

    public function calculateTax(float $subtotal): float
    {
        $taxRate = $this->shippingAddress->getTaxRate();
        return $subtotal * $taxRate;
    }

    public function calculateShipping(): float
    {
        // Shipping calculation logic
        return 9.99;
    }

    private function calculateDiscount(float $subtotal): float
    {
        // Discount calculation logic
        return 0;
    }
}

class OrderItemData
{
    public function __construct(
        public readonly int $productId,
        public readonly int $quantity,
        public readonly float $price,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            quantity: $data['quantity'],
            price: $data['price'],
        );
    }

    public function total(): float
    {
        return $this->quantity * $this->price;
    }
}

class AddressData
{
    public function __construct(
        public readonly string $line1,
        public readonly ?string $line2,
        public readonly string $city,
        public readonly string $state,
        public readonly string $zipCode,
        public readonly string $country,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            line1: $data['line1'],
            line2: $data['line2'] ?? null,
            city: $data['city'],
            state: $data['state'],
            zipCode: $data['zip_code'],
            country: $data['country'],
        );
    }

    public function getTaxRate(): float
    {
        // Tax rate based on state/country
        return match($this->state) {
            'CA' => 0.0725,
            'NY' => 0.08875,
            default => 0.06,
        };
    }

    public function toArray(): array
    {
        return [
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zipCode,
            'country' => $this->country,
        ];
    }
}
```

## Usage in Service

```php
<?php

namespace App\Services;

use App\DataTransferObjects\OrderData;
use App\Models\Order;

class OrderService
{
    public function createOrder(OrderData $orderData): Order
    {
        $order = Order::create([
            'user_id' => $orderData->userId,
            'status' => $orderData->status->value,
            'subtotal' => $orderData->calculateTotal(),
            'tax' => $orderData->calculateTax($orderData->calculateTotal()),
            'shipping' => $orderData->calculateShipping(),
            'total' => $orderData->calculateTotal(),
            'shipping_address' => $orderData->shippingAddress->toArray(),
            'billing_address' => $orderData->billingAddress->toArray(),
        ]);

        foreach ($orderData->items as $itemData) {
            $order->items()->create([
                'product_id' => $itemData->productId,
                'quantity' => $itemData->quantity,
                'price' => $itemData->price,
                'total' => $itemData->total(),
            ]);
        }

        return $order;
    }
}
```

## Usage in Controller

```php
<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\UserData;
use App\Services\UserService;
use App\Http\Requests\RegisterRequest;

class RegisterController extends Controller
{
    public function store(
        RegisterRequest $request,
        UserService $userService
    ) {
        $userData = UserData::fromRequest($request);

        $user = $userService->createUser($userData);

        return response()->json($user, 201);
    }
}
```

## Usage in Livewire

```php
<?php

namespace App\Livewire;

use App\DataTransferObjects\OrderData;
use App\Services\OrderService;
use Livewire\Component;

class Checkout extends Component
{
    public array $items = [];
    public array $shippingAddress = [];
    public array $billingAddress = [];
    public ?string $discountCode = null;

    public function checkout(OrderService $orderService)
    {
        $this->validate([
            'items' => 'required|array',
            'shippingAddress' => 'required|array',
            'billingAddress' => 'required|array',
        ]);

        $orderData = new OrderData(
            userId: auth()->id(),
            items: collect($this->items)->map(
                fn($item) => OrderItemData::fromArray($item)
            ),
            shippingAddress: AddressData::fromArray($this->shippingAddress),
            billingAddress: AddressData::fromArray($this->billingAddress),
            discountCode: $this->discountCode,
        );

        $order = $orderService->createOrder($orderData);

        $this->redirectRoute('orders.show', $order);
    }
}
```

## DTO for API Responses

```php
<?php

namespace App\DataTransferObjects;

class ProductResponseData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly float $price,
        public readonly ?string $description,
        public readonly bool $inStock,
        public readonly array $images,
        public readonly ?CategoryData $category,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            name: $product->name,
            slug: $product->slug,
            price: $product->price,
            description: $product->description,
            inStock: $product->stock > 0,
            images: $product->images->map(fn($img) => $img->url)->toArray(),
            category: $product->category
                ? CategoryData::fromModel($product->category)
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'formatted_price' => '$' . number_format($this->price, 2),
            'description' => $this->description,
            'in_stock' => $this->inStock,
            'images' => $this->images,
            'category' => $this->category?->toArray(),
        ];
    }
}
```

## DTO with Validation

```php
<?php

namespace App\DataTransferObjects;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreatePostData
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly int $categoryId,
        public readonly array $tags = [],
    ) {}

    public static function fromArray(array $data): self
    {
        self::validate($data);

        return new self(
            title: $data['title'],
            content: $data['content'],
            categoryId: $data['category_id'],
            tags: $data['tags'] ?? [],
        );
    }

    private static function validate(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
```

## Immutable DTO with Modification

```php
<?php

namespace App\DataTransferObjects;

class UserData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $phone = null,
    ) {}

    /**
     * Create a new instance with modified name
     */
    public function withName(string $name): self
    {
        return new self(
            name: $name,
            email: $this->email,
            phone: $this->phone,
        );
    }

    /**
     * Create a new instance with modified email
     */
    public function withEmail(string $email): self
    {
        return new self(
            name: $this->name,
            email: $email,
            phone: $this->phone,
        );
    }
}

// Usage
$userData = new UserData('John', 'john@example.com');
$updatedData = $userData->withName('Jane');
```

## Testing DTOs

```php
<?php

use App\DataTransferObjects\UserData;

test('creates DTO from array', function () {
    $dto = UserData::fromArray([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'phone' => '555-1234',
    ]);

    expect($dto->name)->toBe('John Doe');
    expect($dto->email)->toBe('john@example.com');
    expect($dto->phone)->toBe('555-1234');
});

test('converts DTO to array', function () {
    $dto = new UserData(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'password123',
    );

    $array = $dto->toArray();

    expect($array)->toHaveKeys(['name', 'email', 'password']);
    expect($array['name'])->toBe('John Doe');
});
```

## Best Practices

### ✅ DO
- Make DTOs immutable (readonly properties)
- Use named parameters for clarity
- Add factory methods (fromArray, fromRequest, fromModel)
- Include toArray() method for serialization
- Use type hints for all properties
- Keep DTOs simple (data only, minimal logic)

### ❌ DON'T
- Don't put business logic in DTOs
- Don't make DTOs mutable
- Don't use DTOs for database models (use Eloquent)
- Don't over-engineer simple data structures
- Don't forget to handle null values appropriately
