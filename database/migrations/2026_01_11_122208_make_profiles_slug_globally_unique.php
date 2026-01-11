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
            // Drop the existing unique constraint on user_id + slug
            $table->dropUnique('profiles_user_slug_unique');
            
            // Add a globally unique constraint on slug only
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Drop the global unique constraint
            $table->dropUnique(['slug']);
            
            // Restore the per-user unique constraint
            $table->unique(['user_id', 'slug'], 'profiles_user_slug_unique');
        });
    }
};
