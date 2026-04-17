<?php

namespace App\Observers;

use App\Models\Asset;
use App\Services\QrCodeService;

class AssetObserver
{
    public function __construct(private QrCodeService $qrCodeService) {}

    public function creating(Asset $asset): void
    {
        if (empty($asset->qr_code)) {
            // Generate UUID token; PNG will be created after the asset is persisted
            $asset->qr_code = \Illuminate\Support\Str::uuid()->toString();
        }
    }

    public function created(Asset $asset): void
    {
        $url = route('asset.scan', $asset->qr_code);
        $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->errorCorrection('M')->generate($url);
        \Illuminate\Support\Facades\Storage::disk('public')->put('qrcodes/' . $asset->qr_code . '.svg', $svg);
    }

    public function deleted(Asset $asset): void
    {
        \Illuminate\Support\Facades\Storage::disk('public')->delete('qrcodes/' . $asset->qr_code . '.svg');
    }
}
