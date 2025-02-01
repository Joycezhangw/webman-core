<?php
declare(strict_types=1);

namespace Landao\WebmanCore\Exceptions;

class BadRequestHttpException extends BaseException
{
    public int $statusCode = 400;

    public string $errorMessage = '请求参数错误，请检查参数是否正确';

}