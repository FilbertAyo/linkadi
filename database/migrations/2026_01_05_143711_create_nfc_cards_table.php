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
        Schema::create('nfc_cards', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('restrict');
            
            // Card identification
            $table->string('card_number', 50)->unique()->comment('Physical card serial/NFC UID');
            $table->string('qr_code')->nullable()->comment('Unique QR code for this card');
            
            // Card customization
            $table->string('card_color', 50)->nullable();
            $table->boolean('requires_printing')->default(false);
            $table->json('printing_text')->nullable()->comment('Custom text for printing');
            $table->string('design_file')->nullable()->comment('Path to custom design file');
            
            // Subscription tracking
            $table->timestamp('activated_at')->nullable()->comment('When card was first activated');
            $table->timestamp('expires_at')->nullable()->comment('When subscription expires');
            
            // Card status
            $table->enum('status', [
                'pending_production',
                'in_production',
                'produced',
                'shipped',
                'delivered',
                'activated',
                'expired',
                'suspended',
                'deactivated'
            ])->default('pending_production');
            
            // Fulfillment tracking
            $table->text('production_notes')->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('profile_id');
            $table->index('order_id');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfc_cards');
    }
};
