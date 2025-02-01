<?php

namespace Landao\WebmanCore\Exceptions;

use Exception;

class EnumException extends BaseException
{
    public int $statusCode = 500;

    public string $errorMessage = '未配置 Enum 注解说明';
}