<?php

namespace Landao\WebmanCore\Helpers;

class ResultHelper
{
    const CODE_SUCCESS = 200;//正确执行后的返回码
    const CODE_WARNING = -1;//逻辑警告，自定义 message 说明，请结合 Enum\ResultCodeEnum 后端结合使用

    /**
     * 逻辑层 返回 array 数据格式，在api中，要使用 tp6 中的 json() 函数转 json ，laravel 中api自动转json
     * @param string $msg 提示信息
     * @param int $code 状态码 200:一切都ok，-1：逻辑返回错误信息，其他状态码可再定制
     * @param array $data 返回数据
     * @return array
     */
    public static function returnFormat(string $msg = 'success', $code = self::CODE_SUCCESS, $data = []): array
    {
        list($ret['code'], $ret['message']) = [$code, trim($msg)];
        $ret['data'] = $data;
        return $ret;
    }

    /**
     * 返回错误信息
     * @param string $msg
     * @return array
     */
    public static function error(string $msg = 'error'): array
    {
        return self::returnFormat($msg, self::CODE_WARNING);
    }

    /**
     * 返回成功信息
     * @param string $msg
     * @param array $data
     * @return array
     */
    public static function success(string $msg = 'success', array $data = []): array
    {
        return self::returnFormat($msg, self::CODE_SUCCESS, $data);
    }
}