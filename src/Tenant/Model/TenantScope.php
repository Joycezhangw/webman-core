<?php

namespace Landao\WebmanCore\Tenant\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * 应用租户范围
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!tenancy()->hasTenant()) {
            return;
        }
        $builder->where('tenant_id', '=', tenancy()->getTenantId());
    }

    /**
     * 扩展查询构建器
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenancy', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
        $builder->macro('forTenant', function (Builder $builder, $tenantId) {
            return $builder->withoutTenancy()->where('tenant_id', $tenantId);
        });
    }
}