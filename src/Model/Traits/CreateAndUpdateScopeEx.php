<?php

namespace Landao\WebmanCore\Model\Traits;

use Tinywan\Jwt\JwtToken;

trait CreateAndUpdateScopeEx
{
    protected static function boot()
    {
        parent::boot();

        //新增时
        static::creating(function ($model) {
            $uid = JwtToken::getCurrentId();
            if ($uid && $model->isFillable('created_by')) {
                $model->created_by = $uid;
            }
        });

        //更新时
        static::updating(function ($model) {
            $uid = JwtToken::getCurrentId();
            if ($uid && $model->isFillable('updated_by')) {
                $model->created_by = $uid;
            }
        });
    }
}