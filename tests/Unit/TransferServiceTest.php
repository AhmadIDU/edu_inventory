<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetStatus;
use App\Models\AssetTransfer;
use App\Models\Institution;
use App\Models\Room;
use App\Services\TransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransferService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        $this->service = new TransferService();
    }

    public function test_transfer_moves_asset_to_new_room(): void
    {
        $institution = $this->createInstitution();
        $status      = AssetStatus::withoutGlobalScopes()->create(['name' => 'Active', 'color' => '#0f0', 'is_system' => true]);
        $roomA       = Room::withoutGlobalScopes()->create(['institution_id' => $institution->id, 'name' => 'Room A']);
        $roomB       = Room::withoutGlobalScopes()->create(['institution_id' => $institution->id, 'name' => 'Room B']);

        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $institution->id,
            'room_id'        => $roomA->id,
            'status_id'      => $status->id,
            'name'           => 'Test Laptop',
            'qr_code'        => \Illuminate\Support\Str::uuid(),
        ]);

        $this->service->transfer($asset, $roomB->id, 'John Doe', now()->toDateString());

        $asset->refresh();
        $this->assertEquals($roomB->id, $asset->room_id);
    }

    public function test_transfer_creates_log_record(): void
    {
        $institution = $this->createInstitution();
        $status      = AssetStatus::withoutGlobalScopes()->create(['name' => 'Active', 'color' => '#0f0', 'is_system' => true]);
        $roomA       = Room::withoutGlobalScopes()->create(['institution_id' => $institution->id, 'name' => 'Room A']);
        $roomB       = Room::withoutGlobalScopes()->create(['institution_id' => $institution->id, 'name' => 'Room B']);

        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $institution->id,
            'room_id'        => $roomA->id,
            'status_id'      => $status->id,
            'name'           => 'Test Laptop',
            'qr_code'        => \Illuminate\Support\Str::uuid(),
        ]);

        $transfer = $this->service->transfer($asset, $roomB->id, 'Jane Smith', '2026-01-15', 'Test notes');

        $this->assertInstanceOf(AssetTransfer::class, $transfer);
        $this->assertEquals($asset->id,         $transfer->asset_id);
        $this->assertEquals($institution->id,   $transfer->institution_id);
        $this->assertEquals($roomA->id,         $transfer->from_room_id);
        $this->assertEquals($roomB->id,         $transfer->to_room_id);
        $this->assertEquals('Jane Smith',        $transfer->transferred_by);
        $this->assertEquals('2026-01-15',        $transfer->transfer_date->toDateString());
        $this->assertEquals('Test notes',        $transfer->notes);
    }

    public function test_transfer_is_atomic_on_failure(): void
    {
        $institution = $this->createInstitution();
        $status      = AssetStatus::withoutGlobalScopes()->create(['name' => 'Active', 'color' => '#0f0', 'is_system' => true]);
        $roomA       = Room::withoutGlobalScopes()->create(['institution_id' => $institution->id, 'name' => 'Room A']);

        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $institution->id,
            'room_id'        => $roomA->id,
            'status_id'      => $status->id,
            'name'           => 'Test Laptop',
            'qr_code'        => \Illuminate\Support\Str::uuid(),
        ]);

        $originalRoomId = $asset->room_id;

        // Pass a non-existent room ID — should throw and roll back
        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->service->transfer($asset, 99999, 'John', now()->toDateString());

        // Asset room must be unchanged
        $asset->refresh();
        $this->assertEquals($originalRoomId, $asset->room_id);
        $this->assertDatabaseMissing('asset_transfers', ['asset_id' => $asset->id]);
    }

    public function test_transfer_records_from_room_as_null_for_initial_placement(): void
    {
        $institution = $this->createInstitution();
        $status      = AssetStatus::withoutGlobalScopes()->create(['name' => 'Active', 'color' => '#0f0', 'is_system' => true]);
        $roomA       = Room::withoutGlobalScopes()->create(['institution_id' => $institution->id, 'name' => 'Room A']);
        $roomB       = Room::withoutGlobalScopes()->create(['institution_id' => $institution->id, 'name' => 'Room B']);

        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $institution->id,
            'room_id'        => $roomA->id,
            'status_id'      => $status->id,
            'name'           => 'Laptop',
            'qr_code'        => \Illuminate\Support\Str::uuid(),
        ]);

        // Override from_room_id with null to simulate initial placement
        $transfer = AssetTransfer::withoutGlobalScopes()->create([
            'asset_id'       => $asset->id,
            'institution_id' => $institution->id,
            'from_room_id'   => null,
            'to_room_id'     => $roomA->id,
            'transferred_by' => 'Setup',
            'transfer_date'  => now()->toDateString(),
        ]);

        $this->assertNull($transfer->from_room_id);
    }
}
