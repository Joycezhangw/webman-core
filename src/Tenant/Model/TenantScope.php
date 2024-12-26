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

        $tenantColumn = $model::$tenantIdColumn ?? 'tenant_id';
        $builder->where($tenantColumn, '=', tenancy()->getTenantId());
    }

    /**
     * 扩展查询构建器
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenancy', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
        $primaryKey = config('plugin.landao.webman-core.app.tenant.primary_key', 'tenant_id');
        $builder->macro('forTenant', function (Builder $builder, $tenantId) use ($primaryKey) {
            return $builder->withoutTenancy()->where($primaryKey, $tenantId);
        });
    }
}