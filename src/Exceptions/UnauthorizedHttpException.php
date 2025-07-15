<?php
declare(strict_types=1);

namespace Landao\WebmanCore\Exceptions;

class UnauthorizedHttpException extends BaseException
{
    /**
     * HTTP 状态码
     */
    public int $statusCode = 401;

    /**
     * 业务错误码
     * @var int
     */
    public int $errorCode = 401;

    /**
     * 错误消息.
     */
    public string $errorMessage = 'Unauthorized';
}