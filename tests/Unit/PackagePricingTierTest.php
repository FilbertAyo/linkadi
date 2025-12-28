<?php

namespace Tests\Unit;

use App\Models\Package;
use App\Models\PackagePricingTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackagePricingTierTest extends TestCase
{
    use RefreshDatabase;

    public function test_pricing_tier_has_package_relationship(): void
    {
        $package = Package::factory()->create();
        $tier = PackagePricingTier::factory()->create(['package_id' => $package->id]);

        $this->assertInstanceOf(Package::class, $tier->package);
        $this->assertEquals($package->id, $tier->package->id);
    }

    public function test_pricing_tier_matches_quantity_within_range(): void
    {
        $tier = PackagePricingTier::factory()->create([
            'min_quantity' => 10,
            'max_quantity' => 100,
        ]);

        $this->assertFalse($tier->matchesQuantity(5)); // Below min
        $this->assertTrue($tier->matchesQuantity(10)); // At min
        $this->assertTrue($tier->matchesQuantity(50)); // In range
        $this->assertTrue($tier->matchesQuantity(100)); // At max
        $this->assertFalse($tier->matchesQuantity(101)); // Above max
    }

    public function test_pricing_tier_matches_quantity_with_unlimited_max(): void
    {
        $tier = PackagePricingTier::factory()->unlimited()->create([
            'min_quantity' => 100,
        ]);

        $this->assertFalse($tier->matchesQuantity(99)); // Below min
        $this->assertTrue($tier->matchesQuantity(100)); // At min
        $this->assertTrue($tier->matchesQuantity(500)); // Above min
        $this->assertTrue($tier->matchesQuantity(10000)); // Way above min
    }

    public function test_pricing_tier_calculate_price_uses_price_per_unit(): void
    {
        $tier = PackagePricingTier::factory()->create([
            'price_per_unit' => 2.50,
            'total_price' => null,
        ]);

        $this->assertEquals(250.00, $tier->calculatePrice(100)); // 100 * 2.50
        $this->assertEquals(125.00, $tier->calculatePrice(50)); // 50 * 2.50
    }

    public function test_pricing_tier_calculate_price_uses_total_price_when_set(): void
    {
        $tier = PackagePricingTier::factory()->create([
            'price_per_unit' => 2.50,
            'total_price' => 500.00,
        ]);

        // Should return total_price regardless of quantity
        $this->assertEquals(500.00, $tier->calculatePrice(100));
        $this->assertEquals(500.00, $tier->calculatePrice(200));
    }

    public function test_pricing_tier_scope_active_filters_active_tiers(): void
    {
        $package = Package::factory()->create();
        PackagePricingTier::factory()->create(['package_id' => $package->id, 'is_active' => true]);
        PackagePricingTier::factory()->create(['package_id' => $package->id, 'is_active' => false]);
        PackagePricingTier::factory()->create(['package_id' => $package->id, 'is_active' => true]);

        $activeTiers = PackagePricingTier::active()->get();

        $this->assertCount(2, $activeTiers);
        $this->assertTrue($activeTiers->every(fn($tier) => $tier->is_active === true));
    }

    public function test_pricing_tier_scope_for_quantity_finds_matching_tier(): void
    {
        $package = Package::factory()->create();
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
        ]);
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 101,
            'max_quantity' => 500,
        ]);

        $tier = PackagePricingTier::forQuantity(50)->first();
        $this->assertNotNull($tier);
        $this->assertEquals(1, $tier->min_quantity);
        $this->assertEquals(100, $tier->max_quantity);

        $tier2 = PackagePricingTier::forQuantity(200)->first();
        $this->assertNotNull($tier2);
        $this->assertEquals(101, $tier2->min_quantity);
        $this->assertEquals(500, $tier2->max_quantity);
    }

    public function test_pricing_tier_scope_for_quantity_finds_unlimited_tier(): void
    {
        $package = Package::factory()->create();
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
        ]);
        
        PackagePricingTier::factory()->unlimited()->create([
            'package_id' => $package->id,
            'min_quantity' => 501,
        ]);

        $tier = PackagePricingTier::forQuantity(1000)->first();
        $this->assertNotNull($tier);
        $this->assertEquals(501, $tier->min_quantity);
        $this->assertNull($tier->max_quantity);
    }

    public function test_pricing_tier_min_quantity_is_cast_to_integer(): void
    {
        $tier = PackagePricingTier::factory()->create(['min_quantity' => '10']);

        $this->assertIsInt($tier->min_quantity);
        $this->assertEquals(10, $tier->min_quantity);
    }

    public function test_pricing_tier_max_quantity_can_be_null(): void
    {
        $tier = PackagePricingTier::factory()->unlimited()->create();

        $this->assertNull($tier->max_quantity);
    }

    public function test_pricing_tier_price_per_unit_is_cast_to_decimal(): void
    {
        $tier = PackagePricingTier::factory()->create(['price_per_unit' => '2.50']);

        $this->assertIsFloat($tier->price_per_unit);
        $this->assertEquals(2.50, $tier->price_per_unit);
    }

    public function test_pricing_tier_is_active_is_cast_to_boolean(): void
    {
        $tier1 = PackagePricingTier::factory()->create(['is_active' => 1]);
        $tier2 = PackagePricingTier::factory()->create(['is_active' => 0]);

        $this->assertTrue($tier1->is_active);
        $this->assertFalse($tier2->is_active);
    }

    public function test_pricing_tier_calculate_price_handles_zero_quantity(): void
    {
        $tier = PackagePricingTier::factory()->create([
            'price_per_unit' => 2.50,
        ]);

        $this->assertEquals(0.00, $tier->calculatePrice(0));
    }

    public function test_pricing_tier_calculate_price_handles_large_quantities(): void
    {
        $tier = PackagePricingTier::factory()->create([
            'price_per_unit' => 1.50,
        ]);

        $price = $tier->calculatePrice(10000);
        $this->assertEquals(15000.00, $price);
    }
}

