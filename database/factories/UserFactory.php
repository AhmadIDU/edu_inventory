<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institution_id'    => null,
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
        ];
    }

    public function superAdmin(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
            $user->assignRole($role);
        });
    }

    public function institutionAdmin(\App\Models\Institution $institution): static
    {
        return $this->state(['institution_id' => $institution->id])
            ->afterCreating(function (User $user) {
                $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'institution_admin', 'guard_name' => 'web']);
                $user->assignRole($role);
            });
    }
}
