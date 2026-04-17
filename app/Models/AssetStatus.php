<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetStatus extends Model
{
    use HasFactory;
    protected $fillable = [
        'institution_id', 'name', 'color', 'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    // Custom scope: show system defaults + institution's own custom statuses
    protected static function booted(): void
    {
        static::addGlobalScope('institution_statuses', function (Builder $builder) {
            $institutionId = \App\Scopes\InstitutionScope::resolveInstitutionId();
            if ($institutionId) {
                $builder->where(function (Builder $q) use ($institutionId) {
                    $q->whereNull('institution_id')
                        ->orWhere('institution_id', $institutionId);
                });
            }
        });

        static::creating(function (self $model) {
            if (empty($model->institution_id) && ! $model->is_system) {
                $model->institution_id = \App\Scopes\InstitutionScope::resolveInstitutionId();
            }
        });
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'status_id');
    }
}
