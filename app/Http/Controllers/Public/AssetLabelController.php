<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetLabelController extends Controller
{
    public function show(string $token)
    {
        $asset = Asset::withoutGlobalScopes()
            ->with(['room.branch.institution', 'room.responsiblePerson', 'category', 'status'])
            ->where('qr_code', $token)
            ->firstOrFail();

        $pdf = Pdf::loadView('public.asset_label', compact('asset'))
            ->setPaper([0, 0, 200, 200], 'portrait');

        return $pdf->stream('asset-label-' . str($asset->name)->slug() . '.pdf');
    }
}
