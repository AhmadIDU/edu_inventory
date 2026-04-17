<?php

namespace Database\Seeders;

use App\Models\AssetStatus;
use Illuminate\Database\Seeder;

class AssetStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Active / In Use',      'color' => '#22c55e', 'is_system' => true],
            ['name' => 'In Storage',            'color' => '#3b82f6', 'is_system' => true],
            ['name' => 'Under Maintenance',     'color' => '#f59e0b', 'is_system' => true],
            ['name' => 'Disposed',              'color' => '#ef4444', 'is_system' => true],
        ];

        foreach ($statuses as $status) {
            AssetStatus::withoutGlobalScopes()->firstOrCreate(
                ['name' => $status['name'], 'institution_id' => null],
                $status
            );
        }
    }
}
