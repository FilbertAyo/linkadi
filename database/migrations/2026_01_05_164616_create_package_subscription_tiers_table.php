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
        Schema::create('package_subscription_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->integer('years'); // Number of years (1, 2, 3, etc.)
            $table->decimal('price', 10, 2); // Price for this duration
            $table->decimal('discount_percentage', 5, 2)->default(0); // Discount percentage applied
            $table->string('label')->nullable(); // E.g., "Best Value", "Most Popular"
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            // Ensure unique year per package
            $table->unique(['package_id', 'years']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_subscription_tiers');
    }
};
