<?php

namespace Landao\WebmanCore\Middleware;

use Landao\WebmanCore\Helpers\CamelHelper;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\Middleware;

/**
 * 响应数据下划线转驼峰
 */
class ResponseCaseConverter extends Middleware
{
    public function process(Request $request, callable $handler): Response
    {
        // 先执行后续中间件获取响应
        $response = $handler($request);

        // 只处理JSON响应
        $contentType = $response->getHeader('Content-Type');
        if (strpos($contentType, 'application/json') !== false) {
            $body = $response->rawBody();
            $data = json_decode($body, true);

            // 检查JSON解码是否成功
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                // 使用CamelHelper转换键名
                $convertedData = CamelHelper::recursiveConvertNameCaseToCamel($data);

                // 更新响应内容
                $newBody = json_encode($convertedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $response = $response->withBody($newBody);
            }
        }
        return $response;
    }
}