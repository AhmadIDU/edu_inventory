<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetStatus;
use App\Models\Institution;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'room_id'        => Room::factory(),
            'category_id'    => null,
            'status_id'      => AssetStatus::factory()->system(),
            'name'           => fake()->randomElement(['Laptop', 'Projector', 'Desk', 'Chair', 'Printer', 'Monitor']) . ' ' . fake()->bothify('???-###'),
            'serial_number'  => strtoupper(fake()->bothify('??###??###')),
            'qr_code'        => Str::uuid()->toString(),
            'purchase_date'  => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'purchase_value' => fake()->randomFloat(2, 50, 5000),
            'notes'          => fake()->optional()->sentence(),
        ];
    }

    /**
     * Create a fully consistent asset: institution → room → category → status all share the same institution.
     */
    public function forInstitution(Institution $institution, Room $room, AssetStatus $status, ?AssetCategory $category = null): static
    {
        return $this->state([
            'institution_id' => $institution->id,
            'room_id'        => $room->id,
            'status_id'      => $status->id,
            'category_id'    => $category?->id,
        ]);
    }
}
