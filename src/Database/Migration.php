<?php

namespace Landao\WebmanCore\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use support\Db;

abstract class Migration extends \Illuminate\Database\Migrations\Migration
{
    public Connection $db;

    public function __construct()
    {
        $this->db = Db::connection();
    }
    protected function db(): Connection
    {
        return $this->db;
    }

    /**
     * 获取Schema构建器
     * @return Builder
     */
    protected function schema(): Builder
    {
        return $this->db()->getSchemaBuilder();
    }

    /**
     * 执行迁移
     */
    abstract public function up(): void;

    /**
     * 回滚迁移
     */
    abstract public function down(): void;
}