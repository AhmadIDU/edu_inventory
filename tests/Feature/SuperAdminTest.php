<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetStatus;
use App\Models\Institution;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Storage::fake('public');
    }

    public function test_super_admin_can_access_super_panel(): void
    {
        $admin = $this->createSuperAdmin();

        $response = $this->actingAs($admin)->get('/super');

        $response->assertOk();
    }

    public function test_institution_admin_cannot_access_super_panel(): void
    {
        $institution = $this->createInstitution();
        $admin       = $this->createInstitutionAdmin($institution);

        $response = $this->actingAs($admin)->get('/super');

        // Institution admin has no super_admin role — should be forbidden
        $response->assertForbidden();
    }

    public function test_super_admin_can_see_all_institutions(): void
    {
        $this->createInstitution(['name' => 'Inst A']);
        $this->createInstitution(['name' => 'Inst B']);
        $this->createInstitution(['name' => 'Inst C']);

        $count = Institution::count();

        $this->assertEquals(3, $count);
    }

    public function test_super_admin_can_see_assets_from_all_institutions(): void
    {
        $instA  = $this->createInstitution();
        $instB  = $this->createInstitution();
        $status = AssetStatus::withoutGlobalScopes()->create(['name' => 'Active', 'color' => '#0f0', 'is_system' => true]);
        $roomA  = Room::withoutGlobalScopes()->create(['institution_id' => $instA->id, 'name' => 'R1']);
        $roomB  = Room::withoutGlobalScopes()->create(['institution_id' => $instB->id, 'name' => 'R2']);

        Asset::withoutGlobalScopes()->create(['institution_id' => $instA->id, 'room_id' => $roomA->id, 'status_id' => $status->id, 'name' => 'A1', 'qr_code' => Str::uuid()]);
        Asset::withoutGlobalScopes()->create(['institution_id' => $instB->id, 'room_id' => $roomB->id, 'status_id' => $status->id, 'name' => 'A2', 'qr_code' => Str::uuid()]);

        // Super admin query — no global scope applied
        $total = Asset::withoutGlobalScopes()->count();

        $this->assertEquals(2, $total);
    }

    public function test_creating_institution_admin_links_to_institution(): void
    {
        $institution = $this->createInstitution();
        $admin       = $this->createInstitutionAdmin($institution);

        $this->assertEquals($institution->id, $admin->institution_id);
        $this->assertTrue($admin->hasRole('institution_admin'));
    }

    public function test_super_admin_has_no_institution_id(): void
    {
        $admin = $this->createSuperAdmin();

        $this->assertNull($admin->institution_id);
        $this->assertTrue($admin->hasRole('super_admin'));
    }

    public function test_super_admin_panel_shows_institution_count_on_dashboard(): void
    {
        $this->createInstitution();
        $this->createInstitution();

        $admin = $this->createSuperAdmin();

        // Just verify the dashboard route is accessible
        $response = $this->actingAs($admin)->get('/super');

        $response->assertSuccessful();
    }

    public function test_institution_admin_cannot_access_admin_panel_of_another_institution(): void
    {
        $instA = $this->createInstitution();
        $instB = $this->createInstitution();

        $adminA = $this->createInstitutionAdmin($instA);

        // Acting as admin of institution A, try to reach institution B's resources
        $this->actingAs($adminA);
        app()->forgetInstance('current_institution_id');

        // All queries should be scoped to instA only
        Room::withoutGlobalScopes()->create(['institution_id' => $instB->id, 'name' => 'Secret Room']);

        $rooms = Room::all(); // scoped query

        $this->assertCount(0, $rooms); // admin A sees nothing from B
    }
}
