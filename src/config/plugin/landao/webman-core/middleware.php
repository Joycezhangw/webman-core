<?php

use Landao\WebmanCore\Middleware\RequestCaseConverter;
use Landao\WebmanCore\Validation\ValidatorMiddleware;
use Landao\WebmanCore\Middleware\DesensitizeMiddleware;
use Landao\WebmanCore\Middleware\ResponseCaseConverter;

/**
 * 中间是洋葱模型
 * 1、请求时候执行顺序：从上到下
 * 2、响应时候执行顺序：从下到上
 */
return [
    '@' => [
        RequestCaseConverter::class,//将请求参数转为驼峰命名
        ValidatorMiddleware::class,//注解验证
        ResponseCaseConverter::class,//将响应参数转为下划线命名
        DesensitizeMiddleware::class//注解脱敏
    ]
];