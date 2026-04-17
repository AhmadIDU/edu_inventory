<?php

namespace App\Models;

use App\Scopes\InstitutionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTransfer extends Model
{
    use HasFactory;
    protected $fillable = [
        'asset_id', 'institution_id', 'from_room_id', 'to_room_id',
        'transferred_by', 'transfer_date', 'notes',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new InstitutionScope());

        static::creating(function (self $model) {
            if (empty($model->institution_id)) {
                $model->institution_id = \App\Scopes\InstitutionScope::resolveInstitutionId();
            }
        });
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function fromRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'from_room_id');
    }

    public function toRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'to_room_id');
    }
}
