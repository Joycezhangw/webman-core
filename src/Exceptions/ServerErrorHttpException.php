<?php
declare(strict_types=1);

namespace Landao\WebmanCore\Exceptions;

/**
 * 服务器错误
 */
class ServerErrorHttpException extends BaseException
{
    public int $statusCode = 500;

    public string $errorMessage = 'Internal Server Error';
}