<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Subscription renewal price (for NFC cards)
            $table->decimal('subscription_renewal_price', 10, 2)->nullable()->after('base_price');
            
            // Printing fee (for NFC cards when printing is required)
            $table->decimal('printing_fee', 10, 2)->nullable()->after('subscription_renewal_price');
            
            // Design fee (for Classic cards when client doesn't have design)
            $table->decimal('design_fee', 10, 2)->nullable()->after('printing_fee');
            
            // Available card colors (JSON array for NFC cards)
            $table->json('card_colors')->nullable()->after('design_fee');
            
            // Flexible pricing configuration (JSON for complex pricing rules)
            $table->json('pricing_config')->nullable()->after('card_colors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_renewal_price',
                'printing_fee',
                'design_fee',
                'card_colors',
                'pricing_config',
            ]);
        });
    }
};
