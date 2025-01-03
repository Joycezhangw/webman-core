<?php
return [
    'enable' => true,
    'passport' => [
        'check_captcha_cache_key' => 'captcha_uniq_id',
        'password_salt' => getenv('LANDAO_PASSPORT_PASSWORD_SALT', '617a6018f13c5b7a90d03757f3a0ce3f')
    ],
    'security' => [
        'security_key' => getenv('LANDAO_CRYPT_SECURITY_KEY', md5('webman_admin')),
        'security_iv' => getenv('LANDAO_CRYPT_SECURITY_IV', str_repeat("\0", 16))
    ],
    'paginate' => [
        'page_size' => 20
    ],
    'generator' => [
        'basePath' => app_path(),
        'rootNamespace' => 'app\\',
        'paths' => [
            'model' => ['path' => 'app/models', 'generate' => false],
            'repository' => ['path' => 'app/repositories', 'generate' => false],
            'enum' => ['path' => 'app/enums', 'generate' => false],
            'request' => ['path' => 'app/requests', 'generate' => false],
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
                return json(['code' => 400, 'msg' => $message]);
            }
        ],
    ]
];