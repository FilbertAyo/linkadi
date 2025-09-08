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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // Product name
            $table->decimal('price', 10, 2);              // Product price
            $table->integer('stock_quantity')->default(0); // Quantity in stock
            $table->text('description')->nullable();      // Product description
            $table->string('category')->nullable();       // E.g. NFC Card, NFC Sticker, NFC Keychain
            $table->string('sku')->unique()->nullable();  // Stock Keeping Unit (tracking code)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
