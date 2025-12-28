# Dynamic Package System Implementation Plan

## Overview
This document outlines the implementation plan for a dynamic package management system where admins can create and manage NFC card packages with flexible pricing tiers, quantity ranges, and custom images. Packages will be displayed on both the landing page and user dashboard.

## Requirements Summary
- **Dynamic Packages**: Admin-manageable, not hardcoded
- **NFC Card Types**: 
  - Plain cards (with pricing)
  - Printed cards (with different pricing)
- **Classic Cards**: Quantity-based pricing with ranges (e.g., 1-100 cards at $X, 101-500 at $Y, 501+ at $Z)
- **Admin Features**:
  - Create/edit/delete packages
  - Set pricing for different card types
  - Configure quantity ranges and pricing tiers
  - Upload package images
  - Enable/disable packages
  - Set package display order
- **User Features**:
  - View packages on landing page (pricing section)
  - View packages in dashboard
  - Select and order packages

---

## Phase 1: Database Schema Design

### 1.1 Create `packages` Table
**Migration**: `create_packages_table.php`

**Fields**:
- `id` (bigint, primary key)
- `name` (string) - Package name (e.g., "NFC Starter Pack", "Classic Cards Bundle")
- `slug` (string, unique) - URL-friendly identifier
- `description` (text) - Package description
- `type` (enum: 'nfc_plain', 'nfc_printed', 'classic') - Package type
- `image` (string, nullable) - Package image path
- `is_active` (boolean, default: true) - Enable/disable package
- `display_order` (integer, default: 0) - Order for display
- `features` (json, nullable) - Array of features (e.g., ["Free shipping", "QR code included"])
- `created_at`, `updated_at` (timestamps)

### 1.2 Create `package_pricing_tiers` Table
**Migration**: `create_package_pricing_tiers_table.php`

**Fields**:
- `id` (bigint, primary key)
- `package_id` (foreign key to packages)
- `min_quantity` (integer) - Minimum quantity for this tier (e.g., 1, 101)
- `max_quantity` (integer, nullable) - Maximum quantity (null = unlimited)
- `price_per_unit` (decimal 10,2) - Price per card/unit
- `total_price` (decimal 10,2, nullable) - Fixed total price (if applicable)
- `is_active` (boolean, default: true)
- `created_at`, `updated_at` (timestamps)

**Indexes**:
- Index on `package_id`
- Index on `min_quantity`, `max_quantity` for range queries

### 1.3 Create `orders` Table (for future order management)
**Migration**: `create_orders_table.php`

**Fields**:
- `id` (bigint, primary key)
- `user_id` (foreign key to users)
- `package_id` (foreign key to packages)
- `quantity` (integer) - Number of cards ordered
- `unit_price` (decimal 10,2) - Price per unit at time of order
- `total_price` (decimal 10,2) - Total order amount
- `status` (enum: 'pending', 'processing', 'shipped', 'delivered', 'cancelled')
- `shipping_address` (text, nullable)
- `notes` (text, nullable)
- `created_at`, `updated_at` (timestamps)

**Indexes**:
- Index on `user_id`
- Index on `package_id`
- Index on `status`

---

## Phase 2: Models and Relationships

### 2.1 Create `Package` Model
**File**: `app/Models/Package.php`

**Relationships**:
- `hasMany(PackagePricingTier::class)` - Pricing tiers
- `hasMany(Order::class)` - Orders for this package

**Methods**:
- `getImageUrlAttribute()` - Get full image URL
- `getPriceForQuantity(int $quantity)` - Calculate price for given quantity
- `getActiveTiers()` - Get active pricing tiers
- `isAvailable()` - Check if package is active and has pricing

**Casts**:
- `is_active` → boolean
- `features` → array
- `display_order` → integer

### 2.2 Create `PackagePricingTier` Model
**File**: `app/Models/PackagePricingTier.php`

**Relationships**:
- `belongsTo(Package::class)`

**Methods**:
- `matchesQuantity(int $quantity)` - Check if quantity falls in this tier
- `calculatePrice(int $quantity)` - Calculate price for quantity

**Scopes**:
- `active()` - Only active tiers
- `forQuantity(int $quantity)` - Find tier matching quantity

### 2.3 Create `Order` Model
**File**: `app/Models/Order.php`

**Relationships**:
- `belongsTo(User::class)`
- `belongsTo(Package::class)`

**Methods**:
- Status helpers: `isPending()`, `isProcessing()`, etc.

### 2.4 Update `User` Model
**File**: `app/Models/User.php`

**Add Relationship**:
- `hasMany(Order::class)`

---

## Phase 3: Admin Interface - Package Management

### 3.1 Admin Routes
**File**: `routes/web.php`

```php
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // ... existing routes
    
    Route::resource('packages', App\Http\Controllers\Admin\PackageController::class);
    Route::post('packages/{package}/pricing-tiers', [App\Http\Controllers\Admin\PackageController::class, 'storePricingTier'])
        ->name('packages.pricing-tiers.store');
    Route::delete('packages/{package}/pricing-tiers/{tier}', [App\Http\Controllers\Admin\PackageController::class, 'destroyPricingTier'])
        ->name('packages.pricing-tiers.destroy');
});
```

### 3.2 Admin Controller
**File**: `app/Http/Controllers/Admin/PackageController.php`

**Methods**:
- `index()` - List all packages
- `create()` - Show create form
- `store(Request $request)` - Store new package
- `show(Package $package)` - Show package details
- `edit(Package $package)` - Show edit form
- `update(Request $request, Package $package)` - Update package
- `destroy(Package $package)` - Delete package
- `storePricingTier(Request $request, Package $package)` - Add pricing tier
- `destroyPricingTier(Package $package, PackagePricingTier $tier)` - Remove pricing tier
- `toggleActive(Package $package)` - Toggle package active status
- `reorder(Request $request)` - Update display order

**Validation Rules**:
- Package name: required, string, max:255
- Slug: required, unique, regex for URL-safe
- Type: required, in:nfc_plain,nfc_printed,classic
- Image: nullable, image, max:2048
- Pricing tiers: required for classic type, array with min_quantity, max_quantity, price_per_unit

### 3.3 Admin Views

#### 3.3.1 Package List View
**File**: `resources/views/admin/packages/index.blade.php`

**Features**:
- Table listing all packages
- Columns: Name, Type, Status, Display Order, Actions
- Search and filter by type/status
- Quick toggle active/inactive
- Drag-and-drop or manual reordering
- "Add New Package" button

#### 3.3.2 Package Create/Edit Form
**File**: `resources/views/admin/packages/form.blade.php` (partial)
**File**: `resources/views/admin/packages/create.blade.php`
**File**: `resources/views/admin/packages/edit.blade.php`

**Form Fields**:
- Package Name (text input)
- Slug (text input, auto-generate from name)
- Description (textarea)
- Type (select: NFC Plain, NFC Printed, Classic)
- Image Upload (file input with preview)
- Features (dynamic list - add/remove features)
- Active Status (toggle/checkbox)
- Display Order (number input)

**Pricing Tiers Section** (for Classic type):
- Dynamic form to add/remove tiers
- Fields per tier: Min Quantity, Max Quantity, Price Per Unit
- Validation: No overlapping ranges, min < max
- Visual representation of tiers

#### 3.3.3 Package Show View
**File**: `resources/views/admin/packages/show.blade.php`

**Features**:
- Package details display
- Pricing tiers table (for classic)
- Image preview
- Order history (if orders exist)
- Edit/Delete actions

### 3.4 Update Admin Navigation
**File**: `resources/views/layouts/admin.blade.php`

**Add Menu Item**:
- "Packages" link in sidebar navigation

---

## Phase 4: Public Package Display

### 4.1 Landing Page - Pricing Section
**File**: `resources/views/welcome.blade.php`

**Update**: Replace or enhance existing `#pricing` section

**Features**:
- Display active packages grouped by type
- Show package cards with:
  - Package image
  - Name and description
  - Features list
  - Pricing (for NFC types: single price; for Classic: "Starting from $X" or price range)
  - "Select Package" or "Order Now" button
- Responsive grid layout (3 columns on desktop, 1 on mobile)
- Filter tabs: "All", "NFC Cards", "Classic Cards"

**Controller Method**:
- Create `WelcomeController` or add method to existing controller
- Fetch active packages ordered by `display_order`

### 4.2 User Dashboard - Package Selection
**File**: `resources/views/dashboard.blade.php` or new view

**Features**:
- Section showing available packages
- Similar card layout as landing page
- "Order Package" button (links to order form)
- Show user's previous orders (if any)

**Alternative**: Create dedicated packages page
- Route: `/packages` or `/dashboard/packages`
- Full package listing with detailed information

### 4.3 Package Detail Page (Optional)
**File**: `resources/views/packages/show.blade.php`

**Route**: `/packages/{slug}`

**Features**:
- Full package details
- Image gallery
- Detailed pricing (with quantity calculator for classic)
- Features comparison
- Order form/button

---

## Phase 5: Package Selection & Ordering (Basic Implementation)

### 5.1 Order Form Component
**File**: `resources/views/components/package-order-form.blade.php` or Livewire component

**Features**:
- Package selection dropdown/radio
- Quantity input (with validation based on package type)
- For Classic packages: Show pricing tier based on quantity
- Price calculation (real-time)
- Shipping address form
- Notes/instructions field
- Submit order button

### 5.2 Order Controller
**File**: `app/Http/Controllers/OrderController.php`

**Methods**:
- `store(Request $request)` - Create new order
- `index()` - List user's orders
- `show(Order $order)` - Show order details

**Validation**:
- Package must exist and be active
- Quantity must be valid (>= 1, within tier ranges if applicable)
- Shipping address required for physical products

### 5.3 Order Routes
**File**: `routes/web.php`

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/{package:slug}', [PackageController::class, 'show'])->name('packages.show');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});
```

---

## Phase 6: Advanced Features

### 6.1 Quantity Calculator (for Classic Packages)
**JavaScript Component** or **Livewire Component**

**Features**:
- User inputs quantity
- Automatically calculates price based on matching tier
- Shows breakdown: "X cards × $Y = $Z"
- Updates in real-time

### 6.2 Package Comparison
**Optional Feature**

**File**: `resources/views/packages/compare.blade.php`

**Features**:
- Side-by-side comparison of selected packages
- Compare: Price, Features, Card Type, etc.

### 6.3 Package Images Management
**Enhancement**

- Multiple images per package (gallery)
- Image ordering
- Thumbnail generation

**Database**: Add `package_images` table or use JSON field

---

## Phase 7: API Endpoints (Optional - for future mobile app)

### 7.1 Package API
**File**: `routes/api.php`

```php
Route::get('/packages', [Api\PackageController::class, 'index']);
Route::get('/packages/{package}', [Api\PackageController::class, 'show']);
Route::post('/orders', [Api\OrderController::class, 'store'])->middleware('auth:sanctum');
```

---

## Phase 8: Testing

### 8.1 Feature Tests
**Directory**: `tests/Feature/`

**Test Files**:
- `PackageManagementTest.php` - Admin package CRUD
- `PackagePricingTest.php` - Pricing tier calculations
- `PackageDisplayTest.php` - Public package display
- `OrderTest.php` - Order creation and management

**Test Cases**:
- Admin can create/edit/delete packages
- Pricing tiers calculate correctly
- Packages display on landing page
- Users can view packages
- Orders are created correctly
- Quantity validation works

### 8.2 Unit Tests
**Directory**: `tests/Unit/`

**Test Files**:
- `PackageTest.php` - Model methods
- `PackagePricingTierTest.php` - Tier matching logic

---

## Implementation Order

### Sprint 1: Foundation
1. ✅ Create database migrations (packages, package_pricing_tiers, orders)
2. ✅ Create models with relationships
3. ✅ Create seeders for sample packages

### Sprint 2: Admin Interface
4. ✅ Create admin routes and controller
5. ✅ Build admin package list view
6. ✅ Build admin package create/edit forms
7. ✅ Implement image upload functionality
8. ✅ Add pricing tier management (for classic packages)
9. ✅ Add package activation/deactivation
10. ✅ Add display order management

### Sprint 3: Public Display
11. ✅ Update landing page with dynamic packages
12. ✅ Add packages section to user dashboard
13. ✅ Create package detail page (optional)
14. ✅ Style package cards consistently

### Sprint 4: Ordering System
15. ✅ Create order form component
16. ✅ Implement order controller and routes
17. ✅ Add order validation
18. ✅ Create order confirmation/thank you page
19. ✅ Add user order history

### Sprint 5: Enhancements
20. ✅ Add quantity calculator for classic packages
21. ✅ Improve package images (multiple images, gallery)
22. ✅ Add package search/filter on landing page
23. ✅ Add package comparison feature (optional)

### Sprint 6: Testing & Polish
24. ✅ Write feature tests
25. ✅ Write unit tests
26. ✅ Fix bugs and edge cases
27. ✅ Performance optimization
28. ✅ Documentation

---

## Database Seeder Example

Create `database/seeders/PackageSeeder.php` with sample data:

```php
- NFC Plain Card Package
  - Single price: $29.99
  - Features: ["Free shipping", "QR code included", "Lifetime profile updates"]

- NFC Printed Card Package  
  - Single price: $39.99
  - Features: ["Custom design", "Free shipping", "QR code included"]

- Classic Cards Package
  - Tier 1: 1-100 cards @ $2.50 per card
  - Tier 2: 101-500 cards @ $2.00 per card
  - Tier 3: 501+ cards @ $1.75 per card
  - Features: ["Bulk pricing", "Free shipping on 100+", "Custom printing available"]
```

---

## Technical Considerations

### Image Storage
- Store images in `storage/app/public/packages/`
- Use Laravel's filesystem for uploads
- Generate thumbnails if needed (using Intervention Image or similar)

### Pricing Calculation
- For NFC types: Simple fixed price
- For Classic: Query pricing tiers, find matching tier, calculate: `quantity × price_per_unit`

### Display Order
- Use `display_order` field for manual ordering
- Admin can drag-and-drop or use up/down arrows
- Default: order by creation date

### Package Types
- `nfc_plain`: Single price, no quantity tiers
- `nfc_printed`: Single price, no quantity tiers  
- `classic`: Requires pricing tiers, quantity-based

### Validation Rules
- Ensure pricing tiers don't overlap
- Ensure min_quantity < max_quantity (or max is null)
- Ensure at least one tier for classic packages
- Ensure image is valid and within size limits

---

## Future Enhancements (Post-MVP)

1. **Payment Integration**: Stripe/PayPal integration for order payment
2. **Inventory Management**: Track card stock levels
3. **Shipping Integration**: Calculate shipping costs, tracking
4. **Discount Codes**: Coupon/promotion code system
5. **Package Variants**: Different card designs within same package
6. **Subscription Packages**: Recurring orders
7. **Analytics**: Track package views, conversion rates
8. **Email Notifications**: Order confirmations, shipping updates
9. **Admin Dashboard**: Package performance metrics
10. **Multi-currency**: Support different currencies

---

## Notes

- Use Laravel's built-in validation and authorization
- Follow existing code patterns (Livewire/Blade components)
- Maintain consistent UI with existing admin and public pages
- Ensure mobile responsiveness
- Add proper error handling and user feedback
- Consider caching package data for performance
- Add audit logging for admin package changes

