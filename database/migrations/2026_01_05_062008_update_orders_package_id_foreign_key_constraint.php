<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the connection name
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        
        if ($driver === 'mysql') {
            // Drop the existing foreign key constraint
            DB::statement('ALTER TABLE `orders` DROP FOREIGN KEY `orders_package_id_foreign`');
            
            // Make package_id nullable
            DB::statement('ALTER TABLE `orders` MODIFY `package_id` BIGINT UNSIGNED NULL');
            
            // Re-add the foreign key constraint with set null on delete
            DB::statement('ALTER TABLE `orders` ADD CONSTRAINT `orders_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL');
        } else {
            // Fallback for other databases
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['package_id']);
            });
            
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('package_id')->nullable()->change();
            });
            
            Schema::table('orders', function (Blueprint $table) {
                $table->foreign('package_id')
                      ->references('id')
                      ->on('packages')
                      ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get the connection name
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        
        if ($driver === 'mysql') {
            // Drop the foreign key constraint
            DB::statement('ALTER TABLE `orders` DROP FOREIGN KEY `orders_package_id_foreign`');
            
            // Make package_id non-nullable again
            DB::statement('ALTER TABLE `orders` MODIFY `package_id` BIGINT UNSIGNED NOT NULL');
            
            // Re-add the original foreign key constraint with restrict
            DB::statement('ALTER TABLE `orders` ADD CONSTRAINT `orders_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE RESTRICT');
        } else {
            // Fallback for other databases
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['package_id']);
            });
            
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('package_id')->nullable(false)->change();
            });
            
            Schema::table('orders', function (Blueprint $table) {
                $table->foreign('package_id')
                      ->references('id')
                      ->on('packages')
                      ->onDelete('restrict');
            });
        }
    }
};
