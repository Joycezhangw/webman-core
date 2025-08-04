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
            // 递归处理数组值
            if (is_array($value)) {
                $processedValue = self::recursiveConvertParameterNameCase($value);
            } else {
                $processedValue = $value;
            }

            // 根据键类型决定是否转换
            if (is_string($key)) {
                // 字符串键转换为蛇形命名
                $params[Str::snake($key)] = $processedValue;
            } elseif (is_int($key)) {
                // 整数键保持原样
                $params[$key] = $processedValue;
            } else {
                // 其他类型的键（如浮点数、布尔值等）转换为字符串
                $params[strval($key)] = $processedValue;
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
            // 递归处理数组值
            $processedValue = is_array($value) ? self::recursiveConvertNameCaseToCamel($value) : $value;

            // 根据键类型决定是否转换
            if (is_string($key)) {
                // 字符串键转换为驼峰命名
                $outArray[Str::camel($key)] = $processedValue;
            } elseif (is_int($key)) {
                // 整数键保持原样
                $outArray[$key] = $processedValue;
            } else {
                // 其他类型的键转换为字符串后再应用驼峰命名
                $outArray[Str::camel(strval($key))] = $processedValue;
            }
        }
        return $outArray;
    }

}