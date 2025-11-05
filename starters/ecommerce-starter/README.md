# E-commerce Starter Kit

A complete e-commerce solution built with the TALL Stack. Perfect for online stores, marketplaces, and booking systems.

## ✨ Features

- 🛍️ **Product Catalog**: Products with variants, images, and inventory
- 🛒 **Shopping Cart**: Session-based and persistent carts
- 💳 **Checkout**: Multi-step checkout with Stripe integration
- 📦 **Order Management**: Order tracking and fulfillment
- 👤 **Customer Accounts**: Order history and saved addresses
- ⭐ **Product Reviews**: Customer reviews with ratings
- 🎁 **Discount Codes**: Percentage and fixed amount discounts
- 📧 **Email Notifications**: Order confirmations and shipping updates
- 📊 **Admin Dashboard**: Sales analytics and inventory management
- 🔍 **Product Search**: Full-text search with filters

## 🚀 Quick Start

```bash
# Copy starter files
cp -r starters/ecommerce-starter/* your-project/

# Install dependencies
composer install
npm install

# Setup Stripe
# Add to .env:
STRIPE_KEY=your_publishable_key
STRIPE_SECRET=your_secret_key

# Setup database
php artisan migrate:fresh --seed

# Start servers
npm run dev
php artisan serve
```

## 📦 Sample Data

After seeding:
- 50+ products across categories
- Product variants (sizes, colors)
- Sample orders
- Customer reviews

**Admin Login:**
- Email: `admin@store.com`
- Password: `password`

**Customer Login:**
- Email: `customer@example.com`
- Password: `password`

## 🗄️ Database Schema

### Products
```
- id, name, slug, description
- price, compare_at_price
- sku, barcode
- stock_quantity, low_stock_threshold
- is_active, is_featured
- category_id, brand_id
- timestamps, soft_deletes
```

### Product Variants
```
- id, product_id
- name (e.g., "Large / Red")
- sku, price_adjustment
- stock_quantity
- options (JSON: {size: 'L', color: 'Red'})
```

### Orders
```
- id, user_id, order_number
- subtotal, tax, shipping, total
- discount_amount, discount_code
- status (pending, processing, shipped, delivered)
- shipping_address (JSON)
- billing_address (JSON)
- payment_intent_id (Stripe)
- timestamps
```

### Order Items
```
- id, order_id, product_id, variant_id
- name, sku, quantity, price
- timestamps
```

### Product Reviews
```
- id, product_id, user_id
- rating (1-5), title, content
- is_verified_purchase, is_approved
- helpful_count
- timestamps
```

### Discount Codes
```
- id, code, type (percentage/fixed)
- value, min_order_amount
- usage_limit, times_used
- starts_at, expires_at
- timestamps
```

## 🎨 Key Components

- `ProductGrid` - Product listing with filters
- `ProductDetail` - Product page with variants
- `ShoppingCart` - Cart management
- `Checkout` - Multi-step checkout process
- `OrderTracking` - Track order status
- `ProductReviews` - Review system
- `AdminOrders` - Order management dashboard
- `InventoryManager` - Stock management

## 💳 Payment Integration

### Stripe Checkout
```php
// Automatically integrated
$order->createStripePaymentIntent();
```

### Webhook Handler
```php
// routes/api.php
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

// Handles:
- payment_intent.succeeded
- payment_intent.failed
- charge.refunded
```

## 🔒 Security

- ✅ Secure checkout process
- ✅ PCI compliance (via Stripe)
- ✅ Order authorization checks
- ✅ Inventory validation
- ✅ Fraud detection (Stripe Radar)

## 🧪 Testing

```bash
php artisan test --filter=EcommerceTest
```

Tests include:
- Product catalog
- Cart operations
- Checkout process
- Order fulfillment
- Payment processing (mocked)
- Discount code validation

## 💡 Customization

### Configure Shipping
```php
// config/shop.php
'shipping' => [
    'flat_rate' => 5.99,
    'free_shipping_threshold' => 50.00,
    'rates' => [
        'standard' => ['price' => 5.99, 'days' => '3-5'],
        'express' => ['price' => 12.99, 'days' => '1-2'],
    ],
],
```

### Tax Calculation
```php
// config/shop.php
'tax' => [
    'enabled' => true,
    'rate' => 0.08, // 8%
    'included_in_price' => false,
],
```

### Low Stock Alerts
```php
// Automatically sends notifications
$product->checkLowStock(); // Notifies admin if below threshold
```

## 📚 Usage Examples

### Add to Cart
```php
Cart::add([
    'product_id' => 1,
    'variant_id' => 2,
    'quantity' => 1,
]);
```

### Apply Discount
```php
$discount = DiscountCode::findByCode('SAVE10');
$cart->applyDiscount($discount);
```

### Create Order
```php
$order = Order::createFromCart($cart, [
    'shipping_address' => [...],
    'billing_address' => [...],
    'payment_method' => 'card',
]);
```

### Process Payment
```php
$payment = $order->charge($paymentMethodId);

if ($payment->successful()) {
    $order->markAsPaid();
    $order->sendConfirmationEmail();
}
```

## 📊 Admin Features

- **Dashboard**: Sales metrics, revenue charts
- **Orders**: View, filter, and fulfill orders
- **Products**: Manage catalog and inventory
- **Customers**: View customer data and order history
- **Reports**: Sales reports, best sellers, low stock alerts

## 🚀 Going Live

### Required Configuration
```env
# Stripe (production)
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Mail
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS=orders@yourstore.com

# Queue (for emails)
QUEUE_CONNECTION=redis
```

### Launch Checklist
- [ ] Test checkout flow
- [ ] Configure shipping rates
- [ ] Set up tax calculation
- [ ] Test payment processing
- [ ] Configure email templates
- [ ] Enable Stripe webhooks
- [ ] Set up inventory alerts
- [ ] Configure backup system
- [ ] Test refund process
- [ ] Enable SSL certificate

See `/tall-deploy` for complete deployment guide.

---

**Happy Selling! 🛍️**
