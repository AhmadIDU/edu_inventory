<?php

namespace App\Models;

use App\Scopes\InstitutionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;
    protected $fillable = [
        'institution_id', 'branch_id', 'responsible_person_id', 'name', 'room_number',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(ResponsiblePerson::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->name;
        if ($this->room_number) {
            $name .= ' (' . $this->room_number . ')';
        }
        if ($this->branch) {
            $name .= ' — ' . $this->branch->name;
        }
        return $name;
    }
}
