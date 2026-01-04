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
        Schema::table('profiles', function (Blueprint $table) {
            // Profile status lifecycle
            $table->enum('status', [
                'draft',
                'ready',
                'pending_payment',
                'paid',
                'published',
                'expired',
                'suspended'
            ])->default('draft')->after('is_public');
            
            // Publishing timestamps
            $table->timestamp('published_at')->nullable()->after('status');
            $table->timestamp('expires_at')->nullable()->after('published_at');
            
            // Package & Order relationships
            $table->foreignId('package_id')->nullable()->after('expires_at')
                ->constrained('packages')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->after('package_id')
                ->constrained('orders')->nullOnDelete();
            
            // Indexes for performance
            $table->index('status');
            $table->index('published_at');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropForeign(['order_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['published_at']);
            $table->dropIndex(['expires_at']);
            $table->dropColumn([
                'status',
                'published_at',
                'expires_at',
                'package_id',
                'order_id'
            ]);
        });
    }
};
