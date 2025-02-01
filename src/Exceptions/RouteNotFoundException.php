<?php
declare(strict_types=1);

namespace Landao\WebmanCore\Exceptions;

class RouteNotFoundException extends BaseException
{
    /**
     * HTTP 状态码
     */
    public int $statusCode = 404;

    /**
     * 错误消息.
     */
    public string $errorMessage = '路由地址不存在';
}