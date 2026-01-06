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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Profile identification
            $table->string('profile_name')->nullable()
                ->comment('User-friendly name for this profile (e.g., "Personal Card", "Company Card")');
            $table->enum('profile_type', ['individual', 'business'])
                ->default('individual');
            $table->string('slug');
            
            // Profile information
            $table->string('title')->nullable();
            $table->string('company')->nullable();
            $table->string('business_name')->nullable();
            $table->string('tax_id', 100)->nullable()
                ->comment('Tax ID / VAT / Registration Number');
            $table->text('bio')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('cover_image')->nullable();
            
            // Display and visibility
            $table->enum('display_mode', ['personal_only', 'company_only', 'combined'])
                ->default('combined')
                ->comment('Controls what information is displayed: personal only, company only, or both');
            $table->boolean('is_public')->default(true);
            $table->boolean('is_primary')->default(false)
                ->comment('Mark one profile as primary (for backward compatibility)');
            
            // Profile status lifecycle
            $table->enum('status', [
                'draft',
                'ready',
                'pending_payment',
                'paid',
                'published',
                'expired',
                'suspended'
            ])->default('draft');
            
            // Publishing timestamps
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Package & Order relationships
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['user_id', 'slug'], 'profiles_user_slug_unique');
            $table->index('profile_type');
            $table->index(['user_id', 'is_primary']);
            $table->index('status');
            $table->index('published_at');
            $table->index('expires_at');
            $table->index('package_id');
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
