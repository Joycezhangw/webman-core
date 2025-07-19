<?php

namespace Landao\WebmanCore\Helpers;

/**
 * 脱敏工具类
 */
class DesensitizeHelper
{
    /**
     * 内置脱敏规则集合
     * @var array
     */
    private static $builtinRules = [
        // 手机号脱敏：138****1234
        'mobile' => [self::class, 'mobile'],
        // 邮箱脱敏：ab***@example.com
        'email' => [self::class, 'email'],
        // 身份证号脱敏：110********1234
        'id_card' => [self::class, 'idCard'],
        // 姓名脱敏：张*
        'name' => [self::class, 'name'],
        // 银行卡号脱敏：6222 **** **** 1234
        'bank_card' => [self::class, 'bankCard'],
        // 地址脱敏：北京市海淀区****
        'address' => [self::class, 'address'],
    ];

    /**
     * 统一脱敏入口
     * @param mixed $value 待脱敏值
     * @param mixed $rule 脱敏规则
     * @return mixed
     */
    public static function desensitize(mixed $value, mixed $rule, ?string $condition = null): mixed
    {

        if (!is_string($value)) {
            return $value;
        }

        // 内置函数名
        if (is_string($rule) && method_exists(self::class, $rule)) {
            return self::$rule($value);
        }

        if (is_callable($rule)) {
            return call_user_func($rule, $value);
        }

        // 正则表达式配置
        if (is_array($rule) && isset($rule['pattern'], $rule['replacement'])) {
            return preg_replace($rule['pattern'], $rule['replacement'], $value);
        }

        return $value;
    }

    /**
     * 获取内置规则
     * @param array $ruleNames 需要获取的规则名称列表
     * @return array
     */
    public static function getBuiltinRules(array $ruleNames = []): array
    {
        if (empty($ruleNames)) {
            return self::$builtinRules;
        }

        $rules = [];
        foreach ($ruleNames as $name) {
            if (isset(self::$builtinRules[$name])) {
                $rules[$name] = self::$builtinRules[$name];
            }
        }
        return $rules;
    }

    /**
     * 手机号脱敏
     */
    public static function mobile(string $value): string
    {
        return preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $value);
    }

    /**
     * 邮箱脱敏
     */
    public static function email(string $value): string
    {
        return preg_replace('/([a-zA-Z0-9_]{2})[a-zA-Z0-9_]*@/', '$1***@', $value);
    }

    /**
     * 身份证号脱敏
     */
    public static function idCard(string $value): string
    {
        return preg_replace('/(\d{6})\d{8}(\d{4})/', '$1********$2', $value);
    }

    /**
     * 姓名脱敏
     */
    public static function name(string $value): string
    {
        if (mb_strlen($value) <= 1) {
            return $value;
        }
        return mb_substr($value, 0, 1) . str_repeat('*', mb_strlen($value) - 1);
    }

    /**
     * 银行卡号脱敏
     */
    public static function bankCard(string $value): string
    {
        return preg_replace('/(\d{4})\d{12}(\d{4})/', '$1 **** **** $2', $value);
    }

    /**
     * 地址脱敏
     */
    public static function address(string $value): string
    {
        $address = explode(' ', $value);
        if (count($address) > 2) {
            return implode(' ', array_slice($address, 0, 2)) . '****';
        }
        return mb_substr($value, 0, 6) . '****';
    }
}