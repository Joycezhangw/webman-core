<?php

namespace Landao\WebmanCore\Exceptions;

/**
 * 解密异常
 */
class DecryptErrorException extends BaseException
{
    public int $statusCode = 500;

    /**
     * 业务错误码
     * @var int
     */
    public int $errorCode = 500;

    public string $errorMessage = '解密失败';
}