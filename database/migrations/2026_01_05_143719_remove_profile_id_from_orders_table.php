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
            // Drop profile_id foreign key and column
            // Profile relationship is now tracked in nfc_cards table
            // This allows one order to have multiple NFC cards for different profiles
            $table->dropForeign(['profile_id']);
            $table->dropColumn('profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Re-add profile_id for rollback
            $table->foreignId('profile_id')->nullable()->after('user_id')
                ->constrained('profiles')->nullOnDelete();
        });
    }
};
