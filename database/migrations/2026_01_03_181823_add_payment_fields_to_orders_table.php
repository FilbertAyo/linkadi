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
            // Payment status tracking
            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'refunded',
                'cancelled'
            ])->default('pending')->after('status');
            
            // Payment details
            $table->string('payment_method', 50)->nullable()->after('payment_status');
            $table->string('payment_reference')->nullable()->after('payment_method')
                ->comment('Stripe/PayPal transaction ID');
            $table->timestamp('paid_at')->nullable()->after('payment_reference');
            
            // Profile relationship (which profile this order is for)
            $table->foreignId('profile_id')->nullable()->after('user_id')
                ->constrained('profiles')->nullOnDelete();
            
            // Indexes for performance
            $table->index('payment_status');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['paid_at']);
            $table->dropColumn([
                'payment_status',
                'payment_method',
                'payment_reference',
                'paid_at',
                'profile_id'
            ]);
        });
    }
};
