<?php

namespace App\Services;

use App\Models\Asset;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    public function generate(Asset $asset): string
    {
        $token = Str::uuid()->toString();

        $url = route('asset.scan', $token);

        $svg = QrCode::format('svg')->size(300)->errorCorrection('M')->generate($url);

        Storage::disk('public')->put('qrcodes/' . $token . '.svg', $svg);

        return $token;
    }

    public function deleteQrFile(string $token): void
    {
        Storage::disk('public')->delete('qrcodes/' . $token . '.svg');
    }
}
