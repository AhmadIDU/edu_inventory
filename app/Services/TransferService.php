<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetTransfer;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function transfer(
        Asset $asset,
        int $toRoomId,
        string $transferredBy,
        string $transferDate,
        ?string $notes = null
    ): AssetTransfer {
        return DB::transaction(function () use ($asset, $toRoomId, $transferredBy, $transferDate, $notes) {
            $transfer = AssetTransfer::create([
                'asset_id'        => $asset->id,
                'institution_id'  => $asset->institution_id,
                'from_room_id'    => $asset->room_id,
                'to_room_id'      => $toRoomId,
                'transferred_by'  => $transferredBy,
                'transfer_date'   => $transferDate,
                'notes'           => $notes,
            ]);

            $asset->update(['room_id' => $toRoomId]);

            return $transfer;
        });
    }
}
