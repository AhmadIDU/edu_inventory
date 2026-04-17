<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetStatus;
use App\Models\Branch;
use App\Models\Institution;
use App\Models\ResponsiblePerson;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Verifies that institution admins can only see their own institution's data
 * and that the InstitutionScope correctly isolates records between tenants.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $instA;
    protected Institution $instB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        $this->instA = $this->createInstitution(['name' => 'School A']);
        $this->instB = $this->createInstitution(['name' => 'School B']);
    }

    public function test_institution_admin_only_sees_own_branches(): void
    {
        Branch::withoutGlobalScopes()->create(['institution_id' => $this->instA->id, 'name' => 'Branch A']);
        Branch::withoutGlobalScopes()->create(['institution_id' => $this->instB->id, 'name' => 'Branch B']);

        $this->actingAs($this->createInstitutionAdmin($this->instA));
        app()->forgetInstance('current_institution_id');

        $branches = Branch::all();

        $this->assertCount(1, $branches);
        $this->assertEquals('Branch A', $branches->first()->name);
    }

    public function test_institution_admin_only_sees_own_rooms(): void
    {
        Room::withoutGlobalScopes()->create(['institution_id' => $this->instA->id, 'name' => 'Room A']);
        Room::withoutGlobalScopes()->create(['institution_id' => $this->instB->id, 'name' => 'Room B']);

        $this->actingAs($this->createInstitutionAdmin($this->instA));
        app()->forgetInstance('current_institution_id');

        $rooms = Room::all();

        $this->assertCount(1, $rooms);
        $this->assertEquals('Room A', $rooms->first()->name);
    }

    public function test_institution_admin_only_sees_own_responsible_persons(): void
    {
        ResponsiblePerson::withoutGlobalScopes()->create(['institution_id' => $this->instA->id, 'name' => 'Person A']);
        ResponsiblePerson::withoutGlobalScopes()->create(['institution_id' => $this->instB->id, 'name' => 'Person B']);

        $this->actingAs($this->createInstitutionAdmin($this->instA));
        app()->forgetInstance('current_institution_id');

        $persons = ResponsiblePerson::all();

        $this->assertCount(1, $persons);
        $this->assertEquals('Person A', $persons->first()->name);
    }

    public function test_institution_admin_only_sees_own_categories(): void
    {
        AssetCategory::withoutGlobalScopes()->create(['institution_id' => $this->instA->id, 'name' => 'Cat A']);
        AssetCategory::withoutGlobalScopes()->create(['institution_id' => $this->instB->id, 'name' => 'Cat B']);

        $this->actingAs($this->createInstitutionAdmin($this->instA));
        app()->forgetInstance('current_institution_id');

        $cats = AssetCategory::all();

        $this->assertCount(1, $cats);
        $this->assertEquals('Cat A', $cats->first()->name);
    }

    public function test_institution_admin_sees_system_statuses_plus_own_custom_ones(): void
    {
        AssetStatus::withoutGlobalScopes()->create(['institution_id' => null,              'name' => 'System Default', 'is_system' => true,  'color' => '#000']);
        AssetStatus::withoutGlobalScopes()->create(['institution_id' => $this->instA->id, 'name' => 'Custom A',      'is_system' => false, 'color' => '#aaa']);
        AssetStatus::withoutGlobalScopes()->create(['institution_id' => $this->instB->id, 'name' => 'Custom B',      'is_system' => false, 'color' => '#bbb']);

        $this->actingAs($this->createInstitutionAdmin($this->instA));
        app()->forgetInstance('current_institution_id');

        $statuses = AssetStatus::all();
        $names    = $statuses->pluck('name')->sort()->values();

        $this->assertCount(2, $statuses);
        $this->assertTrue($names->contains('System Default'));
        $this->assertTrue($names->contains('Custom A'));
        $this->assertFalse($names->contains('Custom B'));
    }

    public function test_institution_admin_only_sees_own_assets(): void
    {
        $status  = AssetStatus::withoutGlobalScopes()->create(['name' => 'Active', 'color' => '#0f0', 'is_system' => true]);
        $roomA   = Room::withoutGlobalScopes()->create(['institution_id' => $this->instA->id, 'name' => 'Room A']);
        $roomB   = Room::withoutGlobalScopes()->create(['institution_id' => $this->instB->id, 'name' => 'Room B']);

        Asset::withoutGlobalScopes()->create(['institution_id' => $this->instA->id, 'room_id' => $roomA->id, 'status_id' => $status->id, 'name' => 'Asset A', 'qr_code' => Str::uuid()]);
        Asset::withoutGlobalScopes()->create(['institution_id' => $this->instB->id, 'room_id' => $roomB->id, 'status_id' => $status->id, 'name' => 'Asset B', 'qr_code' => Str::uuid()]);

        $this->actingAs($this->createInstitutionAdmin($this->instA));
        app()->forgetInstance('current_institution_id');

        $assets = Asset::all();

        $this->assertCount(1, $assets);
        $this->assertEquals('Asset A', $assets->first()->name);
    }

    public function test_super_admin_has_no_scope_and_sees_all_data(): void
    {
        AssetCategory::withoutGlobalScopes()->create(['institution_id' => $this->instA->id, 'name' => 'Cat A']);
        AssetCategory::withoutGlobalScopes()->create(['institution_id' => $this->instB->id, 'name' => 'Cat B']);

        // Super admin has no institution_id — resolveInstitutionId() returns null
        $this->actingAs($this->createSuperAdmin());

        $cats = AssetCategory::withoutGlobalScopes()->get();
        $this->assertCount(2, $cats);
    }

    public function test_institution_id_auto_stamped_on_category_create(): void
    {
        $admin = $this->createInstitutionAdmin($this->instA);
        $this->actingAs($admin);
        app()->forgetInstance('current_institution_id');

        $cat = AssetCategory::create(['name' => 'Auto-stamped Category']);

        $this->assertEquals($this->instA->id, $cat->institution_id);
    }

    public function test_institution_id_auto_stamped_on_branch_create(): void
    {
        $admin = $this->createInstitutionAdmin($this->instA);
        $this->actingAs($admin);
        app()->forgetInstance('current_institution_id');

        $branch = Branch::create(['name' => 'New Branch']);

        $this->assertEquals($this->instA->id, $branch->institution_id);
    }

    public function test_institution_id_auto_stamped_on_room_create(): void
    {
        $admin = $this->createInstitutionAdmin($this->instA);
        $this->actingAs($admin);
        app()->forgetInstance('current_institution_id');

        $room = Room::create(['name' => 'New Room']);

        $this->assertEquals($this->instA->id, $room->institution_id);
    }

    public function test_institution_id_auto_stamped_on_responsible_person_create(): void
    {
        $admin = $this->createInstitutionAdmin($this->instA);
        $this->actingAs($admin);
        app()->forgetInstance('current_institution_id');

        $person = ResponsiblePerson::create(['name' => 'Mr. Test']);

        $this->assertEquals($this->instA->id, $person->institution_id);
    }
}
