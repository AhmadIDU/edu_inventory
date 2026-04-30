# EduInventory — Educational Asset Management System

> A diploma project submitted in partial fulfillment of the requirements for the degree of Bachelor of Science in Software Engineering.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Target Audience & Problem Statement](#target-audience--problem-statement)
- [Technology Stack](#technology-stack)
- [System Architecture](#system-architecture)
- [Multi-Tenancy Model](#multi-tenancy-model)
- [Data Model & Business Logic](#data-model--business-logic)
- [Key Features](#key-features)
- [User Roles & Access Control](#user-roles--access-control)
- [QR Code Subsystem](#qr-code-subsystem)
- [Asset Transfer Workflow](#asset-transfer-workflow)
- [Bulk Import & Export](#bulk-import--export)
- [Testing Strategy](#testing-strategy)
- [Getting Started](#getting-started)
- [Project Structure](#project-structure)
- [License](#license)

---

## Project Overview

**EduInventory** is a web-based asset management system designed specifically for educational institutions. It enables organizations — schools, universities, and training centers — to track, manage, and audit physical assets (furniture, equipment, electronics, etc.) distributed across multiple branches and rooms.

The system is built on a **multi-tenant architecture**, allowing a single deployment to serve multiple independent institutions simultaneously while guaranteeing complete data isolation between them. Each institution manages its own branches, rooms, responsible persons, asset categories, and assets without any visibility into other institutions' data.

A central **Super Administrator** panel provides platform-wide oversight: creating and managing institutions and their administrator accounts, monitoring background jobs, and inspecting failed operations.

---

## Target Audience & Problem Statement

### Who it is built for

| Stakeholder | Role in the system |
|---|---|
| **Platform operator** | Manages all institutions via the Super Admin panel |
| **Institution administrator** | Manages all assets, rooms, transfers, and staff for their institution |
| **Any user with a QR scanner** | Scans an asset's QR code to view its public information — no login required |

### Problem being solved

Educational institutions maintain hundreds to thousands of physical assets spread across classrooms, laboratories, offices, and storage rooms. Traditional methods — paper logs, generic spreadsheets — suffer from:

- **No audit trail**: It is impossible to know where an asset has been or who was responsible for it at a given point in time.
- **No real-time location tracking**: Assets moved between rooms are either not recorded or recorded inconsistently.
- **No scalability**: A single spreadsheet cannot serve multiple branches of an institution.
- **No accountability**: There is no link between a physical object and a digital record that a non-technical user can resolve on the spot.

EduInventory solves all four problems through a structured relational data model, immutable transfer logs, and QR-code-based physical-to-digital linking.

---

## Technology Stack

| Layer | Technology | Version |
|---|---|---|
| **Language** | PHP | ^8.3 |
| **Framework** | Laravel | ^13.0 |
| **Admin UI** | Filament | ^5.5 |
| **Authorization** | Filament Shield (Spatie Laravel Permission) | ^4.2 |
| **QR Code generation** | SimpleSoftwareIO Simple-QrCode | ^4.2 |
| **PDF generation** | barryvdh/laravel-dompdf | ^3.1 |
| **Image processing** | intervention/image | ^4.0 |
| **Frontend build** | Vite + Tailwind CSS | v8 / v4 |
| **Database** | PostgreSQL (production) / SQLite (tests) | — |
| **Testing** | PHPUnit | ^12.5 |
| **Code style** | Laravel Pint | ^1.27 |

### Why Filament?

Filament 5 is a first-class admin panel framework for Laravel that provides a full CRUD interface, form builder, table builder, and widget system with minimal boilerplate. It allowed this project to focus entirely on business logic rather than UI plumbing, while still producing a polished, responsive interface.

### Why multi-tenant SaaS architecture?

A single shared deployment drastically reduces infrastructure costs and operational overhead compared to running a separate application instance per institution. Row-level tenant isolation via Eloquent global scopes is a well-established pattern for Laravel SaaS applications and was chosen over schema-per-tenant or database-per-tenant approaches for its simplicity and compatibility with standard Eloquent tooling.

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        Web Server (nginx)                       │
└───────────────┬──────────────────────────┬──────────────────────┘
                │                          │
        ┌───────▼────────┐       ┌─────────▼────────┐
        │  /super panel  │       │   /admin panel   │
        │  SuperAdmin    │       │  InstitutionAdmin│
        │  (no tenant    │       │  (tenant scoped) │
        │   scope)       │       │                  │
        └───────┬────────┘       └────────┬─────────┘
                │                         │
        ┌───────▼─────────────────────────▼─────────┐
        │              Laravel Application           │
        │                                            │
        │  ┌──────────────┐   ┌────────────────────┐│
        │  │ InstitutionScope│ │ SetTenantScope     ││
        │  │ (Eloquent    │  │ Middleware          ││
        │  │  global scope)│  │ (per-request bind) ││
        │  └──────────────┘   └────────────────────┘│
        │                                            │
        │  ┌──────────────┐   ┌────────────────────┐│
        │  │ TransferService│ │ QrCodeService      ││
        │  └──────────────┘   └────────────────────┘│
        └───────────────────────┬────────────────────┘
                                │
        ┌───────────────────────▼────────────────────┐
        │              PostgreSQL Database            │
        └────────────────────────────────────────────┘

        ┌────────────────────────────────────────────┐
        │            Public Routes (no auth)         │
        │  GET /scan/{token}  — asset info page      │
        │  GET /qr/{token}    — download SVG QR      │
        │  GET /label/{token} — print PDF label      │
        └────────────────────────────────────────────┘
```

---

## Multi-Tenancy Model

All tenant data is isolated by `institution_id`. Three independent layers enforce this guarantee.

### Layer 1 — `InstitutionScope` (Eloquent Global Scope)

Every tenant model registers `InstitutionScope` as a global scope. It automatically appends `WHERE institution_id = ?` to every query. The institution ID is resolved from the Laravel service container first (`current_institution_id` binding), falling back to `auth()->user()->institution_id`. This dual-source resolution exists because Livewire AJAX requests can bypass HTTP middleware.

### Layer 2 — `SetTenantScope` Middleware

Registered in the `/admin` panel's `authMiddleware` chain. On every authenticated request it reads `auth()->user()->institution_id` and binds it into the service container. This is the primary source for `InstitutionScope`.

### Layer 3 — Filament Shield Policies

Auto-generated resource policies check that `$user->institution_id === $model->institution_id` on every read and write operation, providing a final authorization layer independent of the query scope.

### Auto-stamping

All tenant models register a `creating` Eloquent event listener that calls `InstitutionScope::resolveInstitutionId()` and sets `institution_id` on the new record automatically. Institution admins cannot create records for other institutions even if they craft a direct HTTP request.

---

## Data Model & Business Logic

### Entity Hierarchy

```
Institution
  ├── Branch (optional grouping of rooms; branch_id is nullable on Room)
  │     └── Room
  └── Room (branch_id = null, attached directly to the institution)
        └── Asset (identified by unique UUID qr_code)
              └── AssetTransfer (immutable movement log — never deleted)

Institution
  └── ResponsiblePerson (a named person, NOT a system user; assigned to rooms)

AssetCategory  ─── used by Asset
AssetStatus    ─── used by Asset  (system defaults + institution custom)
```

### Key Entities

| Model | Description |
|---|---|
| `Institution` | Top-level tenant. Every other model belongs to one. |
| `Branch` | Optional physical grouping (e.g., "Main Campus", "North Building"). |
| `Room` | A room within a branch or institution. Has an optional `ResponsiblePerson`. |
| `Asset` | A single physical item. Has a category, status, current room, and unique QR code UUID. |
| `AssetTransfer` | Append-only log of every room change an asset undergoes, with timestamps and the acting user. |
| `AssetCategory` | Classifies assets (e.g., "Laptop", "Chair", "Projector"). Tenant-scoped. |
| `AssetStatus` | Lifecycle status (e.g., "In Use", "In Repair", "Decommissioned"). Supports global system defaults (`institution_id IS NULL`) and institution-specific statuses side by side. |
| `ResponsiblePerson` | A named staff member associated with a room. Not a login account. |

### AssetStatus Special Scope

`AssetStatus` uses a **named** global scope (`institution_statuses`) rather than the standard `InstitutionScope`. This scope returns rows where `institution_id IS NULL` (the four system-wide defaults seeded at setup) **OR** `institution_id = current_institution`. This gives every institution a baseline set of statuses while still allowing them to add their own.

---

## Key Features

### Asset Lifecycle Management
- Create, edit, and decommission assets with full metadata (name, serial number, category, status, location, notes).
- Every asset is permanently linked to a unique UUID that powers its QR code — the identifier never changes.

### Location Tracking
- Assets are always associated with a specific room. Moving an asset requires going through the Asset Transfer workflow, which guarantees an audit log entry is written atomically with the room update.

### QR Code Generation & Scanning
- An SVG QR code is generated automatically the moment an asset is created.
- The QR code encodes a public URL (`/scan/{token}`) that anyone with a phone camera can open — no login needed.
- The public scan page displays the asset's name, category, status, current location, and responsible person.

### Printable Asset Labels
- Institution administrators can download a print-ready PDF label for any asset containing the asset details and its QR code, suitable for physical labeling of equipment.

### Bulk CSV Import
- Administrators can import large datasets via CSV for assets, categories, statuses, branches, rooms, and responsible persons — eliminating manual data entry when onboarding.

### Immutable Transfer History
- Every room-to-room move is recorded as an `AssetTransfer` row. These records are never deleted, providing a full audit trail for any asset.

### Dashboard & Reporting
- A role-appropriate dashboard summarizes asset counts, recent transfers, and status breakdowns using Filament widgets.

---

## User Roles & Access Control

Roles are managed by **Spatie Laravel Permission**, integrated via **Filament Shield**.

| Role | Panel | Capabilities |
|---|---|---|
| `super_admin` | `/super` | Create/manage institutions and institution admin accounts; monitor background jobs and failed jobs |
| `institution_admin` | `/admin` | Full CRUD over all tenant resources (assets, categories, statuses, branches, rooms, responsible persons, transfers) within their institution only |
| — (public) | Public routes | Read-only asset info via QR code scan; no login required |

`User::canAccessPanel()` gates which panel each user may enter. A `super_admin` cannot access `/admin` and vice versa.

---

## QR Code Subsystem

```
Asset creation
      │
      ▼
AssetObserver::creating()
  └─ sets qr_code = Str::uuid()

AssetObserver::created()
  └─ QrCodeService::generate($asset)
        └─ generates SVG via simplesoftwareio/simple-qrcode
        └─ saves to storage/app/public/qrcodes/{uuid}.svg
        └─ php artisan storage:link exposes it at /storage/qrcodes/

Public access (no auth)
  GET /scan/{token}   → AssetScanController  → asset info view
  GET /qr/{token}     → returns SVG file download
  GET /label/{token}  → AssetLabelController → dompdf PDF label
```

**SVG format** is used exclusively because the `imagick` PHP extension required for PNG is not available in all deployment environments.

---

## Asset Transfer Workflow

Asset transfers are the most business-critical operation in the system. They are handled exclusively through `TransferService::transfer()`, which:

1. Validates that the destination room belongs to the same institution as the asset.
2. Opens a database transaction.
3. Creates an `AssetTransfer` record (source room, destination room, user, timestamp, optional note).
4. Updates `Asset.room_id` to the new room.
5. Commits the transaction atomically.

Direct writes to `Asset.room_id` without going through `TransferService` are never done — this invariant is enforced by code convention and test coverage.

---

## Bulk Import & Export

Filament's native import/export system is integrated for the following entities:

| Entity | Import | Export |
|---|---|---|
| Asset | Yes | Yes |
| Asset Category | Yes | Yes |
| Asset Status | Yes | Yes |
| Branch | Yes | Yes |
| Room | Yes | Yes |
| Responsible Person | Yes | Yes |

Failed import rows are stored in the `failed_import_rows` table and surfaced in the admin UI so administrators can correct and re-import problematic records without re-uploading the entire file.

Sample import CSV templates are provided in `public/sample-imports/`.

---

## Testing Strategy

Tests use **SQLite in-memory** databases — no PostgreSQL instance is required to run the test suite.

```bash
# Run all tests
php artisan test

# Run a specific test file
php artisan test tests/Unit/TransferServiceTest.php

# Run a specific test by name
php artisan test --filter="transfer moves asset to new room"
```

### Test Coverage Areas

| Test file | What it covers |
|---|---|
| `Unit/TransferServiceTest.php` | Transfer atomicity, rollback on failure, room validation |
| `Unit/InstitutionScopeTest.php` | Tenant isolation — queries from one institution never return another's data |
| `Unit/AssetObserverTest.php` | QR UUID assignment and SVG file generation on asset creation |
| `Feature/TenantIsolationTest.php` | End-to-end tenant isolation across HTTP requests |
| `Feature/AssetManagementTest.php` | CRUD operations on assets through the Filament panel |
| `Feature/SuperAdminTest.php` | Super admin panel access and institution management |
| `Feature/InstitutionAdminPanelTest.php` | Institution admin panel access and resource CRUD |
| `Feature/PublicQrScanTest.php` | Unauthenticated QR scan routes return correct asset data |

---

## Getting Started

### Prerequisites

- PHP >= 8.3 with extensions: `pdo`, `pdo_pgsql`, `gd`, `mbstring`, `xml`
- Composer
- Node.js >= 18
- PostgreSQL

### Installation

```bash
# 1. Clone the repository
git clone <repository-url>
cd edu_inventory

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies and build frontend assets
npm install
npm run build

# 4. Configure environment
cp .env.example .env
php artisan key:generate

# 5. Configure your database in .env
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=edu_inventory
# DB_USERNAME=your_user
# DB_PASSWORD=your_password

# 6. Run migrations and seed demo data
php artisan migrate:fresh --seed

# 7. Link public storage (for QR code SVG files)
php artisan storage:link

# 8. Start the development server
php artisan serve
```

### Seeded Demo Accounts

After `php artisan migrate:fresh --seed`:

| Role | Email | Password |
|---|---|---|
| Super Admin | superadmin@eduinventory.com | password |
| Institution Admin (Greenwood) | admin@greenwood.edu | password |
| Institution Admin (Riverside) | admin@riverside.edu | password |
| Institution Admin (Sunrise) | admin@sunrise.edu | password |

### Panel URLs

| Panel | URL | Role Required |
|---|---|---|
| Super Admin | `/super` | `super_admin` |
| Institution Admin | `/admin` | `institution_admin` |

---

## Project Structure

```
app/
├── Filament/
│   ├── Admin/                  # /admin panel — institution-scoped resources
│   │   ├── Resources/          # Asset, Branch, Room, Transfer, etc.
│   │   └── Imports/            # CSV importer classes
│   └── SuperAdmin/             # /super panel — platform-wide resources
│       └── Resources/          # Institution, User, Job, FailedJob
├── Http/
│   ├── Controllers/Public/     # Unauthenticated QR scan & label routes
│   └── Middleware/
│       └── SetTenantScope.php  # Binds institution_id per request
├── Models/                     # Eloquent models (all tenant models use InstitutionScope)
├── Observers/
│   └── AssetObserver.php       # QR UUID assignment + SVG generation
├── Providers/Filament/
│   ├── AdminPanelProvider.php
│   └── SuperAdminPanelProvider.php
├── Scopes/
│   └── InstitutionScope.php    # Global Eloquent scope for tenant isolation
└── Services/
    ├── QrCodeService.php        # SVG QR code generation
    └── TransferService.php      # Atomic asset room transfer

database/
├── migrations/                 # Ordered schema migrations
└── seeders/
    ├── SuperAdminSeeder.php
    ├── AssetStatusSeeder.php    # System-wide default statuses
    └── DemoSeeder.php           # 3 demo institutions with full data

tests/
├── Unit/                       # Service and scope unit tests
└── Feature/                    # HTTP + panel integration tests
```

---

## License

This project was developed as a diploma thesis. All rights reserved by the author.

---

*Built with Laravel 13 · Filament 5 · PHP 8.3*