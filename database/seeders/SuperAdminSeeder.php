<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'institution_admin', 'guard_name' => 'web']);

        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@eduinventory.com'],
            [
                'name'             => 'Super Admin',
                'password'         => bcrypt('password'),
                'institution_id'   => null,
            ]
        );

        $superAdmin->assignRole($superAdminRole);
    }
}
