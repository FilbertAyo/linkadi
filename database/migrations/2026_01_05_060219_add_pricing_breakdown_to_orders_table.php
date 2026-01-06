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
        Schema::table('orders', function (Blueprint $table) {
            // Pricing breakdown fields
            $table->decimal('base_price', 10, 2)->nullable()->after('unit_price');
            $table->decimal('subscription_price', 10, 2)->nullable()->after('base_price');
            $table->decimal('printing_fee', 10, 2)->nullable()->after('subscription_price');
            $table->decimal('design_fee', 10, 2)->nullable()->after('printing_fee');
            
            // Order options
            $table->boolean('requires_printing')->default(false)->after('design_fee');
            $table->boolean('has_design')->default(false)->after('requires_printing');
            $table->string('card_color')->nullable()->after('has_design');
            
            // Pricing breakdown JSON for complex scenarios
            $table->json('pricing_breakdown')->nullable()->after('card_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'base_price',
                'subscription_price',
                'printing_fee',
                'design_fee',
                'requires_printing',
                'has_design',
                'card_color',
                'pricing_breakdown',
            ]);
        });
    }
};
