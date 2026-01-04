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
            // Profile type (individual or business)
            $table->enum('profile_type', ['individual', 'business'])
                ->default('individual')
                ->after('user_id');
            
            // Business-specific fields
            $table->string('business_name')->nullable()->after('company');
            $table->string('tax_id', 100)->nullable()->after('business_name')
                ->comment('Tax ID / VAT / Registration Number');
            
            // Index for filtering
            $table->index('profile_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex(['profile_type']);
            $table->dropColumn([
                'profile_type',
                'business_name',
                'tax_id',
            ]);
        });
    }
};
