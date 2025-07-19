<?php

namespace Landao\WebmanCore\Middleware;

use Landao\WebmanCore\Helpers\CamelHelper;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * 请求案例转换器中间件
 * 该中间件用于将请求中的参数名称从驼峰命名法转换为下划线命名法
 */
class RequestCaseConverter implements MiddlewareInterface
{

    /**
     * 处理请求
     *
     * @param Request $request 入参，包含请求信息
     * @param callable $handler 请求处理程序
     * @return Response 返回处理后的响应
     */
    public function process(Request $request, callable $handler): Response
    {
        $this->convertRequestNameCase($request);
        return $handler($request);
    }

    /**
     * 请求数据驼峰转下划线
     * 该方法将请求的POST和GET参数中的驼峰命名转换为下划线命名
     *
     * @param Request $request 入参，包含请求信息
     */
    private function convertRequestNameCase(Request $request): void
    {
        // 获取所有参数
        $postParams = $request->post();
        $queryParams = $request->get();

        // 转换参数名称（驼峰转蛇形）
        $postParams = CamelHelper::recursiveConvertParameterNameCase($postParams);
        $queryParams = CamelHelper::recursiveConvertParameterNameCase($queryParams);

        // 更新请求参数（仅支持POST和GET）
        $request->setPost($postParams);
        $request->setGet($queryParams);

        // 注意：Webman Request类不支持setFile()和setCookie()方法
        // 文件上传和Cookie参数无法通过中间件修改
    }
}