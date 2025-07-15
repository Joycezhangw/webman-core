<?php

namespace Landao\WebmanCore\Exceptions;


class AnnotationException extends BaseException
{
    public int $statusCode = 500;

    /**
     * 业务错误码
     * @var int
     */
    public int $errorCode = 500;

    public string $errorMessage = '路由解析错误';
}