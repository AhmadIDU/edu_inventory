<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetStatus;
use App\Models\Institution;
use App\Models\Room;
use App\Services\TransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AssetManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;
    protected Room $room;
    protected AssetStatus $status;
    protected AssetCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Storage::fake('public');

        $this->institution = $this->createInstitution();
        $this->room        = Room::withoutGlobalScopes()->create(['institution_id' => $this->institution->id, 'name' => 'Lab 1']);
        $this->status      = AssetStatus::withoutGlobalScopes()->create(['name' => 'Active', 'color' => '#22c55e', 'is_system' => true]);
        $this->category    = AssetCategory::withoutGlobalScopes()->create(['institution_id' => $this->institution->id, 'name' => 'IT Equipment']);
    }

    public function test_asset_can_be_created_with_required_fields(): void
    {
        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $this->institution->id,
            'room_id'        => $this->room->id,
            'status_id'      => $this->status->id,
            'name'           => 'Dell Laptop',
            'serial_number'  => 'SN-12345',
            'qr_code'        => Str::uuid(),
        ]);

        $this->assertDatabaseHas('assets', [
            'name'           => 'Dell Laptop',
            'serial_number'  => 'SN-12345',
            'institution_id' => $this->institution->id,
        ]);
    }

    public function test_asset_generates_unique_qr_code_per_asset(): void
    {
        $asset1 = Asset::withoutGlobalScopes()->create([
            'institution_id' => $this->institution->id,
            'room_id'        => $this->room->id,
            'status_id'      => $this->status->id,
            'name'           => 'Laptop 1',
        ]);
        $asset2 = Asset::withoutGlobalScopes()->create([
            'institution_id' => $this->institution->id,
            'room_id'        => $this->room->id,
            'status_id'      => $this->status->id,
            'name'           => 'Laptop 2',
        ]);

        $this->assertNotEquals($asset1->qr_code, $asset2->qr_code);
    }

    public function test_asset_qr_svg_file_exists_after_creation(): void
    {
        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $this->institution->id,
            'room_id'        => $this->room->id,
            'status_id'      => $this->status->id,
            'name'           => 'Test Asset',
        ]);

        Storage::disk('public')->assertExists('qrcodes/' . $asset->qr_code . '.svg');
    }

    public function test_asset_qr_svg_deleted_when_asset_deleted(): void
    {
        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $this->institution->id,
            'room_id'        => $this->room->id,
            'status_id'      => $this->status->id,
            'name'           => 'Laptop',
        ]);

        $token = $asset->qr_code;
        $asset->delete();

        Storage::disk('public')->assertMissing('qrcodes/' . $token . '.svg');
    }

    public function test_asset_relationships_are_accessible(): void
    {
        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $this->institution->id,
            'room_id'        => $this->room->id,
            'status_id'      => $this->status->id,
            'category_id'    => $this->category->id,
            'name'           => 'Monitor',
            'qr_code'        => Str::uuid(),
        ]);

        $fresh = Asset::withoutGlobalScopes()->with(['room', 'status', 'category', 'institution'])->find($asset->id);

        $this->assertEquals('Lab 1',        $fresh->room->name);
        $this->assertEquals('Active',       $fresh->status->name);
        $this->assertEquals('IT Equipment', $fresh->category->name);
        $this->assertEquals($this->institution->id, $fresh->institution->id);
    }

    public function test_asset_can_be_transferred_between_rooms(): void
    {
        $roomB = Room::withoutGlobalScopes()->create(['institution_id' => $this->institution->id, 'name' => 'Storage']);

        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $this->institution->id,
            'room_id'        => $this->room->id,
            'status_id'      => $this->status->id,
            'name'           => 'Projector',
            'qr_code'        => Str::uuid(),
        ]);

        $service = new TransferService();
        $service->transfer($asset, $roomB->id, 'Admin User', now()->toDateString(), 'Moving to storage');

        $asset->refresh();
        $this->assertEquals($roomB->id, $asset->room_id);
        $this->assertDatabaseHas('asset_transfers', [
            'asset_id'       => $asset->id,
            'from_room_id'   => $this->room->id,
            'to_room_id'     => $roomB->id,
            'transferred_by' => 'Admin User',
        ]);
    }

    public function test_multiple_transfers_are_recorded_in_history(): void
    {
        $roomB   = Room::withoutGlobalScopes()->create(['institution_id' => $this->institution->id, 'name' => 'Room B']);
        $roomC   = Room::withoutGlobalScopes()->create(['institution_id' => $this->institution->id, 'name' => 'Room C']);
        $service = new TransferService();

        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $this->institution->id,
            'room_id'        => $this->room->id,
            'status_id'      => $this->status->id,
            'name'           => 'Printer',
            'qr_code'        => Str::uuid(),
        ]);

        $service->transfer($asset, $roomB->id, 'User A', now()->toDateString());
        $service->transfer($asset, $roomC->id, 'User B', now()->toDateString());

        $this->assertCount(2, $asset->transfers()->withoutGlobalScopes()->get());
    }

    public function test_asset_purchase_value_stored_correctly(): void
    {
        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $this->institution->id,
            'room_id'        => $this->room->id,
            'status_id'      => $this->status->id,
            'name'           => 'Expensive Server',
            'purchase_value' => 12500.99,
            'purchase_date'  => '2025-03-01',
            'qr_code'        => Str::uuid(),
        ]);

        $this->assertEquals('12500.99', number_format($asset->fresh()->purchase_value, 2, '.', ''));
        $this->assertEquals('2025-03-01', $asset->fresh()->purchase_date->toDateString());
    }
}
