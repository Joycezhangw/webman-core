<?php
declare(strict_types=1);

namespace Landao\WebmanCore\Exceptions;

/**
 * 错误码接口
 */
interface ErrorCodeInterface
{
    /**
     * 获取枚举描述
     * @return string
     */
    public function getEnumLabel(): string;

    /**
     * 获取枚举值
     * @return int
     */
    public function getValue(): int;
}