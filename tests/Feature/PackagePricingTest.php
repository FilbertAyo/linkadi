<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackagePricingTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackagePricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_nfc_plain_package_returns_base_price(): void
    {
        $package = Package::factory()->nfcPlain()->create([
            'base_price' => 29.99,
        ]);

        $price = $package->getPriceForQuantity(1);
        
        $this->assertEquals(29.99, $price);
    }

    public function test_nfc_printed_package_returns_base_price(): void
    {
        $package = Package::factory()->nfcPrinted()->create([
            'base_price' => 39.99,
        ]);

        $price = $package->getPriceForQuantity(1);
        
        $this->assertEquals(39.99, $price);
    }

    public function test_classic_package_calculates_price_for_quantity_in_first_tier(): void
    {
        $package = Package::factory()->classic()->create();
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
        ]);

        $price = $package->getPriceForQuantity(50);
        
        $this->assertEquals(125.00, $price); // 50 * 2.50
    }

    public function test_classic_package_calculates_price_for_quantity_in_second_tier(): void
    {
        $package = Package::factory()->classic()->create();
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
        ]);
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 101,
            'max_quantity' => 500,
            'price_per_unit' => 2.00,
        ]);

        $price = $package->getPriceForQuantity(200);
        
        $this->assertEquals(400.00, $price); // 200 * 2.00
    }

    public function test_classic_package_calculates_price_for_unlimited_tier(): void
    {
        $package = Package::factory()->classic()->create();
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
        ]);
        
        PackagePricingTier::factory()->unlimited()->create([
            'package_id' => $package->id,
            'min_quantity' => 501,
            'price_per_unit' => 1.75,
        ]);

        $price = $package->getPriceForQuantity(1000);
        
        $this->assertEquals(1750.00, $price); // 1000 * 1.75
    }

    public function test_classic_package_returns_null_for_quantity_without_matching_tier(): void
    {
        $package = Package::factory()->classic()->create();
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 10,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
        ]);

        $price = $package->getPriceForQuantity(5); // Below min_quantity
        
        $this->assertNull($price);
    }

    public function test_package_is_available_when_active_and_has_pricing(): void
    {
        $nfcPackage = Package::factory()->nfcPlain()->create([
            'is_active' => true,
            'base_price' => 29.99,
        ]);

        $this->assertTrue($nfcPackage->isAvailable());
    }

    public function test_package_is_not_available_when_inactive(): void
    {
        $package = Package::factory()->nfcPlain()->create([
            'is_active' => false,
            'base_price' => 29.99,
        ]);

        $this->assertFalse($package->isAvailable());
    }

    public function test_nfc_package_is_not_available_without_base_price(): void
    {
        $package = Package::factory()->nfcPlain()->create([
            'is_active' => true,
            'base_price' => null,
        ]);

        $this->assertFalse($package->isAvailable());
    }

    public function test_classic_package_is_available_with_active_tiers(): void
    {
        $package = Package::factory()->classic()->create(['is_active' => true]);
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'is_active' => true,
        ]);

        $this->assertTrue($package->isAvailable());
    }

    public function test_classic_package_is_not_available_without_tiers(): void
    {
        $package = Package::factory()->classic()->create(['is_active' => true]);

        $this->assertFalse($package->isAvailable());
    }

    public function test_pricing_tier_matches_quantity_correctly(): void
    {
        $tier = PackagePricingTier::factory()->create([
            'min_quantity' => 10,
            'max_quantity' => 100,
        ]);

        $this->assertFalse($tier->matchesQuantity(5));
        $this->assertTrue($tier->matchesQuantity(10));
        $this->assertTrue($tier->matchesQuantity(50));
        $this->assertTrue($tier->matchesQuantity(100));
        $this->assertFalse($tier->matchesQuantity(101));
    }

    public function test_unlimited_pricing_tier_matches_any_quantity_above_min(): void
    {
        $tier = PackagePricingTier::factory()->unlimited()->create([
            'min_quantity' => 100,
        ]);

        $this->assertFalse($tier->matchesQuantity(99));
        $this->assertTrue($tier->matchesQuantity(100));
        $this->assertTrue($tier->matchesQuantity(1000));
        $this->assertTrue($tier->matchesQuantity(10000));
    }

    public function test_pricing_tier_calculates_price_correctly(): void
    {
        $tier = PackagePricingTier::factory()->create([
            'price_per_unit' => 2.50,
        ]);

        $price = $tier->calculatePrice(100);
        
        $this->assertEquals(250.00, $price);
    }
}

