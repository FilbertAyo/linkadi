<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use App\Models\PackagePricingTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_orders_index(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/orders');

        $response->assertOk();
        $response->assertSee('My Orders');
    }

    public function test_user_can_view_their_order_details(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/orders/{$order->id}");

        $response->assertOk();
        $response->assertSee("Order #{$order->id}");
    }

    public function test_user_cannot_view_other_users_orders(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->get("/orders/{$order->id}");

        $response->assertForbidden();
    }

    public function test_user_can_create_order_for_nfc_package(): void
    {
        $user = User::factory()->create();
        $package = Package::factory()->nfcPlain()->create([
            'base_price' => 29.99,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/orders', [
            'package_id' => $package->id,
            'quantity' => 1,
            'shipping_address' => '123 Test St, Test City',
            'notes' => 'Test notes',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'package_id' => $package->id,
            'quantity' => 1,
            'unit_price' => 29.99,
            'total_price' => 29.99,
            'status' => 'pending',
        ]);
    }

    public function test_user_can_create_order_for_classic_package_with_quantity(): void
    {
        $user = User::factory()->create();
        $package = Package::factory()->classic()->create(['is_active' => true]);
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
        ]);

        $response = $this->actingAs($user)->post('/orders', [
            'package_id' => $package->id,
            'quantity' => 50,
            'shipping_address' => '123 Test St',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'package_id' => $package->id,
            'quantity' => 50,
            'unit_price' => 2.50,
            'total_price' => 125.00, // 50 * 2.50
        ]);
    }

    public function test_order_uses_correct_pricing_tier_for_classic_package(): void
    {
        $user = User::factory()->create();
        $package = Package::factory()->classic()->create(['is_active' => true]);
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
        ]);
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 101,
            'max_quantity' => 500,
            'price_per_unit' => 2.00,
        ]);

        $response = $this->actingAs($user)->post('/orders', [
            'package_id' => $package->id,
            'quantity' => 200,
            'shipping_address' => '123 Test St',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'quantity' => 200,
            'unit_price' => 2.00,
            'total_price' => 400.00, // 200 * 2.00
        ]);
    }

    public function test_order_validation_requires_package_and_quantity(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/orders', []);

        $response->assertSessionHasErrors(['package_id', 'quantity']);
    }

    public function test_cannot_order_inactive_package(): void
    {
        $user = User::factory()->create();
        $package = Package::factory()->inactive()->create();

        $response = $this->actingAs($user)->post('/orders', [
            'package_id' => $package->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_cannot_order_with_invalid_quantity_for_classic_package(): void
    {
        $user = User::factory()->create();
        $package = Package::factory()->classic()->create(['is_active' => true]);
        
        PackagePricingTier::factory()->create([
            'package_id' => $package->id,
            'min_quantity' => 10,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
        ]);

        $response = $this->actingAs($user)->post('/orders', [
            'package_id' => $package->id,
            'quantity' => 5, // Below min_quantity
        ]);

        $response->assertSessionHas('error');
    }

    public function test_guest_cannot_create_order(): void
    {
        $package = Package::factory()->create();

        $response = $this->post('/orders', [
            'package_id' => $package->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_order_shows_correct_status_badges(): void
    {
        $user = User::factory()->create();
        
        $pendingOrder = Order::factory()->pending()->create(['user_id' => $user->id]);
        $processingOrder = Order::factory()->processing()->create(['user_id' => $user->id]);
        $deliveredOrder = Order::factory()->delivered()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/orders');

        $response->assertOk();
        $response->assertSee('Pending');
        $response->assertSee('Processing');
        $response->assertSee('Delivered');
    }

    public function test_orders_are_paginated(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(20)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/orders');

        $response->assertOk();
        // Check that pagination is working
        $this->assertTrue($response->viewData('orders')->hasPages());
    }

    public function test_order_quantity_must_be_at_least_one(): void
    {
        $user = User::factory()->create();
        $package = Package::factory()->create();

        $response = $this->actingAs($user)->post('/orders', [
            'package_id' => $package->id,
            'quantity' => 0,
        ]);

        $response->assertSessionHasErrors(['quantity']);
    }
}

