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
            // Add separate fields for personal and company information
            $table->text('personal_bio')->nullable()->after('bio');
            $table->text('company_bio')->nullable()->after('personal_bio');
            $table->string('company_email')->nullable()->after('email');
            $table->string('company_phone')->nullable()->after('phone');
            $table->string('company_website')->nullable()->after('website');
            $table->text('company_address')->nullable()->after('address');
            $table->string('company_logo')->nullable()->after('profile_image');
            $table->string('industry')->nullable()->after('business_name');
            $table->string('company_size')->nullable()->after('industry');
            $table->text('services_offered')->nullable()->after('company_bio');
            $table->string('linkedin_url')->nullable()->after('website');
            $table->string('facebook_url')->nullable()->after('linkedin_url');
            $table->string('twitter_url')->nullable()->after('facebook_url');
            $table->string('instagram_url')->nullable()->after('twitter_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'personal_bio',
                'company_bio',
                'company_email',
                'company_phone',
                'company_website',
                'company_address',
                'company_logo',
                'industry',
                'company_size',
                'services_offered',
                'linkedin_url',
                'facebook_url',
                'twitter_url',
                'instagram_url',
            ]);
        });
    }
};
