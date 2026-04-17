<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class InstitutionScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $institutionId = $this->resolveInstitutionId();

        if ($institutionId) {
            $builder->where($model->getTable() . '.institution_id', $institutionId);
        }
    }

    public static function resolveInstitutionId(): ?int
    {
        if (app()->has('current_institution_id')) {
            return app('current_institution_id');
        }

        $user = auth()->user();
        if ($user && $user->institution_id) {
            // Cache it for the rest of this request
            app()->instance('current_institution_id', $user->institution_id);
            return $user->institution_id;
        }

        return null;
    }
}
