<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Institution;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        $institution = Institution::factory()->create();

        return [
            'institution_id'        => $institution->id,
            'branch_id'             => null,
            'responsible_person_id' => null,
            'name'                  => fake()->randomElement(['Lab', 'Classroom', 'Office', 'Storage', 'Library']) . ' ' . fake()->numberBetween(1, 50),
            'room_number'           => (string) fake()->numerify('##-##'),
        ];
    }

    public function withBranch(Branch $branch): static
    {
        return $this->state([
            'institution_id' => $branch->institution_id,
            'branch_id'      => $branch->id,
        ]);
    }
}
