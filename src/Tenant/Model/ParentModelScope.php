<?php

namespace Landao\WebmanCore\Tenant\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ParentModelScope implements Scope
{
    /**
     * 应用父模型范围
     */
    public function apply(Builder $builder, Model $model): void
    {
        $relationshipMethod = $model->getRelationshipToPrimaryModel();
        $builder->whereHas($relationshipMethod);
    }

    /**
     * 扩展查询构建器
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutParentModel', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        $builder->macro('withParentModel', function (Builder $builder) {
            return $builder->withoutParentModel()->whereHas(
                $builder->getModel()->getRelationshipToPrimaryModel()
            );
        });
    }
}