<?php

namespace App\Models;

use App\Scopes\InstitutionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResponsiblePerson extends Model
{
    use HasFactory;
    protected $table = 'responsible_persons';

    protected $fillable = [
        'institution_id', 'name', 'contact', 'position',
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

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
