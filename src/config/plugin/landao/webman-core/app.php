<?php
return [
    'enable' => true,
    'passport' => [
        'check_captcha_cache_key' => 'captcha_uniq_id',
        'password_salt' => md5('landdao by webman')
    ],
    'security' => [
        'secret_key' => md5('landdao by webman'),
        'secret_iv' => str_repeat("\0", 16)
    ],
    'paginate' => [
        'page_size' => 20
    ],
    'generator' => [
        'basePath' => app_path(),
        'rootNamespace' => 'app\\',
        'paths' => [
            'model' => ['path' => 'app/model', 'generate' => false],
            'repository' => ['path' => 'app/repositories', 'generate' => false],
            'enum' => ['path' => 'app/enum', 'generate' => false],
            'request' => ['path' => 'app/request', 'generate' => false],
            //database
            'migration' => ['path' => 'database/migrations', 'generate' => true],
            'seeder' => ['path' => 'database/seeders', 'generate' => true],
        ]
    ],
    'listenORM' => [
        'enable' => true,
        'console' => false,
        'file' => true,
    ],
    'tenant' => [
        'enable' => true,
        'primary_key' => 'tenant_id',
        'model' => \app\model\TenantModel::class
    ],
    "annotation" => [
        //路由注解
        'route' => [
            'enable' => true,
            // 扫描的目录
            'directories' => [
                app_path('controller') => [
                ]
            ],
        ],
        // 验证器注解
        'validator' => [
            'enable' => true,
            // 验证失败处理方法
            'fail_handle' => function (Webman\Http\Request $request, string $message) {
                return response(json_encode(['status' => 'error', 'code' => 400, 'msg' => $message]), 400, ['Content-Type' => 'application/json;charset=utf-8']);
            }
        ],
    ],
    'exception_handler' => [
        // 不需要记录错误日志
        'dont_report' => [
            Landao\WebmanCore\Exceptions\BadRequestHttpException::class,
            Landao\WebmanCore\Exceptions\UnauthorizedHttpException::class,
            Landao\WebmanCore\Exceptions\ForbiddenHttpException::class,
            Landao\WebmanCore\Exceptions\NotFoundHttpException::class,
            Landao\WebmanCore\Exceptions\RouteNotFoundException::class,
            Landao\WebmanCore\Exceptions\TooManyRequestsHttpException::class,
            Landao\WebmanCore\Exceptions\ServerErrorHttpException::class,
            Tinywan\Jwt\Exception\JwtTokenException::class
        ],
        // 自定义HTTP状态码
        'status' => [
            'jwt_token' => 401, // 认证失败
            'jwt_token_expired' => 401, // 访问令牌过期
            'jwt_refresh_token_expired' => 402, // 刷新令牌过期
            'server_error' => 500, // 服务器内部错误
            'server_error_is_response' => false, // 是否响应服务器内部错误
            'type_error' => 400, // 参数类型错误码
            'type_error_is_response' => false, // 参数类型与预期声明的参数类型不匹配
        ],
        // 自定义响应消息
        'body' => [
            'code' => 500,
            'msg' => '服务器内部异常',
            'data' => null
        ],
        /** 异常报警域名标题 */
        'domain' => [
            'dev' => 'dev-api.landao.com', // 开发环境
            'test' => 'test-api.landao.com', // 测试环境
            'pre' => 'pre-api.landao.com', // 预发环境
            'prod' => 'api.landao.com',  // 生产环境
        ],
        /** 是否生产环境 。可以通过配置文件或者数据库读取返回 eg：return config('app.env') === 'prod';*/
        'is_prod_env' => function () {
            return false;
        },
    ],
    // response 驼峰转下划线
    'camel_case_response' => true
];