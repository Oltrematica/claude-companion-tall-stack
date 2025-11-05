---
description: Generate RESTful API resources for TALL Stack models
---

# TALL Stack API Resource Generator

You are an expert in creating RESTful APIs for TALL Stack (Tailwind, Alpine.js, Laravel, Livewire) applications. Your goal is to generate complete, secure, and well-documented API resources following Laravel best practices.

## Your Task

Guide the user through creating comprehensive API resources for their models:

1. **Gather Requirements**
   Ask the user:
   - Which model to create API for?
   - What operations needed? (CRUD, custom actions)
   - Authentication required? (Sanctum, Passport, none)
   - Who will consume the API? (Mobile app, SPA, third-party)
   - Rate limiting requirements?
   - API versioning needed?
   - Pagination preferences?
   - Filtering and sorting requirements?

2. **Generate API Components**

   Create the following for each model:

   ### A. API Resource Classes
   ```bash
   php artisan make:resource {Model}Resource
   php artisan make:resource {Model}Collection
   ```

   ### B. API Controller
   ```bash
   php artisan make:controller Api/V1/{Model}Controller --api
   ```

   ### C. Form Requests (Validation)
   ```bash
   php artisan make:request Store{Model}Request
   php artisan make:request Update{Model}Request
   ```

   ### D. API Routes
   In `routes/api.php`

   ### E. Policy (Authorization)
   ```bash
   php artisan make:policy {Model}Policy --model={Model}
   ```

   ### F. API Documentation
   Generate OpenAPI/Swagger documentation

3. **Implementation Pattern**

   ### Resource Class Example
   ```php
   <?php

   namespace App\Http\Resources;

   use Illuminate\Http\Request;
   use Illuminate\Http\Resources\Json\JsonResource;

   class ProductResource extends JsonResource
   {
       /**
        * Transform the resource into an array.
        *
        * @return array<string, mixed>
        */
       public function toArray(Request $request): array
       {
           return [
               'id' => $this->id,
               'name' => $this->name,
               'slug' => $this->slug,
               'description' => $this->description,
               'price' => [
                   'amount' => $this->price,
                   'formatted' => $this->formatted_price,
                   'currency' => 'EUR',
               ],
               'stock' => $this->stock,
               'is_available' => $this->is_available,

               // Relationships
               'category' => new CategoryResource($this->whenLoaded('category')),
               'images' => ImageResource::collection($this->whenLoaded('images')),

               // Conditional fields
               'cost' => $this->when($request->user()?->isAdmin(), $this->cost),

               // Timestamps
               'created_at' => $this->created_at?->toISOString(),
               'updated_at' => $this->updated_at?->toISOString(),

               // HATEOAS links
               'links' => [
                   'self' => route('api.v1.products.show', $this->id),
                   'category' => route('api.v1.categories.show', $this->category_id),
               ],
           ];
       }
   }
   ```

   ### API Controller Example
   ```php
   <?php

   namespace App\Http\Controllers\Api\V1;

   use App\Http\Controllers\Controller;
   use App\Http\Requests\StoreProductRequest;
   use App\Http\Requests\UpdateProductRequest;
   use App\Http\Resources\ProductResource;
   use App\Http\Resources\ProductCollection;
   use App\Models\Product;
   use Illuminate\Http\Request;
   use Illuminate\Http\Response;

   class ProductController extends Controller
   {
       public function __construct()
       {
           $this->authorizeResource(Product::class);
       }

       /**
        * Display a listing of products.
        */
       public function index(Request $request)
       {
           $products = Product::query()
               ->with(['category', 'images'])
               ->when($request->search, function ($query, $search) {
                   $query->where('name', 'like', "%{$search}%");
               })
               ->when($request->category, function ($query, $category) {
                   $query->where('category_id', $category);
               })
               ->when($request->sort, function ($query, $sort) {
                   $direction = $request->direction === 'desc' ? 'desc' : 'asc';
                   $query->orderBy($sort, $direction);
               })
               ->paginate($request->per_page ?? 15);

           return new ProductCollection($products);
       }

       /**
        * Store a newly created product.
        */
       public function store(StoreProductRequest $request)
       {
           $product = Product::create($request->validated());

           if ($request->has('images')) {
               $product->syncImages($request->images);
           }

           return new ProductResource($product->load(['category', 'images']))
               ->response()
               ->setStatusCode(Response::HTTP_CREATED);
       }

       /**
        * Display the specified product.
        */
       public function show(Product $product)
       {
           return new ProductResource($product->load(['category', 'images']));
       }

       /**
        * Update the specified product.
        */
       public function update(UpdateProductRequest $request, Product $product)
       {
           $product->update($request->validated());

           if ($request->has('images')) {
               $product->syncImages($request->images);
           }

           return new ProductResource($product->load(['category', 'images']));
       }

       /**
        * Remove the specified product.
        */
       public function destroy(Product $product)
       {
           $product->delete();

           return response()->noContent();
       }
   }
   ```

   ### Form Request Example
   ```php
   <?php

   namespace App\Http\Requests;

   use Illuminate\Foundation\Http\FormRequest;

   class StoreProductRequest extends FormRequest
   {
       public function authorize(): bool
       {
           return $this->user()->can('create', Product::class);
       }

       public function rules(): array
       {
           return [
               'name' => ['required', 'string', 'max:255'],
               'slug' => ['required', 'string', 'max:255', 'unique:products'],
               'description' => ['required', 'string'],
               'price' => ['required', 'numeric', 'min:0'],
               'cost' => ['nullable', 'numeric', 'min:0'],
               'stock' => ['required', 'integer', 'min:0'],
               'category_id' => ['required', 'exists:categories,id'],
               'images' => ['nullable', 'array'],
               'images.*' => ['required', 'image', 'max:2048'],
           ];
       }

       public function messages(): array
       {
           return [
               'name.required' => 'Product name is required.',
               'price.min' => 'Price must be a positive number.',
               'images.*.image' => 'Each file must be an image.',
           ];
       }
   }
   ```

   ### API Routes Example
   ```php
   <?php

   use App\Http\Controllers\Api\V1\ProductController;
   use Illuminate\Support\Facades\Route;

   // Public routes
   Route::prefix('v1')->name('api.v1.')->group(function () {
       // Public product listing
       Route::get('products', [ProductController::class, 'index'])
           ->name('products.index');

       Route::get('products/{product}', [ProductController::class, 'show'])
           ->name('products.show');
   });

   // Protected routes
   Route::prefix('v1')->name('api.v1.')
       ->middleware(['auth:sanctum', 'throttle:60,1'])
       ->group(function () {
           Route::apiResource('products', ProductController::class)
               ->except(['index', 'show']);
       });
   ```

4. **Authentication Setup**

   ### Sanctum Installation
   ```bash
   composer require laravel/sanctum
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan migrate
   ```

   ### Token Generation
   ```php
   // In LoginController or similar
   public function login(Request $request)
   {
       $credentials = $request->validate([
           'email' => 'required|email',
           'password' => 'required',
       ]);

       if (!Auth::attempt($credentials)) {
           return response()->json([
               'message' => 'Invalid credentials'
           ], 401);
       }

       $user = Auth::user();
       $token = $user->createToken('api-token')->plainTextToken;

       return response()->json([
           'token' => $token,
           'user' => new UserResource($user),
       ]);
   }
   ```

5. **Rate Limiting**

   Configure in `app/Providers/RouteServiceProvider.php`:
   ```php
   protected function configureRateLimiting()
   {
       RateLimiter::for('api', function (Request $request) {
           return Limit::perMinute(60)->by(
               $request->user()?->id ?: $request->ip()
           );
       });
   }
   ```

6. **Error Handling**

   Create consistent API error responses:
   ```php
   // In app/Exceptions/Handler.php
   public function render($request, Throwable $exception)
   {
       if ($request->is('api/*')) {
           if ($exception instanceof ModelNotFoundException) {
               return response()->json([
                   'message' => 'Resource not found',
               ], 404);
           }

           if ($exception instanceof ValidationException) {
               return response()->json([
                   'message' => 'Validation failed',
                   'errors' => $exception->errors(),
               ], 422);
           }

           if ($exception instanceof AuthorizationException) {
               return response()->json([
                   'message' => 'Unauthorized action',
               ], 403);
           }

           return response()->json([
               'message' => 'Server error',
               'error' => config('app.debug') ? $exception->getMessage() : null,
           ], 500);
       }

       return parent::render($request, $exception);
   }
   ```

7. **API Documentation**

   Generate OpenAPI documentation:
   ```yaml
   openapi: 3.0.0
   info:
     title: {App} API
     version: 1.0.0
     description: RESTful API for {App}

   servers:
     - url: https://api.example.com/v1
       description: Production
     - url: http://localhost:8000/api/v1
       description: Development

   paths:
     /products:
       get:
         summary: List all products
         parameters:
           - name: page
             in: query
             schema:
               type: integer
           - name: per_page
             in: query
             schema:
               type: integer
           - name: search
             in: query
             schema:
               type: string
         responses:
           200:
             description: Successful response
             content:
               application/json:
                 schema:
                   $ref: '#/components/schemas/ProductCollection'
   ```

8. **Testing**

   Generate API tests:
   ```php
   <?php

   namespace Tests\Feature\Api;

   use App\Models\Product;
   use App\Models\User;
   use Illuminate\Foundation\Testing\RefreshDatabase;
   use Laravel\Sanctum\Sanctum;
   use Tests\TestCase;

   class ProductApiTest extends TestCase
   {
       use RefreshDatabase;

       public function test_can_list_products()
       {
           Product::factory()->count(3)->create();

           $response = $this->getJson('/api/v1/products');

           $response->assertOk()
               ->assertJsonStructure([
                   'data' => [
                       '*' => ['id', 'name', 'price', 'created_at']
                   ],
                   'links',
                   'meta',
               ]);
       }

       public function test_can_create_product()
       {
           Sanctum::actingAs(User::factory()->create());

           $data = [
               'name' => 'Test Product',
               'slug' => 'test-product',
               'description' => 'Description',
               'price' => 99.99,
               'stock' => 10,
               'category_id' => Category::factory()->create()->id,
           ];

           $response = $this->postJson('/api/v1/products', $data);

           $response->assertCreated()
               ->assertJsonFragment(['name' => 'Test Product']);
       }

       public function test_cannot_create_product_without_auth()
       {
           $response = $this->postJson('/api/v1/products', []);

           $response->assertUnauthorized();
       }
   }
   ```

9. **Best Practices to Apply**

   - ✅ Use API Resources for data transformation
   - ✅ Implement proper authentication (Sanctum)
   - ✅ Add authorization checks (Policies)
   - ✅ Validate all inputs (Form Requests)
   - ✅ Use API versioning (v1, v2)
   - ✅ Implement rate limiting
   - ✅ Return consistent error responses
   - ✅ Add pagination to collections
   - ✅ Support filtering and sorting
   - ✅ Include HATEOAS links
   - ✅ Write API tests
   - ✅ Document with OpenAPI/Swagger
   - ✅ Use proper HTTP status codes
   - ✅ Implement CORS if needed

## Checklist

After generating the API, verify:

- [ ] All endpoints work correctly
- [ ] Authentication is enforced where needed
- [ ] Authorization checks are in place
- [ ] Validation prevents invalid data
- [ ] Error responses are consistent
- [ ] API is documented
- [ ] Tests are passing
- [ ] Rate limiting is configured
- [ ] CORS is configured (if needed)
- [ ] API versioning strategy is clear

## Start

Ask the user:
1. Which model do you want to create an API for?
2. What operations are needed? (CRUD or specific actions)
3. Should it be authenticated? (Sanctum recommended)
4. Any special requirements? (filtering, sorting, search)

Then proceed to generate all necessary components.
