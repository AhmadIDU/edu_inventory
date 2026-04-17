<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\ResponsiblePerson;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResponsiblePersonFactory extends Factory
{
    protected $model = ResponsiblePerson::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name'           => fake()->name(),
            'contact'        => fake()->phoneNumber(),
            'position'       => fake()->jobTitle(),
        ];
    }
}
