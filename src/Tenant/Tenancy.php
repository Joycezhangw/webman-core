<?php

namespace Landao\WebmanCore\Tenant;


/**
 * 租户管理类
 */
class Tenancy
{
    private static $instance = null;
    private $currentTenant = null;

    private function __construct()
    {
        // 私有构造函数，防止直接实例化
    }

    /**
     * 私有克隆函数，防止克隆实例
     */
    private function __clone()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 设置当前租户
     * @param Tenant|null $tenant
     * @return $this
     */
    public function setTenant($tenant): self
    {
        $this->currentTenant = $tenant;

//        if ($tenant) {
//            $this->switchDatabase($tenant);
//        }

        return $this;
    }

    /**
     * 获取当前租户信息
     * @return array|null
     */
    public function getTenant()
    {
        return $this->currentTenant;
    }

    /**
     * 获取主键名称
     */
    private function getPrimaryKey(): string
    {
        return config('plugin.landao.webman-core.app.tenant.primary_key', 'tenant_id');
    }

    /**
     * 获取当前租户ID
     * @return int|null
     */
    public function getTenantId(): ?int
    {
        if (!$this->currentTenant) {
            return null;
        }
        $primaryKey = $this->getPrimaryKey();
        return $this->currentTenant->$primaryKey;
    }

    /**
     * 判断是否有当前租户
     * @return bool
     */
    public function hasTenant(): bool
    {
        return $this->currentTenant !== null;
    }

    /**
     * 清除当前租户信息
     * @return $this
     */
    public function clearTenant(): self
    {
        $this->currentTenant = null;
        return $this;
    }

    /**
     * 切换数据库连接
     */
    private function switchDatabase(Tenant $tenant): void
    {
        // 动态设置数据库连接
//        config([
//            'database.connections.tenant' => [
//                'driver' => 'mysql',
//                'host' => config('database.connections.mysql.host'),
//                'port' => config('database.connections.mysql.port', 3306),
//                'database' => $tenant->database,
//                'username' => config('database.connections.mysql.username'),
//                'password' => config('database.connections.mysql.password'),
//                'charset' => config('database.connections.mysql.charset', 'utf8mb4'),
//                'collation' => config('database.connections.mysql.collation', 'utf8mb4_unicode_ci'),
//                'prefix' => config('database.connections.mysql.prefix', ''),
//                'strict' => config('database.connections.mysql.strict', true),
//                'engine' => config('database.connections.mysql.engine', null),
//            ]
//        ]);
    }
}