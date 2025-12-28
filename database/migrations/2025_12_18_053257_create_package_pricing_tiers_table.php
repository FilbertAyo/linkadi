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
        Schema::create('package_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->integer('min_quantity');
            $table->integer('max_quantity')->nullable(); // null means unlimited
            $table->decimal('price_per_unit', 10, 2);
            $table->decimal('total_price', 10, 2)->nullable(); // Optional fixed total price
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for efficient range queries
            $table->index(['package_id', 'min_quantity', 'max_quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_pricing_tiers');
    }
};
