<?php

namespace Landao\WebmanCore\Exceptions;

class BusinessException extends BaseException
{
    /**
     * HTTP 状态码
     * @var int
     */
    public int $statusCode = 200;

    /**
     * 业务错误码
     * @var int
     */
    public int $errorCode = 0;

    /**
     * 业务错误消息
     * @var string
     */
    public string $errorMessage = 'success';

    public function __construct(string $message = "", int $errorCode = 0, string $error = '')
    {
        // 调用父类构造函数，将错误码包装成数组
        parent::__construct($message, ['errorCode' => $errorCode], $error);
        // 设置当前类的错误码和错误消息
        $this->errorCode = $errorCode;
        $this->errorMessage = $message;
    }
}