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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Relationships
            $table->foreignId('package_id')->nullable()
                ->constrained('packages')->onDelete('set null');
            $table->foreignId('profile_id')->nullable()
                ->constrained('profiles')->nullOnDelete()
                ->comment('For subscription renewal orders');
            
            // Order details
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            
            // Pricing breakdown
            $table->decimal('base_price', 10, 2)->nullable();
            $table->decimal('subscription_price', 10, 2)->nullable();
            $table->decimal('printing_fee', 10, 2)->nullable();
            $table->decimal('design_fee', 10, 2)->nullable();
            
            // Subscription details
            $table->integer('subscription_years')->default(1);
            $table->decimal('subscription_discount', 10, 2)->default(0);
            
            // Order options
            $table->boolean('requires_printing')->default(false);
            $table->boolean('has_design')->default(false);
            $table->string('card_color')->nullable();
            $table->json('pricing_breakdown')->nullable()
                ->comment('Pricing breakdown JSON for complex scenarios');
            
            // Order status
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            
            // Payment status tracking
            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'refunded',
                'cancelled'
            ])->default('pending');
            
            // Payment details
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference')->nullable()
                ->comment('Stripe/PayPal transaction ID');
            $table->timestamp('paid_at')->nullable();
            
            // Shipping and notes
            $table->text('shipping_address')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('package_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
