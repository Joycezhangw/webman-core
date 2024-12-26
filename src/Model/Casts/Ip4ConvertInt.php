<?php

namespace Landao\WebmanCore\Model\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Landao\WebmanCore\Helpers\StrHelper;

/**
 * ip4地址转int
 */
class Ip4ConvertInt implements CastsAttributes
{
    public function set($model, string $key, $value, array $attributes)
    {
        return StrHelper::ip2long($value);
    }

    public function get($model, string $key, $value, array $attributes)
    {
        return $value > 0 ? long2ip(intval($value)) : '-';
    }
}