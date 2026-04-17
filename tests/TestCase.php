<?php

namespace Tests;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected function createSuperAdmin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    protected function createInstitutionAdmin(Institution $institution): User
    {
        return User::factory()->institutionAdmin($institution)->create();
    }

    protected function createInstitution(array $attributes = []): Institution
    {
        return Institution::factory()->create($attributes);
    }

    /** Boot Spatie roles needed by tests without running the seeder. */
    protected function seedRoles(): void
    {
        Role::firstOrCreate(['name' => 'super_admin',       'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'institution_admin', 'guard_name' => 'web']);
    }
}
