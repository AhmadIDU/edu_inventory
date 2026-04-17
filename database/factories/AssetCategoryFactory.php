<?php

namespace Database\Factories;

use App\Models\AssetCategory;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetCategoryFactory extends Factory
{
    protected $model = AssetCategory::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name'           => fake()->randomElement(['IT Equipment', 'Furniture', 'Lab Tools', 'Books', 'Audio/Visual']),
            'description'    => fake()->sentence(),
        ];
    }
}
