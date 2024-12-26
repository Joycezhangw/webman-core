<?php

namespace Landao\WebmanCore\Tenant\Model;

use support\Model;

class Tenant extends Model
{
    public function __construct(array $attributes = [])
    {
        // 从配置文件获取表名
        $this->table = config('plugin.landao.webman-core.app.tenant.table', 'tenants');

        // 从配置文件获取主键
        $this->primaryKey = config('plugin.landao.webman-core.app.tenant.primary_key', 'tenant_id');

        // 从配置文件获取可填充字段
        $this->fillable = config('plugin.landao.webman-core.app.tenant.fillable', []);

        // 从配置文件获取数据库连接
//        $this->connection = config('plugin.landao.tenant.app.tenant.connection', 'mysql');

        parent::__construct($attributes);
    }
}