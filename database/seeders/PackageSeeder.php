<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackagePricingTier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NFC Plain Card Package
        $nfcPlain = Package::create([
            'name' => 'NFC Plain Card',
            'slug' => 'nfc-plain-card',
            'description' => 'A simple, elegant NFC card that stores your digital profile URL. Perfect for professionals who want a clean, minimalist design.',
            'type' => 'nfc_plain',
            'is_active' => true,
            'display_order' => 1,
            'base_price' => 29.99,
            'features' => [
                'Free shipping',
                'QR code included',
                'Lifetime profile updates',
                'Works with all NFC-enabled devices',
            ],
        ]);

        // NFC Printed Card Package
        $nfcPrinted = Package::create([
            'name' => 'NFC Printed Card',
            'slug' => 'nfc-printed-card',
            'description' => 'A premium NFC card with custom printing. Showcase your brand with a professionally designed card that matches your style.',
            'type' => 'nfc_printed',
            'is_active' => true,
            'display_order' => 2,
            'base_price' => 39.99,
            'features' => [
                'Custom design',
                'Free shipping',
                'QR code included',
                'Lifetime profile updates',
                'Premium cardstock',
            ],
        ]);

        // Classic Cards Package (with quantity-based pricing)
        $classic = Package::create([
            'name' => 'Classic Business Cards',
            'slug' => 'classic-business-cards',
            'description' => 'Traditional business cards with QR codes linking to your digital profile. Perfect for bulk orders with volume discounts.',
            'type' => 'classic',
            'is_active' => true,
            'display_order' => 3,
            'features' => [
                'Bulk pricing available',
                'Free shipping on orders 100+',
                'Custom printing available',
                'QR code on every card',
                'Multiple design options',
            ],
        ]);

        // Create pricing tiers for Classic package
        PackagePricingTier::create([
            'package_id' => $classic->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
            'is_active' => true,
        ]);

        PackagePricingTier::create([
            'package_id' => $classic->id,
            'min_quantity' => 101,
            'max_quantity' => 500,
            'price_per_unit' => 2.00,
            'is_active' => true,
        ]);

        PackagePricingTier::create([
            'package_id' => $classic->id,
            'min_quantity' => 501,
            'max_quantity' => null, // Unlimited
            'price_per_unit' => 1.75,
            'is_active' => true,
        ]);
    }
}
