<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetStatus;
use App\Models\Branch;
use App\Models\Institution;
use App\Models\Room;
use App\Models\User;
use App\Scopes\InstitutionScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_scope_filters_by_current_institution_id_binding(): void
    {
        $instA = $this->createInstitution();
        $instB = $this->createInstitution();

        AssetCategory::withoutGlobalScopes()->create(['institution_id' => $instA->id, 'name' => 'Cat A']);
        AssetCategory::withoutGlobalScopes()->create(['institution_id' => $instB->id, 'name' => 'Cat B']);

        app()->instance('current_institution_id', $instA->id);

        $results = AssetCategory::all();

        $this->assertCount(1, $results);
        $this->assertEquals('Cat A', $results->first()->name);

        app()->forgetInstance('current_institution_id');
    }

    public function test_scope_reads_from_authenticated_user_when_no_binding(): void
    {
        $instA = $this->createInstitution();
        $instB = $this->createInstitution();

        AssetCategory::withoutGlobalScopes()->create(['institution_id' => $instA->id, 'name' => 'Cat A']);
        AssetCategory::withoutGlobalScopes()->create(['institution_id' => $instB->id, 'name' => 'Cat B']);

        $admin = $this->createInstitutionAdmin($instA);
        $this->actingAs($admin);

        // No container binding — scope must fall back to auth()->user()->institution_id
        $results = AssetCategory::all();

        $this->assertCount(1, $results);
        $this->assertEquals('Cat A', $results->first()->name);

        app()->forgetInstance('current_institution_id');
    }

    public function test_scope_is_bypassed_when_no_institution_context(): void
    {
        $instA = $this->createInstitution();
        $instB = $this->createInstitution();

        AssetCategory::withoutGlobalScopes()->create(['institution_id' => $instA->id, 'name' => 'Cat A']);
        AssetCategory::withoutGlobalScopes()->create(['institution_id' => $instB->id, 'name' => 'Cat B']);

        // No auth, no binding — super admin context: all records visible
        $results = AssetCategory::withoutGlobalScopes()->get();

        $this->assertCount(2, $results);
    }

    public function test_resolve_institution_id_caches_in_container(): void
    {
        $instA = $this->createInstitution();
        $admin = $this->createInstitutionAdmin($instA);
        $this->actingAs($admin);

        // First call should resolve from auth() and cache it
        $id = InstitutionScope::resolveInstitutionId();
        $this->assertEquals($instA->id, $id);

        // Container binding should now exist
        $this->assertTrue(app()->has('current_institution_id'));
        $this->assertEquals($instA->id, app('current_institution_id'));

        app()->forgetInstance('current_institution_id');
    }
}
