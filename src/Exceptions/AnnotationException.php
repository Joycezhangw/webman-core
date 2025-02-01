<?php

namespace Landao\WebmanCore\Exceptions;


class AnnotationException extends BaseException
{
    public int $statusCode = 500;

    public string $errorMessage = '路由解析错误';
}