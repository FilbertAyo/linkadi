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
            // Add profile_id for subscription renewal orders
            // Nullable because new card orders don't have a single profile (they have multiple via nfc_cards)
            if (!Schema::hasColumn('orders', 'profile_id')) {
                $table->foreignId('profile_id')->nullable()->after('user_id')
                    ->constrained('profiles')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'profile_id')) {
                $table->dropForeign(['profile_id']);
                $table->dropColumn('profile_id');
            }
        });
    }
};
