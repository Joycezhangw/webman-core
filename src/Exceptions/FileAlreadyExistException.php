<?php

namespace Landao\WebmanCore\Exceptions;


class FileAlreadyExistException extends BaseException
{
    public int $statusCode = 500;

    /**
     * 业务错误码
     * @var int
     */
    public int $errorCode = 500;

    public string $errorMessage = '文件扫描不存在';
}