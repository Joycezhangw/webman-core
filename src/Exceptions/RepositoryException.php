<?php

namespace Landao\WebmanCore\Exceptions;


class RepositoryException extends BaseException
{
    /**
     * @var int
     */
    public int $statusCode = 500;

    /**
     * @var string
     */
    public string $errorMessage = 'Repository exception';
}