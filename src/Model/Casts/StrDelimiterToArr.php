<?php

namespace Landao\WebmanCore\Model\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * 以逗号分割的字符串数值转数组
 */
class StrDelimiterToArr implements CastsAttributes
{
    public function set($model, string $key, $value, array $attributes)
    {
        return is_array($value) && count($value) > 0 ? implode(',', $value) : '';
    }

    public function get($model, string $key, $value, array $attributes)
    {
        return trim($value) != '' ? explode(',', $value) : [];
    }
}