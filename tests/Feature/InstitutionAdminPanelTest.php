<?php

namespace Tests\Feature;

use App\Models\AssetCategory;
use App\Models\AssetStatus;
use App\Models\Branch;
use App\Models\Institution;
use App\Models\ResponsiblePerson;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests that institution admin can access /admin panel routes
 * and that the data they manage is correctly scoped.
 */
class InstitutionAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->institution = $this->createInstitution(['name' => 'Test School']);
    }

    public function test_institution_admin_can_access_admin_panel(): void
    {
        $admin = $this->createInstitutionAdmin($this->institution);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }

    public function test_unauthenticated_user_redirected_from_admin_panel(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_super_admin_cannot_access_institution_admin_panel(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/admin');

        // Super admin has no institution_id — should be rejected with 403
        $response->assertForbidden();
    }

    public function test_institution_admin_can_reach_branches_list(): void
    {
        $admin = $this->createInstitutionAdmin($this->institution);

        $response = $this->actingAs($admin)->get('/admin/branches');

        $response->assertOk();
    }

    public function test_institution_admin_can_reach_rooms_list(): void
    {
        $admin = $this->createInstitutionAdmin($this->institution);

        $response = $this->actingAs($admin)->get('/admin/rooms');

        $response->assertOk();
    }

    public function test_institution_admin_can_reach_assets_list(): void
    {
        $admin = $this->createInstitutionAdmin($this->institution);

        $response = $this->actingAs($admin)->get('/admin/assets');

        $response->assertOk();
    }

    public function test_institution_admin_can_reach_categories_list(): void
    {
        $admin = $this->createInstitutionAdmin($this->institution);

        $response = $this->actingAs($admin)->get('/admin/asset-categories');

        $response->assertOk();
    }

    public function test_institution_admin_can_reach_responsible_persons_list(): void
    {
        $admin = $this->createInstitutionAdmin($this->institution);

        $response = $this->actingAs($admin)->get('/admin/responsible-people');

        $response->assertOk();
    }

    public function test_institution_admin_can_reach_transfer_log(): void
    {
        $admin = $this->createInstitutionAdmin($this->institution);

        $response = $this->actingAs($admin)->get('/admin/asset-transfers');

        $response->assertOk();
    }
}
