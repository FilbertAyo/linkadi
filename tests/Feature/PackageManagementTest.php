<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackagePricingTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PackageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
    }

    protected function createAdminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        return $user;
    }

    public function test_admin_can_view_packages_list(): void
    {
        $admin = $this->createAdminUser();
        Package::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/packages');

        $response->assertOk();
        $response->assertSee('Package Management');
    }

    public function test_admin_can_create_nfc_plain_package(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post('/admin/packages', [
            'name' => 'NFC Plain Test',
            'slug' => 'nfc-plain-test',
            'description' => 'Test description',
            'type' => 'nfc_plain',
            'base_price' => 29.99,
            'is_active' => true,
            'display_order' => 1,
            'features' => ['Feature 1', 'Feature 2'],
        ]);

        $response->assertRedirect('/admin/packages');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('packages', [
            'name' => 'NFC Plain Test',
            'type' => 'nfc_plain',
            'base_price' => 29.99,
        ]);
    }

    public function test_admin_can_create_nfc_printed_package(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post('/admin/packages', [
            'name' => 'NFC Printed Test',
            'slug' => 'nfc-printed-test',
            'description' => 'Test description',
            'type' => 'nfc_printed',
            'base_price' => 39.99,
            'is_active' => true,
            'display_order' => 2,
            'features' => ['Feature 1'],
        ]);

        $response->assertRedirect('/admin/packages');
        $this->assertDatabaseHas('packages', [
            'name' => 'NFC Printed Test',
            'type' => 'nfc_printed',
            'base_price' => 39.99,
        ]);
    }

    public function test_admin_can_create_classic_package_with_pricing_tiers(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post('/admin/packages', [
            'name' => 'Classic Cards Test',
            'slug' => 'classic-cards-test',
            'description' => 'Test description',
            'type' => 'classic',
            'is_active' => true,
            'display_order' => 3,
            'features' => ['Bulk pricing'],
            'pricing_tiers' => [
                [
                    'min_quantity' => 1,
                    'max_quantity' => 100,
                    'price_per_unit' => 2.50,
                ],
                [
                    'min_quantity' => 101,
                    'max_quantity' => 500,
                    'price_per_unit' => 2.00,
                ],
                [
                    'min_quantity' => 501,
                    'max_quantity' => null,
                    'price_per_unit' => 1.75,
                ],
            ],
        ]);

        $response->assertRedirect('/admin/packages');
        
        $package = Package::where('slug', 'classic-cards-test')->first();
        $this->assertNotNull($package);
        $this->assertEquals('classic', $package->type);
        $this->assertEquals(3, $package->pricingTiers()->count());
    }

    public function test_admin_can_update_package(): void
    {
        $admin = $this->createAdminUser();
        $package = Package::factory()->nfcPlain()->create([
            'name' => 'Original Name',
            'base_price' => 25.00,
        ]);

        $response = $this->actingAs($admin)->put("/admin/packages/{$package->id}", [
            'name' => 'Updated Name',
            'slug' => $package->slug,
            'description' => 'Updated description',
            'type' => 'nfc_plain',
            'base_price' => 30.00,
            'is_active' => true,
            'display_order' => 1,
            'features' => ['Updated feature'],
        ]);

        $response->assertRedirect('/admin/packages');
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'name' => 'Updated Name',
            'base_price' => 30.00,
        ]);
    }

    public function test_admin_can_delete_package(): void
    {
        $admin = $this->createAdminUser();
        $package = Package::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/packages/{$package->id}");

        $response->assertRedirect('/admin/packages');
        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
    }

    public function test_admin_can_toggle_package_active_status(): void
    {
        $admin = $this->createAdminUser();
        $package = Package::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post("/admin/packages/{$package->id}/toggle-active");

        $response->assertRedirect();
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_add_pricing_tier_to_classic_package(): void
    {
        $admin = $this->createAdminUser();
        $package = Package::factory()->classic()->create();

        $response = $this->actingAs($admin)->post("/admin/packages/{$package->id}/pricing-tiers", [
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('package_pricing_tiers', [
            'package_id' => $package->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
            'price_per_unit' => 2.50,
        ]);
    }

    public function test_admin_can_remove_pricing_tier(): void
    {
        $admin = $this->createAdminUser();
        $package = Package::factory()->classic()->create();
        $tier = PackagePricingTier::factory()->create(['package_id' => $package->id]);

        $response = $this->actingAs($admin)->delete("/admin/packages/{$package->id}/pricing-tiers/{$tier->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('package_pricing_tiers', ['id' => $tier->id]);
    }

    public function test_non_admin_cannot_access_package_management(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/packages');

        $response->assertForbidden();
    }

    public function test_package_validation_requires_name_and_type(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post('/admin/packages', []);

        $response->assertSessionHasErrors(['name', 'type']);
    }

    public function test_package_slug_is_auto_generated_if_not_provided(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post('/admin/packages', [
            'name' => 'Test Package Name',
            'type' => 'nfc_plain',
            'base_price' => 29.99,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('packages', [
            'name' => 'Test Package Name',
            'slug' => 'test-package-name',
        ]);
    }

    public function test_package_search_functionality(): void
    {
        $admin = $this->createAdminUser();
        Package::factory()->create(['name' => 'NFC Card Package']);
        Package::factory()->create(['name' => 'Classic Cards']);

        $response = $this->actingAs($admin)->get('/admin/packages?search=NFC');

        $response->assertOk();
        $response->assertSee('NFC Card Package');
        $response->assertDontSee('Classic Cards');
    }

    public function test_package_filter_by_type(): void
    {
        $admin = $this->createAdminUser();
        Package::factory()->nfcPlain()->create();
        Package::factory()->classic()->create();

        $response = $this->actingAs($admin)->get('/admin/packages?type=nfc_plain');

        $response->assertOk();
    }

    public function test_package_filter_by_status(): void
    {
        $admin = $this->createAdminUser();
        Package::factory()->create(['is_active' => true]);
        Package::factory()->inactive()->create();

        $response = $this->actingAs($admin)->get('/admin/packages?status=active');

        $response->assertOk();
    }
}

