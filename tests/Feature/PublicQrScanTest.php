<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetStatus;
use App\Models\Branch;
use App\Models\Institution;
use App\Models\ResponsiblePerson;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicQrScanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        Storage::fake('public');
    }

    public function test_scan_page_returns_200_for_valid_token(): void
    {
        $asset = $this->makeAsset();

        $response = $this->get(route('asset.scan', $asset->qr_code));

        $response->assertOk();
    }

    public function test_scan_page_returns_404_for_unknown_token(): void
    {
        $response = $this->get(route('asset.scan', Str::uuid()));

        $response->assertNotFound();
    }

    public function test_scan_page_shows_institution_name(): void
    {
        $asset = $this->makeAsset();

        $response = $this->get(route('asset.scan', $asset->qr_code));

        $response->assertSee($asset->room->institution->name);
    }

    public function test_scan_page_shows_asset_name(): void
    {
        $asset = $this->makeAsset(['name' => 'Special Microscope']);

        $response = $this->get(route('asset.scan', $asset->qr_code));

        $response->assertSee('Special Microscope');
    }

    public function test_scan_page_shows_room_name(): void
    {
        $asset = $this->makeAsset();

        $response = $this->get(route('asset.scan', $asset->qr_code));

        $response->assertSee($asset->room->name);
    }

    public function test_scan_page_shows_branch_name_when_present(): void
    {
        $institution = $this->createInstitution();
        $branch      = Branch::withoutGlobalScopes()->create(['institution_id' => $institution->id, 'name' => 'Main Campus']);
        $room        = Room::withoutGlobalScopes()->create(['institution_id' => $institution->id, 'branch_id' => $branch->id, 'name' => 'Lab 3']);
        $status      = AssetStatus::withoutGlobalScopes()->create(['name' => 'Active', 'color' => '#0f0', 'is_system' => true]);

        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $institution->id,
            'room_id'        => $room->id,
            'status_id'      => $status->id,
            'name'           => 'Laptop',
            'qr_code'        => Str::uuid(),
        ]);

        $response = $this->get(route('asset.scan', $asset->qr_code));

        $response->assertSee('Main Campus');
    }

    public function test_scan_page_shows_responsible_person_when_assigned(): void
    {
        $institution = $this->createInstitution();
        $person      = ResponsiblePerson::withoutGlobalScopes()->create([
            'institution_id' => $institution->id,
            'name'           => 'Dr. Smith',
            'contact'        => '+998901234567',
        ]);
        $room   = Room::withoutGlobalScopes()->create([
            'institution_id'        => $institution->id,
            'responsible_person_id' => $person->id,
            'name'                  => 'Office',
        ]);
        $status = AssetStatus::withoutGlobalScopes()->create(['name' => 'Active', 'color' => '#0f0', 'is_system' => true]);

        $asset = Asset::withoutGlobalScopes()->create([
            'institution_id' => $institution->id,
            'room_id'        => $room->id,
            'status_id'      => $status->id,
            'name'           => 'PC',
            'qr_code'        => Str::uuid(),
        ]);

        $response = $this->get(route('asset.scan', $asset->qr_code));

        $response->assertSee('Dr. Smith');
        $response->assertSee('+998901234567');
    }

    public function test_scan_page_shows_serial_number_when_present(): void
    {
        $asset = $this->makeAsset(['serial_number' => 'SN-UNIQUE-XYZ']);

        $response = $this->get(route('asset.scan', $asset->qr_code));

        $response->assertSee('SN-UNIQUE-XYZ');
    }

    public function test_scan_page_accessible_without_authentication(): void
    {
        $asset = $this->makeAsset();

        // Not logged in — public page must still work
        $response = $this->get(route('asset.scan', $asset->qr_code));

        $response->assertOk();
    }

    public function test_qr_download_returns_svg_file(): void
    {
        $asset = $this->makeAsset();

        // Put a fake SVG file so the download controller can find it
        Storage::disk('public')->put('qrcodes/' . $asset->qr_code . '.svg', '<svg></svg>');

        $response = $this->get(route('asset.qr.download', $asset->qr_code));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function makeAsset(array $overrides = []): Asset
    {
        $institution = $this->createInstitution();
        $room        = Room::withoutGlobalScopes()->create(['institution_id' => $institution->id, 'name' => 'Test Room']);
        $status      = AssetStatus::withoutGlobalScopes()->create(['name' => 'Active', 'color' => '#0f0', 'is_system' => true]);

        return Asset::withoutGlobalScopes()->create(array_merge([
            'institution_id' => $institution->id,
            'room_id'        => $room->id,
            'status_id'      => $status->id,
            'name'           => 'Test Asset',
            'qr_code'        => Str::uuid(),
        ], $overrides));
    }
}
