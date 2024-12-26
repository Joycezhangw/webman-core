<?php

namespace Landao\WebmanCore\Tenant\Traits;

use Landao\WebmanCore\Tenant\Model\ParentModelScope;

trait BelongsToPrimaryModel
{
    /**
     * 获取与主模型的关系名称
     */
    abstract public function getRelationshipToPrimaryModel(): string;

    /**
     * 启动父模型trait
     */
    public static function bootBelongsToPrimaryModel(): void
    {
        static::addGlobalScope(new ParentModelScope());
    }

}