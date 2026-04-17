<?php

namespace App\Models;

use App\Scopes\InstitutionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;
    protected $fillable = [
        'institution_id', 'room_id', 'category_id', 'status_id',
        'name', 'serial_number', 'qr_code',
        'purchase_date', 'purchase_value', 'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_value' => 'decimal:2',
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

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AssetStatus::class, 'status_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class);
    }

    public function getQrImageUrlAttribute(): string
    {
        return asset('storage/qrcodes/' . $this->qr_code . '.svg');
    }

    public function getQrScanUrlAttribute(): string
    {
        return route('asset.scan', $this->qr_code);
    }
}
