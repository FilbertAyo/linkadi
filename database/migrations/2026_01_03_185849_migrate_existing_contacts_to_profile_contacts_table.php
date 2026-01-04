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
        // Migrate existing phone and email data to profile_contacts table
        $profiles = DB::table('profiles')->get();
        
        foreach ($profiles as $profile) {
            $order = 0;
            
            // Migrate phone if exists
            if ($profile->phone) {
                DB::table('profile_contacts')->insert([
                    'profile_id' => $profile->id,
                    'type' => 'phone',
                    'value' => $profile->phone,
                    'category' => 'main',
                    'is_primary' => true,
                    'order' => $order++,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Migrate email if exists
            if ($profile->email) {
                DB::table('profile_contacts')->insert([
                    'profile_id' => $profile->id,
                    'type' => 'email',
                    'value' => $profile->email,
                    'category' => 'main',
                    'is_primary' => true,
                    'order' => $order++,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear migrated data
        DB::table('profile_contacts')->truncate();
    }
};
