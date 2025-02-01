<?php
declare(strict_types=1);

namespace Landao\WebmanCore\Exceptions;

class NotFoundHttpException extends BaseException
{
    /**
     * @var int
     */
    public int $statusCode = 404;

    /**
     * @var string
     */
    public string $errorMessage = '未找到请求的资源';
}