<?php

namespace Landao\WebmanCore\Tenant\Traits;

use Landao\WebmanCore\Exceptions\TenancyException;
use Landao\WebmanCore\Tenant\Model\TenantScope;

trait BelongsToTenant
{


    /**
     * 获取租户ID字段名
     */
    public static function getTenantIdColumn(): string
    {
        return config('plugin.landao.webman-core.app.tenant.primary_key', 'tenant_id');
    }

    /**
     * 租户关联
     */
    public function tenant()
    {
        $tenantModel = config('plugin.landao.webman-core.app.tenant.model');
        // 检查 tenantModel 是否有效
        if (!$tenantModel) {
            throw new TenancyException('租户模型未定义，请检查配置。');
        }
        return $this->belongsTo($tenantModel, static::getTenantIdColumn());
    }

    /**
     * 启动多租户trait
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            $tenantIdColumn = static::getTenantIdColumn();
            if (!$model->getAttribute($tenantIdColumn) && !$model->relationLoaded('tenant')) {
                if (tenancy()->hasTenant()) {
                    $model->setAttribute($tenantIdColumn, tenancy()->getTenantId());
                    $model->setRelation('tenant', tenancy()->getTenant());
                }
            }
        });

    }

}