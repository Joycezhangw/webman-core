<?php
declare(strict_types=1);

namespace Landao\WebmanCore\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Throwable;

class BaseException extends Exception implements Throwable
{
    /**
     * Http Response 状态码
     * @var int
     */
    public int $statusCode = FoundationResponse::HTTP_BAD_REQUEST;

    /**
     * Http Response 头信息
     * @var array
     */
    public array $headers = [];

    /**
     * 业务错误信息
     * @var string
     */
    public string $errorMessage = 'The requested resource is not available or not exists';

    /**
     * 业务错误码
     * @var int
     */
    public int $errorCode = 0;

    /**
     * 业务数据
     * @var array
     */
    public array $data = [];

    /**
     * 详细的错误信息
     * @var string
     */
    public string $error = '';

    public function __construct(string $message = "", array $params = [], string $error = '', Throwable $previous = null)
    {
        parent::__construct($message, $this->statusCode, $previous);

        if (!empty($params)) {
            $this->errorMessage = $message;
        }
        if (!empty($error)) {
            $this->error = $error;
        }
        if (!empty($params)) {
            if (array_key_exists('statusCode', $params)) {
                $this->statusCode = $params['statusCode'];
            }
            if (array_key_exists('header', $params)) {
                $this->header = $params['header'];
            }
            if (array_key_exists('errorCode', $params)) {
                $this->errorCode = $params['errorCode'];
            }
            if (array_key_exists('data', $params)) {
                $this->data = $params['data'];
            }
        }

    }

}