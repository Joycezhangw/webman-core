<?php

namespace Landao\WebmanCore\Helpers;

use Illuminate\Support\Str;

/**
 *
 * CamelHelper 驼峰命名和蛇形命名互转
 *
 * Class CamelHelper
 * @package LanDao\LaravelCore\Helpers
 */
class CamelHelper
{
    /**
     * 循环迭代将数组键驼峰转下划线
     * @param $arr
     * @return array
     */
    public static function recursiveConvertParameterNameCase($arr): array
    {
        if (!is_array($arr)) {
            return $arr;
        }
        $params = [];
        foreach ($arr as $key => $value) {
            if (!is_int($key)) {
                if (is_array($value)) {
                    $params[Str::snake($key)] = self::recursiveConvertParameterNameCase($value);
                } else {
                    $params[Str::snake($key)] = $value;
                }
            } elseif (is_array($value)) {
                $params[$key] = self::recursiveConvertParameterNameCase($value);
            } else {
                $params[Str::snake($key)] = $value;
            }
        }
        return $params;
    }

    /**
     * 循环迭代将数组键转换位驼峰
     * @param $arr
     * @return array
     */
    public static function recursiveConvertNameCaseToCamel($arr): array
    {
        if (!is_array($arr)) {
            return $arr;
        }
        $outArray = [];
        foreach ($arr as $key => $value) {
            if (!is_int($key)) {
                if (is_array($value)) {
                    $outArray[Str::camel($key)] = self::recursiveConvertNameCaseToCamel($value);
                } else {
                    $outArray[Str::camel($key)] = $value;
                }
            } elseif (is_array($value)) {
                $outArray[$key] = self::recursiveConvertNameCaseToCamel($value);
            } else {
                $outArray[Str::camel($key)] = $value;
            }
        }
        return $outArray;
    }

}