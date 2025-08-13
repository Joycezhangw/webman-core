<?php
declare(strict_types=1);

namespace Landao\WebmanCore\Exceptions;

/**
 * 异常工厂类
 */
class ExceptionFactory
{
    /**
     * 创建 BusinessException 异常
     *
     * @param object $errorCode 实现了获取标签和值方法的错误码对象
     * @param array $params 参数数组，用于替换消息中的占位符
     * @param string $labelMethod 获取错误信息的方法名，默认为 getEnumLabel
     * @param string $valueProperty 获取错误码值的属性名，默认为 value
     * @return BusinessException
     */
    public static function businessException(
        object $errorCode,
               ...$params
    ): BusinessException
    {
        $message = method_exists($errorCode, 'getEnumLabel')
            ? $errorCode->getEnumLabel()
            : 'Unknown error';

        // 如果有参数，则进行替换
        if (!empty($params)) {
            $message = self::replacePlaceholders($message, $params);
        }

        $code = property_exists($errorCode, 'value')
            ? $errorCode->value
            : 0;

        return new BusinessException($message, $code);
    }

    /**
     * 直接抛出 BusinessException 异常
     *
     * @param object $errorCode 实现了获取标签和值方法的错误码对象
     * @param array $params 参数数组，用于替换消息中的占位符
     * @param string $labelMethod 获取错误信息的方法名，默认为 getEnumLabel
     * @param string $valueProperty 获取错误码值的属性名，默认为 value
     * @throws BusinessException
     */
    public static function throwBusinessException(
        object $errorCode,
               ...$params
    ): void
    {
        throw self::businessException($errorCode, ...$params);
    }

    /**
     * 替换消息中的占位符
     * 支持 {} 或 {0}, {1} 等格式
     *
     * @param string $message 原始消息
     * @param array $params 参数数组
     * @return string
     */
    private static function replacePlaceholders(string $message, array $params): string
    {
        // 支持 {} 格式（按顺序替换）
        if (strpos($message, '{}') !== false) {
            foreach ($params as $param) {
                $message = preg_replace('/\{\}/', (string)$param, $message, 1);
            }
            return $message;
        }

        // 支持 {0}, {1} 等索引格式
        foreach ($params as $index => $param) {
            $message = str_replace('{' . $index . '}', (string)$param, $message);
        }

        return $message;
    }
}