<?php

namespace Database\Factories;

use App\Models\AssetStatus;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetStatusFactory extends Factory
{
    protected $model = AssetStatus::class;

    public function definition(): array
    {
        return [
            'institution_id' => null,
            'name'           => fake()->randomElement(['Active / In Use', 'In Storage', 'Under Maintenance', 'Disposed']),
            'color'          => fake()->hexColor(),
            'is_system'      => false,
        ];
    }

    public function system(): static
    {
        return $this->state(['institution_id' => null, 'is_system' => true]);
    }

    public function forInstitution(Institution $institution): static
    {
        return $this->state(['institution_id' => $institution->id, 'is_system' => false]);
    }
}
