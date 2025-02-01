<?php

namespace Landao\WebmanCore\Exceptions;


class FileAlreadyExistException extends BaseException
{
    public int $statusCode = 500;

    public string $errorMessage = '文件扫描不存在';
}