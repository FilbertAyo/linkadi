<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing packages: convert nfc_plain and nfc_printed to nfc_card
        DB::table('packages')
            ->whereIn('type', ['nfc_plain', 'nfc_printed'])
            ->update(['type' => 'nfc_card']);

        // Modify the enum column
        DB::statement("ALTER TABLE `packages` MODIFY `type` ENUM('nfc_card', 'classic') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert nfc_card back to nfc_plain (we can't determine which it was originally)
        DB::table('packages')
            ->where('type', 'nfc_card')
            ->update(['type' => 'nfc_plain']);

        // Restore the original enum
        DB::statement("ALTER TABLE `packages` MODIFY `type` ENUM('nfc_plain', 'nfc_printed', 'classic') NOT NULL");
    }
};
