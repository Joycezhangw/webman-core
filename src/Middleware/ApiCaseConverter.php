<?php

namespace Landao\WebmanCore\Middleware;

use Landao\WebmanCore\Helpers\CamelHelper;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class ApiCaseConverter implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $this->convertRequestNameCase($request);
        return $handler($request);
    }

    /**
     * 请求数据驼峰转下划线
     * @param Request $request
     */
    private function convertRequestNameCase(Request $request): void
    {
        // 获取所有参数
        $postParams = $request->post();
        $queryParams = $request->get();
//        $fileParams = $request->file();
//        $cookieParams = $request->cookie();

        // 转换参数名称
        $postParams = CamelHelper::recursiveConvertParameterNameCase($postParams);
        $queryParams = CamelHelper::recursiveConvertParameterNameCase($queryParams);
//        $fileParams = CamelHelper::recursiveConvertParameterNameCase($fileParams);
//        $cookieParams = CamelHelper::recursiveConvertParameterNameCase($cookieParams);

        // 更新请求参数
        $request->setPost($postParams);
        $request->setGet($queryParams);
//        $request->setFile($fileParams);
//        $request->setCookie($cookieParams);
    }
}
