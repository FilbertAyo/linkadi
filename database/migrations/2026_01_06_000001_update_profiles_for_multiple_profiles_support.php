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
        Schema::table('profiles', function (Blueprint $table) {
            // Remove unique constraint on slug (users can have multiple profiles with different slugs)
            // But keep slug unique per user
            $table->dropUnique(['slug']);
            
            // Add profile name for user identification
            $table->string('profile_name')->nullable()->after('user_id')
                ->comment('User-friendly name for this profile (e.g., "Personal Card", "Company Card")');
            
            // Add display mode to control what information is shown
            $table->enum('display_mode', ['personal_only', 'company_only', 'combined'])
                ->default('combined')
                ->after('profile_name')
                ->comment('Controls what information is displayed: personal only, company only, or both');
            
            // Add is_primary flag for backward compatibility
            $table->boolean('is_primary')->default(false)->after('display_mode')
                ->comment('Mark one profile as primary (for backward compatibility)');
            
            // Add unique constraint: slug must be unique per user
            $table->unique(['user_id', 'slug'], 'profiles_user_slug_unique');
            
            // Add index for faster queries
            $table->index(['user_id', 'is_primary']);
        });
        
        // Set existing profiles as primary
        DB::statement('UPDATE profiles SET is_primary = true WHERE id IN (
            SELECT id FROM (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY created_at ASC) as rn
                FROM profiles
            ) t WHERE rn = 1
        )');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('profiles_user_slug_unique');
            
            // Drop indexes
            $table->dropIndex(['user_id', 'is_primary']);
            
            // Drop new columns
            $table->dropColumn(['profile_name', 'display_mode', 'is_primary']);
            
            // Restore unique constraint on slug
            $table->unique('slug');
        });
    }
};

