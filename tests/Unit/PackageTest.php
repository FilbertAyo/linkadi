<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Package;
use App\Models\PackagePricingTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_has_pricing_tiers_relationship(): void
    {
        $package = Package::factory()->classic()->create();
        PackagePricingTier::factory()->count(3)->create(['package_id' => $package->id]);

        $this->assertCount(3, $package->pricingTiers);
        $this->assertInstanceOf(PackagePricingTier::class, $package->pricingTiers->first());
    }

    public function test_package_has_active_pricing_tiers_relationship(): void
    {
        $package = Package::factory()->classic()->create();
        PackagePricingTier::factory()->create(['package_id' => $package->id, 'is_active' => true]);
        PackagePricingTier::factory()->create(['package_id' => $package->id, 'is_active' => false]);
        PackagePricingTier::factory()->create(['package_id' => $package->id, 'is_active' => true]);

        $this->assertCount(2, $package->activePricingTiers);
    }

    public function test_package_has_orders_relationship(): void
    {
        $package = Package::factory()->create();
        Order::factory()->count(2)->create(['package_id' => $package->id]);

        $this->assertCount(2, $package->orders);
        $this->assertInstanceOf(Order::class, $package->orders->first());
    }

    public function test_package_image_url_accessor_returns_null_when_no_image(): void
    {
        $package = Package::factory()->create(['image' => null]);

        $this->assertNull($package->image_url);
    }

    public function test_package_image_url_accessor_returns_correct_path(): void
    {
        $package = Package::factory()->create(['image' => 'packages/test-image.jpg']);

        $this->assertStringContainsString('storage/packages/test-image.jpg', $package->image_url);
    }

    public function test_package_get_price_for_quantity_returns_base_price_for_nfc_plain(): void
    {
        $package = Package::factory()->nfcPlain()->create(['base_price' => 29.99]);

        $this->assertEquals(29.99, $package->getPriceForQuantity(1));
        $this->assertEquals(29.99, $package->getPriceForQuantity(10)); // Same price regardless of quantity
    }

    public function test_package_get_price_for_quantity_returns_base_price_for_nfc_printed(): void
    {
        $package = Package::factory()->nfcPrinted()->create(['base_price' => 39.99]);

        $this->assertEquals(39.99, $package->getPriceForQuantity(1));
    }

    public function test_package_get_price_for_quantity_returns_null_for_nfc_without_base_price(): void
    {
        $package = Package::factory()->nfcPlain()->create(['base_price' => null]);

        $this->assertNull($package->getPriceForQuantity(1));
    }

    public function test_package_get_price_for_quantity_calculates_for_classic_with_tier(): void
    {
        $package = Package::factory()->classic()->create();
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
            'is_active' => true,
        ]);

        $this->assertEquals(125.00, $package->getPriceForQuantity(50)); // 50 * 2.50
    }

    public function test_package_get_price_for_quantity_returns_null_for_classic_without_matching_tier(): void
    {
        $package = Package::factory()->classic()->create();
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 10,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
            'is_active' => true,
        ]);

        $this->assertNull($package->getPriceForQuantity(5)); // Below min_quantity
    }

    public function test_package_get_price_for_quantity_uses_correct_tier_for_classic(): void
    {
        $package = Package::factory()->classic()->create();
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
            'is_active' => true,
        ]);
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 101,
            'max_quantity' => 500,
            'price_per_unit' => 2.00,
            'is_active' => true,
        ]);

        $this->assertEquals(250.00, $package->getPriceForQuantity(100)); // 100 * 2.50
        $this->assertEquals(400.00, $package->getPriceForQuantity(200)); // 200 * 2.00
    }

    public function test_package_get_price_for_quantity_ignores_inactive_tiers(): void
    {
        $package = Package::factory()->classic()->create();
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
            'is_active' => false, // Inactive
        ]);
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => 3.00,
            'is_active' => true,
        ]);

        $this->assertEquals(300.00, $package->getPriceForQuantity(100)); // Uses active tier
    }

    public function test_package_is_available_returns_true_for_active_nfc_with_base_price(): void
    {
        $package = Package::factory()->nfcPlain()->create([
            'is_active' => true,
            'base_price' => 29.99,
        ]);

        $this->assertTrue($package->isAvailable());
    }

    public function test_package_is_available_returns_false_for_inactive_package(): void
    {
        $package = Package::factory()->nfcPlain()->create([
            'is_active' => false,
            'base_price' => 29.99,
        ]);

        $this->assertFalse($package->isAvailable());
    }

    public function test_package_is_available_returns_false_for_nfc_without_base_price(): void
    {
        $package = Package::factory()->nfcPlain()->create([
            'is_active' => true,
            'base_price' => null,
        ]);

        $this->assertFalse($package->isAvailable());
    }

    public function test_package_is_available_returns_true_for_classic_with_active_tiers(): void
    {
        $package = Package::factory()->classic()->create(['is_active' => true]);
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'is_active' => true,
        ]);

        $this->assertTrue($package->isAvailable());
    }

    public function test_package_is_available_returns_false_for_classic_without_tiers(): void
    {
        $package = Package::factory()->classic()->create(['is_active' => true]);

        $this->assertFalse($package->isAvailable());
    }

    public function test_package_is_available_returns_false_for_classic_with_only_inactive_tiers(): void
    {
        $package = Package::factory()->classic()->create(['is_active' => true]);
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'is_active' => false,
        ]);

        $this->assertFalse($package->isAvailable());
    }

    public function test_package_generate_unique_slug_creates_slug_from_name(): void
    {
        $slug = Package::generateUniqueSlug('Test Package Name');

        $this->assertEquals('test-package-name', $slug);
    }

    public function test_package_generate_unique_slug_handles_special_characters(): void
    {
        $slug = Package::generateUniqueSlug('Test & Package (2024)');

        $this->assertEquals('test-package-2024', $slug);
    }

    public function test_package_generate_unique_slug_appends_number_if_exists(): void
    {
        Package::factory()->create(['slug' => 'test-package']);

        $slug = Package::generateUniqueSlug('Test Package');

        $this->assertEquals('test-package-1', $slug);
    }

    public function test_package_generate_unique_slug_increments_counter(): void
    {
        Package::factory()->create(['slug' => 'test-package']);
        Package::factory()->create(['slug' => 'test-package-1']);

        $slug = Package::generateUniqueSlug('Test Package');

        $this->assertEquals('test-package-2', $slug);
    }

    public function test_package_scope_active_filters_active_packages(): void
    {
        Package::factory()->create(['is_active' => true]);
        Package::factory()->create(['is_active' => true]);
        Package::factory()->create(['is_active' => false]);

        $activePackages = Package::active()->get();

        $this->assertCount(2, $activePackages);
        $this->assertTrue($activePackages->every(fn($pkg) => $pkg->is_active === true));
    }

    public function test_package_scope_ordered_orders_by_display_order_then_created_at(): void
    {
        $package1 = Package::factory()->create(['display_order' => 2, 'created_at' => now()->subDays(2)]);
        $package2 = Package::factory()->create(['display_order' => 1, 'created_at' => now()->subDays(1)]);
        $package3 = Package::factory()->create(['display_order' => 1, 'created_at' => now()]);

        $ordered = Package::ordered()->get();

        $this->assertEquals($package2->id, $ordered[0]->id); // display_order 1, older
        $this->assertEquals($package3->id, $ordered[1]->id); // display_order 1, newer
        $this->assertEquals($package1->id, $ordered[2]->id); // display_order 2
    }

    public function test_package_features_are_cast_to_array(): void
    {
        $features = ['Feature 1', 'Feature 2', 'Feature 3'];
        $package = Package::factory()->create(['features' => $features]);

        $this->assertIsArray($package->features);
        $this->assertEquals($features, $package->features);
    }

    public function test_package_base_price_is_cast_to_decimal(): void
    {
        $package = Package::factory()->create(['base_price' => '29.99']);

        $this->assertIsFloat($package->base_price);
        $this->assertEquals(29.99, $package->base_price);
    }

    public function test_package_is_active_is_cast_to_boolean(): void
    {
        $package1 = Package::factory()->create(['is_active' => 1]);
        $package2 = Package::factory()->create(['is_active' => 0]);

        $this->assertTrue($package1->is_active);
        $this->assertFalse($package2->is_active);
    }
}

