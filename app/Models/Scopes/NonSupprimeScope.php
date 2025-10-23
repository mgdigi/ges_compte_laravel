<?php

namespace App\Models\Scopes;

use \Illuminate\Database\Eloquent\Scope;
use \Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Builder;

class NonSupprimeScope implements Scope
{
    // Scope implementation
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereNull($model->getTable() . '.deleted_at');
    }
} 