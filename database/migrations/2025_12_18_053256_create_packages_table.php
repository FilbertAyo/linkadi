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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['nfc_card', 'classic']);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->json('features')->nullable();
            
            // Pricing
            $table->decimal('base_price', 10, 2)->nullable()
                ->comment('For NFC types (single price)');
            $table->decimal('subscription_renewal_price', 10, 2)->nullable()
                ->comment('Subscription renewal price (for NFC cards)');
            $table->decimal('printing_fee', 10, 2)->nullable()
                ->comment('Printing fee (for NFC cards when printing is required)');
            $table->decimal('design_fee', 10, 2)->nullable()
                ->comment('Design fee (for Classic cards when client doesn\'t have design)');
            
            // Subscription settings
            $table->integer('subscription_duration_days')->default(365)
                ->comment('Default subscription duration in days (365 days = 1 year)');
            $table->boolean('enable_multi_year_subscriptions')->default(false)
                ->comment('Flag to enable multi-year subscription tiers');
            
            // Flexible pricing configuration
            $table->json('card_colors')->nullable()
                ->comment('Available card colors (JSON array for NFC cards)');
            $table->json('pricing_config')->nullable()
                ->comment('Flexible pricing configuration (JSON for complex pricing rules)');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
