# TALL Stack Starter Kits

Pre-configured starter templates for common TALL Stack application types. Each starter includes a complete setup with models, migrations, Livewire components, and best practices baked in.

## 🚀 Available Starters

### 1. SaaS Starter (`saas-starter/`)
Complete SaaS application foundation with:
- Multi-tenancy support
- Team/Organization management
- Subscription billing (Stripe/Paddle)
- Role-based permissions
- User invitations
- API tokens
- Activity log
- Settings management

**Perfect for:** SaaS products, B2B platforms, team collaboration tools

### 2. Blog Starter (`blog-starter/`)
Full-featured blogging platform with:
- Post management (CRUD)
- Categories and tags
- Author profiles
- Comments system
- SEO optimization
- RSS feeds
- Social sharing
- Search functionality

**Perfect for:** Blogs, news sites, content platforms, documentation

### 3. E-commerce Starter (`ecommerce-starter/`)
Complete e-commerce solution with:
- Product catalog
- Shopping cart
- Order management
- Payment processing
- Inventory tracking
- Customer accounts
- Product reviews
- Discount codes

**Perfect for:** Online stores, marketplaces, booking systems

### 4. Dashboard Starter (`dashboard-starter/`)
Admin dashboard and analytics platform with:
- Real-time statistics
- Charts and graphs
- Data tables
- User management
- Activity monitoring
- Export functionality
- Notifications
- Responsive layout

**Perfect for:** Admin panels, analytics dashboards, internal tools

## 📥 How to Use a Starter

### Option 1: Use with Claude Code

```bash
# Navigate to your project
cd your-laravel-project

# Ask Claude to setup a starter
I want to use the [saas-starter/blog-starter/ecommerce-starter/dashboard-starter] template
```

Claude will:
1. Copy the starter structure
2. Install dependencies
3. Run migrations
4. Set up configuration
5. Generate sample data
6. Provide setup instructions

### Option 2: Manual Installation

```bash
# Clone this repository
git clone <repo-url> tall-stack-ai

# Copy starter to your project
cp -r tall-stack-ai/starters/saas-starter/* your-laravel-project/

# Install dependencies
cd your-laravel-project
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate:fresh --seed

# Build assets
npm run dev
```

## 📝 Starter Structure

Each starter includes:

```
starter-name/
├── README.md                    # Specific starter documentation
├── app/
│   ├── Models/                  # Pre-configured models
│   ├── Livewire/                # Livewire components
│   ├── Policies/                # Authorization policies
│   └── Http/
│       └── Controllers/         # Controllers (if needed)
├── database/
│   ├── migrations/              # Database migrations
│   ├── factories/               # Model factories
│   └── seeders/                 # Database seeders
├── resources/
│   ├── views/
│   │   └── livewire/           # Livewire views
│   └── js/                      # Alpine.js components
├── routes/
│   └── web.php                  # Routes
├── tests/
│   ├── Feature/                 # Feature tests
│   └── Unit/                    # Unit tests
└── config/
    └── [starter].php            # Starter-specific config
```

## 🎨 Customization

All starters are designed to be customized. After installation:

1. **Branding**: Update colors, logos, and fonts
2. **Models**: Extend or modify models for your needs
3. **Components**: Customize Livewire components
4. **Styling**: Adjust Tailwind configuration
5. **Features**: Add or remove features as needed

## 🔒 Security

All starters include:
- ✅ CSRF protection
- ✅ XSS prevention
- ✅ SQL injection prevention
- ✅ Mass assignment protection
- ✅ Authorization policies
- ✅ Input validation
- ✅ Secure file uploads
- ✅ Rate limiting

## 🧪 Testing

Each starter includes:
- ✅ Feature tests
- ✅ Unit tests
- ✅ Livewire component tests
- ✅ Browser tests (Dusk) setup
- ✅ CI/CD configuration

Run tests:
```bash
php artisan test
# or with Pest
./vendor/bin/pest
```

## 📚 Documentation

Each starter has its own detailed README with:
- Feature overview
- Installation instructions
- Configuration guide
- Customization tips
- Common use cases
- Troubleshooting

## 🤝 Contributing

Want to contribute a starter? Follow these guidelines:

1. **Quality**: Follow TALL Stack best practices
2. **Testing**: Include comprehensive tests
3. **Documentation**: Write clear documentation
4. **Security**: Implement security best practices
5. **Styling**: Use Tailwind CSS consistently
6. **Accessibility**: Ensure components are accessible

## 📄 License

All starters are provided under MIT License.

## 🌟 Credits

These starters are maintained by the TALL Stack AI Assistant community.

---

**Need help?** Ask Claude Code:
```
Help me customize the [starter-name] for my project
```
