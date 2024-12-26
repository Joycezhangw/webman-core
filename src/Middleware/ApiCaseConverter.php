<?php

namespace Landao\WebmanCore\Middleware;

use Landao\WebmanCore\Helpers\CamelHelper;
use Symfony\Component\HttpFoundation\ParameterBag;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class ApiCaseConverter implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $this->convertRequestNameCase($request);
        $response = $handler($request);
        $this->convertResponseNameCase($response);
        return $response;
    }

    /**
     * 请求数据驼峰转下划线
     * @param $request
     */
    private function convertRequestNameCase($request): void
    {
        $this->convertParameterNameCase($request->request);;
        $this->convertParameterNameCase($request->query);;
        $this->convertParameterNameCase($request->files);;
        $this->convertParameterNameCase($request->cookies);;
    }

    /**
     * 将驼峰命名转下划线命名
     * @param ParameterBag $parameterBag
     */
    private function convertParameterNameCase($parameterBag): void
    {
        $parameters = $parameterBag->all();
        $parameterBag->replace(CamelHelper::recursiveConvertParameterNameCase($parameters));
    }


    /**
     * 响应数据下划线转驼峰
     * @param $response
     */
    private function convertResponseNameCase($response): void
    {
        $content = $response->getContent();
        $json = json_decode($content, true);
        if (is_array($json)) {
            $json = CamelHelper::recursiveConvertNameCaseToCamel($json);
            $response->setContent(json_encode($json));
        }
    }

}