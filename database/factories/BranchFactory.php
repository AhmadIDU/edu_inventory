<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name'           => fake()->city() . ' Branch',
            'address'        => fake()->address(),
            'is_active'      => true,
        ];
    }
}
