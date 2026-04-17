# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Common Commands

```bash
# Development server
php artisan serve

# Run all tests (SQLite in-memory, no DB setup needed)
php artisan test

# Run a single test file
php artisan test tests/Unit/TransferServiceTest.php

# Run a single test by name
php artisan test --filter="transfer moves asset to new room"

# Fresh migration + all seeders (super admin + system statuses + 3 demo institutions)
php artisan migrate:fresh --seed

# Seed only demo data (requires SuperAdminSeeder + AssetStatusSeeder to have run first)
php artisan db:seed --class=DemoSeeder

# Code style (Laravel Pint)
./vendor/bin/pint

# Link storage for QR SVG files to be publicly accessible
php artisan storage:link
```

## Architecture Overview

### Multi-Tenancy Model
All tenant data is isolated by `institution_id`. Three layers enforce this:

1. **`InstitutionScope`** (`app/Scopes/InstitutionScope.php`) — global Eloquent scope on every tenant model. Reads institution ID from the service container (`current_institution_id` binding) first, then falls back to `auth()->user()->institution_id`. Does nothing if neither is set (super admin context).

2. **`SetTenantScope` middleware** (`app/Http/Middleware/SetTenantScope.php`) — registered in the `/admin` panel's `authMiddleware`. Binds `current_institution_id` into the container per request. The fallback in `InstitutionScope` exists because Livewire POST requests sometimes bypass this middleware.

3. **Filament Shield policies** — auto-generated; each policy checks `$user->institution_id === $model->institution_id`.

All tenant models call `InstitutionScope::resolveInstitutionId()` in their `creating` callback to auto-stamp `institution_id` on new records. Always use `withoutGlobalScopes()` in seeders, observers, and public controllers.

### Two Filament Panels
- **`/super`** — `SuperAdminPanelProvider`, panel ID `super-admin`, default panel. No tenant scope. Resources: `InstitutionResource`, `UserResource`. Accessed by `super_admin` role only.
- **`/admin`** — `AdminPanelProvider`, panel ID `admin`. Adds `SetTenantScope` to `authMiddleware`. Resources: `BranchResource`, `RoomResource`, `ResponsiblePersonResource`, `AssetCategoryResource`, `AssetStatusResource`, `AssetResource`, `AssetTransferResource`. Accessed by `institution_admin` role only.

Roles are managed by Spatie Laravel Permission. `User::canAccessPanel()` gates which panel a user may enter.

### Data Hierarchy
```
Institution
  ├── Branch (optional — branch_id is nullable on Room)
  │     └── Room
  └── Room (branch_id = null, belongs directly to institution)
        └── Asset (unique qr_code UUID)
              └── AssetTransfer (log only — never delete)

Institution
  └── ResponsiblePerson (NOT a User — managed separately, assigned to rooms)
```

### AssetStatus Special Scope
`AssetStatus` uses a **named** global scope (`institution_statuses`), not `InstitutionScope`. It shows rows where `institution_id IS NULL` (4 system defaults seeded by `AssetStatusSeeder`) **OR** `institution_id = current`. System defaults have `is_system = true` and `institution_id = null`.

### QR Code Flow
- **Generation**: `AssetObserver::creating()` sets `qr_code` UUID; `created()` generates an SVG via `simplesoftwareio/simple-qrcode` and saves it to `storage/app/public/qrcodes/{uuid}.svg`. Use SVG format only — PNG requires the `imagick` PHP extension which is not installed.
- **Public scan**: `GET /scan/{token}` — no auth, uses `Asset::withoutGlobalScopes()`, served by `AssetScanController`.
- **Download**: `GET /qr/{token}` returns the SVG file.
- **Print label**: `GET /label/{token}` renders a dompdf PDF via `AssetLabelController`.
- **Bulk seeding**: Use `DB::table('assets')->insert()` to bypass the observer. QR files will not be generated for bulk-inserted assets.

### Asset Transfers
Always use `TransferService::transfer()` — it wraps the `AssetTransfer` log creation and `Asset.room_id` update in a single DB transaction. Never update `room_id` directly without creating a transfer log.

### Filament 5 Conventions
- `form()` signature: `public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema`
- Use `$schema->components([...])` not `$form->schema([...])`
- `$navigationGroup` type: `protected static string | \UnitEnum | null $navigationGroup`
- Widget `$heading` must be non-static
- Dashboard `getColumns()` return type must be `int | array`

### Testing
- Tests use SQLite in-memory (`phpunit.xml`). Never require a running PostgreSQL instance.
- Base `TestCase` provides `createSuperAdmin()`, `createInstitutionAdmin(Institution)`, `createInstitution()`, and `seedRoles()`.
- Call `seedRoles()` in `setUp()` for any test that creates users with roles.
- Call `Storage::fake('public')` in `setUp()` for any test that triggers `AssetObserver`.
- Use `Asset::withoutGlobalScopes()->create([...])` in tests to bypass the institution scope.
- Reset the container binding between tests: `app()->forgetInstance('current_institution_id')`.

### Seeder Accounts
After `migrate:fresh --seed`:
- Super admin: `superadmin@eduinventory.com` / `password`
- Demo institution admins: `admin@greenwood.edu`, `admin@riverside.edu`, `admin@sunrise.edu` / `password`
