<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetTransfer;
use App\Models\Institution;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetTransferFactory extends Factory
{
    protected $model = AssetTransfer::class;

    public function definition(): array
    {
        $institution = Institution::factory()->create();

        return [
            'asset_id'        => Asset::factory(),
            'institution_id'  => $institution->id,
            'from_room_id'    => null,
            'to_room_id'      => Room::factory()->create(['institution_id' => $institution->id])->id,
            'transferred_by'  => fake()->name(),
            'transfer_date'   => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'notes'           => fake()->optional()->sentence(),
        ];
    }
}
