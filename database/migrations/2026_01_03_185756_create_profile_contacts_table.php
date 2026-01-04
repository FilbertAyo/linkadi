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
        Schema::create('profile_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            
            // Contact type and value
            $table->enum('type', ['phone', 'email']);
            $table->string('value'); // The actual phone number or email
            
            // Category/label
            $table->enum('category', ['main', 'work', 'home', 'mobile', 'custom'])->default('main');
            $table->string('custom_label')->nullable(); // For custom category
            
            // Priority and visibility
            $table->boolean('is_primary')->default(false); // One primary per type per profile
            $table->integer('order')->default(0); // Display order
            $table->boolean('is_public')->default(true); // Show on public profile
            
            $table->timestamps();
            
            // Indexes
            $table->index(['profile_id', 'type']);
            $table->index(['profile_id', 'is_primary']);
            $table->index('order');
            
            // Unique constraint: Only one primary per type per profile
            $table->unique(['profile_id', 'type', 'is_primary'], 'unique_primary_per_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_contacts');
    }
};
