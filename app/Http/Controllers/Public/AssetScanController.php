<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssetScanController extends Controller
{
    public function show(string $token)
    {
        $asset = Asset::withoutGlobalScopes()
            ->with(['room.branch.institution', 'room.responsiblePerson', 'category', 'status'])
            ->where('qr_code', $token)
            ->firstOrFail();

        return view('public.asset_scan', compact('asset'));
    }

    public function downloadQr(string $token): BinaryFileResponse
    {
        $asset = Asset::withoutGlobalScopes()
            ->where('qr_code', $token)
            ->firstOrFail();

        $path = Storage::disk('public')->path('qrcodes/' . $token . '.svg');

        abort_unless(file_exists($path), 404, 'QR code not found.');

        return response()->file($path, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="' . str($asset->name)->slug() . '-qr.svg"',
        ]);
    }
}
