<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackagePricingTier;
use App\Models\PackageSubscriptionTier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NFC Card Package
        // Card price: 30,000 TZS includes 1 year subscription
        // Renewal after 1 year: 10,000 TZS / year
        // Multi-year options with discounts: 2 years (5% off), 3 years (10% off)
        // If printing is required: 36,000 TZS (card + print), subscription rules stay the same
        $nfcCard = Package::create([
            'name' => 'NFC Card',
            'slug' => 'nfc-card',
            'description' => 'A simple, elegant NFC card that stores your digital profile URL. Perfect for professionals who want a clean, minimalist design. Includes 1 year subscription. Printing option available.',
            'type' => 'nfc_card',
            'is_active' => true,
            'display_order' => 1,
            'base_price' => 30000.00, // Card price includes 1 year subscription
            'subscription_renewal_price' => 10000.00, // Renewal after 1 year
            'subscription_duration_days' => 365, // 1 year
            'enable_multi_year_subscriptions' => true, // Enable multi-year options
            'printing_fee' => 6000.00, // Additional fee if printing is required (36,000 - 30,000)
            'card_colors' => [
                'white',
                'black',
                'blue',
                'red',
                'green',
                'gold',
                'silver',
            ],
            'features' => [
                '1 year subscription included',
                'Multi-year discounts available',
                'Free shipping',
                'QR code included',
                'Lifetime profile updates',
                'Works with all NFC-enabled devices',
                'Multiple color options',
            ],
        ]);

        // Create subscription tiers for NFC Card package
        // Year 1: 30,000 TZS (Card + 1st year subscription included)
        // Year 2+: 10,000 TZS per year (with discounts for multi-year purchase)
        
        // 1 Year: Just the card + 1st year (30,000 TZS)
        PackageSubscriptionTier::create([
            'package_id' => $nfcCard->id,
            'years' => 1,
            'price' => 30000.00, // Card + 1 year subscription included
            'discount_percentage' => 0,
            'label' => null,
            'is_active' => true,
            'display_order' => 1,
        ]);

        // 2 Years: Card + 1st year (30,000) + 2nd year with 5% discount (9,500)
        // Total: 39,500 TZS (Save 500 TZS from regular 40,000)
        PackageSubscriptionTier::create([
            'package_id' => $nfcCard->id,
            'years' => 2,
            'price' => 39500.00, // 30,000 + (10,000 * 0.95)
            'discount_percentage' => 5.00,
            'label' => 'Save 500 TZS',
            'is_active' => true,
            'display_order' => 2,
        ]);

        // 3 Years: Card + 1st year (30,000) + 2nd year (9,500) + 3rd year with 10% discount (9,000)
        // Total: 48,500 TZS (Save 1,500 TZS from regular 50,000)
        PackageSubscriptionTier::create([
            'package_id' => $nfcCard->id,
            'years' => 3,
            'price' => 48500.00, // 30,000 + 9,500 + 9,000
            'discount_percentage' => 10.00,
            'label' => 'Best Value - Save 1,500 TZS',
            'is_active' => true,
            'display_order' => 3,
        ]);

        // Classic Business Cards Package (with quantity-based pricing)
        // Keep existing range logic
        // Add design fee: 10,000 TZS if client doesn't have design
        $classic = Package::create([
            'name' => 'Classic Business Card',
            'slug' => 'classic-business-card',
            'description' => 'Traditional business cards with QR codes linking to your digital profile. Perfect for bulk orders with volume discounts. Design service available.',
            'type' => 'classic',
            'is_active' => true,
            'display_order' => 2,
            'design_fee' => 10000.00, // Design fee if client doesn't have design
            'features' => [
                'Bulk pricing available',
                'Free shipping on orders 100+',
                'Custom printing included',
                'QR code on every card',
                'Design service available',
                'Volume discounts',
            ],
        ]);

        // Create pricing tiers for Classic package
        PackagePricingTier::create([
            'package_id' => $classic->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => 2500.00,
            'is_active' => true,
        ]);

        PackagePricingTier::create([
            'package_id' => $classic->id,
            'min_quantity' => 101,
            'max_quantity' => 500,
            'price_per_unit' => 2000.00,
            'is_active' => true,
        ]);

        PackagePricingTier::create([
            'package_id' => $classic->id,
            'min_quantity' => 501,
            'max_quantity' => null, // Unlimited
            'price_per_unit' => 1750.00,
            'is_active' => true,
        ]);
    }
}
