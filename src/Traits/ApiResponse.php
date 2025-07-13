<?php
declare(strict_types=1);

namespace Landao\WebmanCore\Traits;

use Landao\WebmanCore\Helpers\CamelHelper;
use support\Response;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;

trait ApiResponse
{
    /**
     * HTTP状态
     * @var int
     */
    protected int $httpStatusCode = FoundationResponse::HTTP_OK;

    /**
     * 业务状态码
     * @var int|null
     */
    protected ?int $businessCode = null;


    /**
     * 默认响应头
     * @var array
     */
    protected array $defaultHeaders = ['Content-Type' => 'application/json;charset=UTF-8'];

    /**
     * 获取HTTP状态码
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    /**
     * 设置HTTP状态码
     * @param int $httpStatusCode
     * @return $this
     */
    public function setHttpStatusCode(int $httpStatusCode): self
    {
        if ($this->isValidHttpStatusCode($httpStatusCode)) {
            $this->httpStatusCode = $httpStatusCode;
        } else {
            throw new \InvalidArgumentException("Invalid HTTP status code: $httpStatusCode");
        }
        return $this;
    }

    /**
     * 获取业务状态码
     * @return ?int
     */
    public function getBusinessCode(): ?int
    {
        return $this->businessCode;
    }

    /**
     * 设置业务状态码
     * @param ?int $businessCode
     * @return $this
     */
    public function setBusinessCode(?int $businessCode): self
    {
        $this->businessCode = $businessCode;
        return $this;
    }


    /**
     * @param mixed $data
     * @param array $header
     * @return Response
     */
    public function respond(mixed $data, array $header = []): Response
    {
        if (config('plugin.landao.webman-core.app.camel_case_response', false)) {
            $data = CamelHelper::recursiveConvertNameCaseToCamel($data);
        }
        $headers = array_merge($this->defaultHeaders, $header);
        return new Response($this->getHttpStatusCode(), $headers, json_encode($data));
    }

    /**
     * @param string $status
     * @param array $data
     * @param ?int $httpStatusCode
     * @param ?int $businessCode
     * @return Response
     */
    public function status(string $status, array $data, ?int $httpStatusCode = null, ?int $businessCode = null): Response
    {
        // 验证并设置HTTP状态码
        if (!is_null($httpStatusCode)) {
            try {
                $this->setHttpStatusCode($httpStatusCode);
            } catch (\Exception $e) {
                error_log("Failed to set HTTP status code: " . $this->sanitizeLogMessage($e->getMessage()));
                $this->setHttpStatusCode(FoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        // 设置业务状态码
        if (!is_null($businessCode)) {
            $this->setBusinessCode($businessCode);
        }

        // 构建状态信息
        $statusInfo = [
//            'status' => $status,
            'code' => $this->getBusinessCode() ?? $this->getHttpStatusCode()
        ];

        // 处理 message 键为空字符串的情况
        if (isset($data['msg']) && is_string($data['msg']) && trim($data['msg']) === '') {
            unset($data['msg']);
        }

        // 合并数据并返回响应
        $responseData = array_merge($statusInfo, $data);
        return $this->respond($responseData);
    }

    /**
     * 提示
     * @param string $message
     * @param string $status
     * @param ?int $businessCode
     * @return Response
     */
    public function message(string $message, string $status = 'error', ?int $businessCode = 400): Response
    {
        return $this->status($status, ['msg' => $message], null, $businessCode);
    }

    /**
     * 500 错误
     * @param string $message
     * @param ?int $businessCode
     * @return Response
     */
    public function internalError(string $message = 'Internal Server Error!', ?int $businessCode = 500): Response
    {
        return $this->failed($message, FoundationResponse::HTTP_INTERNAL_SERVER_ERROR, $businessCode);
    }

    /**
     * 请求成功，服务器创建了新资源
     * @param string $message
     * @param ?int $businessCode
     * @return Response
     */
    public function created(string $message = 'created', ?int $businessCode = 0): Response
    {
        return $this->setHttpStatusCode(FoundationResponse::HTTP_CREATED)->message($message, 'success', $businessCode);
    }

    /**
     * 请求方法不存在
     * @param string $message
     * @param ?int $businessCode
     * @return Response
     */
    public function methodNotAllow(string $message = 'Method Not Allowed!', ?int $businessCode = 405): Response
    {
        return $this->failed($message, FoundationResponse::HTTP_METHOD_NOT_ALLOWED, $businessCode);
    }

    /**
     * 请求错误响应
     * @param string $message
     * @param ?int $businessCode
     * @param ?int $httpStatusCode
     * @return Response
     */
    public function failed(string $message, ?int $businessCode = 400, ?int $httpStatusCode = FoundationResponse::HTTP_BAD_REQUEST): Response
    {
        return $this->status('error', ['msg' => $message], $httpStatusCode, $businessCode);
    }

    /**
     * 身份验证失败响应
     * @param string $message
     * @param ?int $businessCode
     * @return Response
     */
    public function unAuthorized(string $message = 'Unauthorized.', ?int $businessCode = 401): Response
    {
        return $this->failed($message, $businessCode, FoundationResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * 服务器未知错误响应
     * @param string $message
     * @param ?int $businessCode
     * @return Response
     */
    public function serviceUnavailable(string $message = 'Service Unavailable!', ?int $businessCode = 503): Response
    {
        return $this->failed($message, $businessCode, FoundationResponse::HTTP_SERVICE_UNAVAILABLE);
    }

    /**
     * 权限不足响应
     * @param string $message
     * @param ?int $businessCode
     * @return Response
     */
    public function forbidden(string $message = 'Forbidden.', ?int $businessCode = 403): Response
    {
        return $this->failed($message, $businessCode, FoundationResponse::HTTP_FORBIDDEN);
    }

    /**
     * 表单验证错误响应
     * @param string $message
     * @param ?int $businessCode
     * @return Response
     */
    public function badRequest(string $message = 'Bad Request!', ?int $businessCode = 400): Response
    {
        return $this->failed($message, $businessCode, FoundationResponse::HTTP_BAD_REQUEST);
    }

    /**
     * 成功响应
     * @param array $data
     * @param string $msg
     * @param string $status
     * @param ?int $businessCode
     * @return Response
     */
    public function success(array $data = [], string $msg = '', string $status = 'success', ?int $businessCode = 0): Response
    {
        return $this->status($status, compact('data', 'msg'), null, $businessCode);
    }

    /**
     * 成功响应带消息
     * @param string $message
     * @param ?int $businessCode
     * @return Response
     */
    public function successRequest(string $message = '', ?int $businessCode = 0): Response
    {
        return $this->success([], $message, 'success', $businessCode);
    }

    /**
     * 请求成功，其他业务状态码响应
     * @param string $message
     * @param int $businessCode
     * @param array $data
     * @return Response
     */
    public function badSuccessRequest(string $message = 'Bad Request!', int $businessCode = 400, array $data = []): Response
    {
        $status = [
//            'status' => 'error',
            'code' => $businessCode,
        ];
        $res = ['msg' => $message];
        if ($data) {
            $res['data'] = $data;
        }
        $data = array_merge($status, $res);
        return $this->respond($data);
    }

    /**
     * 资源不存在
     * @param string $message
     * @param ?int $businessCode
     * @return Response
     */
    public function notFound(string $message = 'Not Found!', ?int $businessCode = 404): Response
    {
        return $this->failed($message, $businessCode, FoundationResponse::HTTP_NOT_FOUND);
    }

    /**
     * 清理日志消息，防止敏感信息泄露
     * @param string $message
     * @return string
     */
    private function sanitizeLogMessage(string $message): string
    {
        // 这里可以根据需要添加更多的清理逻辑
        return preg_replace('/[^\x20-\x7E]/', '', $message); // 移除非ASCII字符
    }

    /**
     * 验证HTTP状态码是否有效
     * @param int $httpStatusCode
     * @return bool
     */
    private function isValidHttpStatusCode(int $httpStatusCode): bool
    {
        return $httpStatusCode >= 100 && $httpStatusCode < 600;
    }
}
