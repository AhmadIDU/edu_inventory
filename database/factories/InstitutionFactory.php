<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory
{
    protected $model = Institution::class;

    public function definition(): array
    {
        return [
            'name'      => fake()->company(),
            'email'     => fake()->unique()->companyEmail(),
            'phone'     => fake()->phoneNumber(),
            'address'   => fake()->address(),
            'website'   => fake()->url(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
