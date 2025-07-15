<?php

namespace Landao\WebmanCore\Exceptions;


class ValidationException extends BaseException
{
    public int $statusCode = 400;

    /**
     * 业务错误码
     * @var int
     */
    public int $errorCode = 400;

    public string $errorMessage = '请求参数错误，请检查参数是否正确';
}