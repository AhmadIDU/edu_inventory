<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\AssetStatus;
use App\Models\Branch;
use App\Models\Institution;
use App\Models\ResponsiblePerson;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    private array $assetNames = [
        'Laptop', 'Desktop PC', 'Monitor', 'Keyboard', 'Mouse',
        'Projector', 'Smart Board', 'Printer', 'Scanner', 'Webcam',
        'Chair', 'Desk', 'Bookshelf', 'Cabinet', 'Whiteboard',
        'Lab Microscope', 'Bunsen Burner', 'Test Kit', 'Precision Scale', 'Digital Timer',
        'TV Screen', 'Speaker System', 'Amplifier', 'Microphone', 'Headset',
        'Router', 'Switch', 'UPS Battery', 'External HDD', 'USB Hub',
    ];

    private array $categoryDefs = [
        ['IT Equipment',      'Computers, laptops, and peripherals'],
        ['Furniture',         'Desks, chairs, and storage units'],
        ['Lab Equipment',     'Scientific tools and instruments'],
        ['Audio/Visual',      'Projectors, screens, and sound systems'],
        ['Books & Resources', 'Textbooks, reference materials, and educational kits'],
    ];

    private array $roomTypes = [
        'Classroom', 'Lab', 'Office', 'Library', 'Storage Room',
        'Computer Lab', 'Science Room', 'Staff Room', 'Conference Room', 'Workshop',
    ];

    private array $positions = [
        'Teacher', 'Lab Assistant', 'IT Staff', 'Coordinator', 'Administrator',
        'Department Head', 'Support Staff', 'Librarian',
    ];

    private array $firstNames = ['James', 'Maria', 'John', 'Sarah', 'David', 'Emily', 'Michael', 'Lisa', 'Robert', 'Anna'];
    private array $lastNames  = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Wilson', 'Taylor'];
    private array $sentences  = [
        'Needs replacement soon.',
        'Purchased with grant funding.',
        'Requires annual calibration.',
        'Shared between departments.',
        'Under warranty until next year.',
        'Donated by parent association.',
        'Used for advanced classes only.',
    ];

    public function run(): void
    {
        $statuses = AssetStatus::withoutGlobalScopes()->get();

        if ($statuses->isEmpty()) {
            $this->command->error('No asset statuses found. Run AssetStatusSeeder first.');
            return;
        }

        // Institution 1 — 2 branches
        $this->seedInstitution(
            name: 'Greenwood Academy',
            adminEmail: 'admin@greenwood.edu',
            branches: ['Main Campus', 'Science Wing'],
        );

        // Institution 2 — 3 branches
        $this->seedInstitution(
            name: 'Riverside Institute',
            adminEmail: 'admin@riverside.edu',
            branches: ['North Campus', 'South Campus', 'Arts Block'],
        );

        // Institution 3 — NO branches
        $this->seedInstitution(
            name: 'Sunrise Elementary',
            adminEmail: 'admin@sunrise.edu',
            branches: [],
        );
    }

    private function seedInstitution(string $name, string $adminEmail, array $branches): void
    {
        $this->command->info("Seeding institution: {$name}");

        // ── Institution ───────────────────────────────────────────────────────
        $institution = Institution::create([
            'name'      => $name,
            'email'     => $adminEmail,
            'phone'     => '+1-' . rand(200, 999) . '-' . rand(200, 999) . '-' . rand(1000, 9999),
            'address'   => rand(100, 999) . ' Main St, City, ST ' . rand(10000, 99999),
            'is_active' => true,
        ]);

        // ── Institution admin user ────────────────────────────────────────────
        $adminUser = User::create([
            'name'           => "{$name} Admin",
            'email'          => $adminEmail,
            'password'       => Hash::make('password'),
            'institution_id' => $institution->id,
        ]);
        $role = \Spatie\Permission\Models\Role::where('name', 'institution_admin')->first();
        if ($role) {
            $adminUser->assignRole($role);
        }

        // ── Responsible persons (5) ───────────────────────────────────────────
        $personIds = [];
        for ($i = 0; $i < 5; $i++) {
            $person = ResponsiblePerson::create([
                'institution_id' => $institution->id,
                'name'           => $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)],
                'contact'        => '+1-' . rand(200, 999) . '-' . rand(200, 999) . '-' . rand(1000, 9999),
                'position'       => $this->positions[array_rand($this->positions)],
            ]);
            $personIds[] = $person->id;
        }

        // ── Categories (5) ───────────────────────────────────────────────────
        $categoryIds = [];
        foreach ($this->categoryDefs as [$catName, $catDesc]) {
            $cat = AssetCategory::create([
                'institution_id' => $institution->id,
                'name'           => $catName,
                'description'    => $catDesc,
            ]);
            $categoryIds[] = $cat->id;
        }

        // ── Branches + 10 rooms ──────────────────────────────────────────────
        $roomIds = [];

        if (empty($branches)) {
            // No branches — rooms belong directly to the institution
            for ($r = 1; $r <= 10; $r++) {
                $room = Room::create([
                    'institution_id'        => $institution->id,
                    'branch_id'             => null,
                    'responsible_person_id' => $personIds[array_rand($personIds)],
                    'name'                  => $this->roomTypes[$r - 1],
                    'room_number'           => sprintf('R%02d', $r),
                ]);
                $roomIds[] = $room->id;
            }
        } else {
            $roomCounter = 0;
            $roomsLeft   = 10;
            $branchCount = count($branches);

            foreach ($branches as $idx => $branchName) {
                $branch = Branch::create([
                    'institution_id' => $institution->id,
                    'name'           => $branchName,
                    'address'        => rand(100, 999) . ' ' . $branchName . ' Ave, City, ST ' . rand(10000, 99999),
                    'is_active'      => true,
                ]);

                // Distribute rooms evenly; last branch gets whatever remains
                $isLast     = ($idx === $branchCount - 1);
                $roomsHere  = $isLast ? $roomsLeft : (int) floor(10 / $branchCount);
                $roomsLeft -= $roomsHere;

                for ($r = 1; $r <= $roomsHere; $r++) {
                    $roomCounter++;
                    $room = Room::create([
                        'institution_id'        => $institution->id,
                        'branch_id'             => $branch->id,
                        'responsible_person_id' => $personIds[array_rand($personIds)],
                        'name'                  => $this->roomTypes[($roomCounter - 1) % 10],
                        'room_number'           => sprintf('%d%02d', $idx + 1, $r),
                    ]);
                    $roomIds[] = $room->id;
                }
            }
        }

        // ── Assets — 100 per room, bulk insert ───────────────────────────────
        $totalAssets = count($roomIds) * 100;
        $this->command->line("  Inserting {$totalAssets} assets...");

        $statusIds  = AssetStatus::withoutGlobalScopes()->pluck('id')->toArray();
        $now        = now()->toDateTimeString();
        $assetRows  = [];

        foreach ($roomIds as $roomId) {
            for ($a = 0; $a < 100; $a++) {
                $assetRows[] = [
                    'institution_id' => $institution->id,
                    'room_id'        => $roomId,
                    'category_id'    => $categoryIds[array_rand($categoryIds)],
                    'status_id'      => $statusIds[array_rand($statusIds)],
                    'name'           => $this->assetNames[array_rand($this->assetNames)] . ' ' . strtoupper(Str::random(4)),
                    'serial_number'  => strtoupper(Str::random(3)) . rand(10000, 99999),
                    'qr_code'        => Str::uuid()->toString(),
                    'purchase_date'  => date('Y-m-d', strtotime('-' . rand(0, 1095) . ' days')),
                    'purchase_value' => round(rand(50, 500000) / 100, 2),
                    'notes'          => rand(1, 10) <= 3 ? $this->sentences[array_rand($this->sentences)] : null,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
        }

        // Insert in chunks of 500 for performance
        foreach (array_chunk($assetRows, 500) as $chunk) {
            DB::table('assets')->insert($chunk);
        }

        // ── Transfer logs — 10 per institution ───────────────────────────────
        $this->command->line('  Inserting 10 transfer logs...');

        $allAssetIds = DB::table('assets')
            ->where('institution_id', $institution->id)
            ->pluck('id')
            ->toArray();

        $transferRows = [];
        for ($t = 0; $t < 10; $t++) {
            $fromRoomId = $roomIds[array_rand($roomIds)];
            $toRoomId   = $roomIds[array_rand($roomIds)];

            // Ensure from != to when more than 1 room exists
            $attempts = 0;
            while ($toRoomId === $fromRoomId && count($roomIds) > 1 && $attempts < 10) {
                $toRoomId = $roomIds[array_rand($roomIds)];
                $attempts++;
            }

            $transferRows[] = [
                'asset_id'       => $allAssetIds[array_rand($allAssetIds)],
                'institution_id' => $institution->id,
                'from_room_id'   => $fromRoomId,
                'to_room_id'     => $toRoomId,
                'transferred_by' => $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)],
                'transfer_date'  => date('Y-m-d', strtotime('-' . rand(0, 365) . ' days')),
                'notes'          => rand(1, 10) <= 6 ? $this->sentences[array_rand($this->sentences)] : null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        DB::table('asset_transfers')->insert($transferRows);

        $this->command->info("  Done. Rooms: " . count($roomIds) . " | Assets: {$totalAssets} | Transfers: 10");
    }
}
