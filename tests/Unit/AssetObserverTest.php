<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetStatus;
use App\Models\Institution;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AssetObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Storage::fake('public');
    }

    public function test_qr_code_token_is_generated_on_create(): void
    {
        ['asset' => $asset] = $this->makeAsset();

        $this->assertNotNull($asset->qr_code);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $asset->qr_code
        );
    }

    public function test_qr_svg_file_is_saved_on_create(): void
    {
        ['asset' => $asset] = $this->makeAsset();

        Storage::disk('public')->assertExists('qrcodes/' . $asset->qr_code . '.svg');
    }

    public function test_qr_svg_file_is_deleted_when_asset_is_deleted(): void
    {
        ['asset' => $asset] = $this->makeAsset();
        $token = $asset->qr_code;

        Storage::disk('public')->assertExists('qrcodes/' . $token . '.svg');

        $asset->delete();

        Storage::disk('public')->assertMissing('qrcodes/' . $token . '.svg');
    }

    public function test_existing_qr_code_is_not_overwritten_on_create(): void
    {
        $customToken = Str::uuid()->toString();
        ['inst' => $inst, 'room' => $room, 'status' => $status] = $this->makeAssetDependencies();

        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $inst->id,
            'room_id'        => $room->id,
            'status_id'      => $status->id,
            'name'           => 'Test',
            'qr_code'        => $customToken,
        ]);

        $this->assertEquals($customToken, $asset->qr_code);
    }

    public function test_qr_image_url_attribute_points_to_svg(): void
    {
        ['asset' => $asset] = $this->makeAsset();

        $this->assertStringEndsWith($asset->qr_code . '.svg', $asset->qr_image_url);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function makeAssetDependencies(): array
    {
        $inst   = $this->createInstitution();
        $room   = Room::withoutGlobalScopes()->create(['institution_id' => $inst->id, 'name' => 'Room']);
        $status = AssetStatus::withoutGlobalScopes()->create(['name' => 'Active', 'color' => '#0f0', 'is_system' => true]);

        return compact('inst', 'room', 'status');
    }

    private function makeAsset(): array
    {
        $inst   = $this->createInstitution();
        $room   = Room::withoutGlobalScopes()->create(['institution_id' => $inst->id, 'name' => 'Room']);
        $status = AssetStatus::withoutGlobalScopes()->create(['name' => 'Active', 'color' => '#0f0', 'is_system' => true]);

        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $inst->id,
            'room_id'        => $room->id,
            'status_id'      => $status->id,
            'name'           => 'Test Laptop',
        ]);

        return compact('asset', 'inst', 'room', 'status');
    }
}
