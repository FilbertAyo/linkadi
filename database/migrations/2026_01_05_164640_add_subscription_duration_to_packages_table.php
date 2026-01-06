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
        Schema::table('packages', function (Blueprint $table) {
            // Add default subscription duration in days (365 days = 1 year)
            $table->integer('subscription_duration_days')->default(365)->after('subscription_renewal_price');
            // Add flag to enable multi-year subscription tiers
            $table->boolean('enable_multi_year_subscriptions')->default(false)->after('subscription_duration_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['subscription_duration_days', 'enable_multi_year_subscriptions']);
        });
    }
};
