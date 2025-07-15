<?php

namespace Landao\WebmanCore\Exceptions;


class RepositoryException extends BaseException
{
    /**
     * @var int
     */
    public int $statusCode = 500;

    /**
     * 业务错误码
     * @var int
     */
    public int $errorCode = 500;

    /**
     * @var string
     */
    public string $errorMessage = 'Repository exception';
}