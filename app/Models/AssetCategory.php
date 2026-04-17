<?php

namespace App\Models;

use App\Scopes\InstitutionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'institution_id', 'name', 'description',
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

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'category_id');
    }
}
