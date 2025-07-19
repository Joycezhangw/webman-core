# landao/webman-core 使用指南

- landao/webman-core 已实现的模块有 路由注解、验证器注解、数据迁移、数据填充、枚举注解、命令生(model, migrate, seeder,validate,enum,Repository,)。


## 1、环境和安装

* 在使用扩展前，先安装 `composer require webman/console`

### 1.1. 环境要求
- 由于使用了[PHP8.1和枚举特性](https://www.php.net/releases/8.1/en.php)，因此PHP版本最低要求 >= 8.1，框架版本使用的是 webman1.6.x ([webman1.6.x 中文文档](https://www.workerman.net/doc/webman/README.html))

## 2、注解使用

### 2.1. 路由注解

* 在控制器中使用以下注解，快速创建一条路由

> Tips: 需要 plugin.landao.webman-core.app.annotation.route.enable 开启 和 plugin.landao.webman-core.app.annotation.route.directories 扫描路径，否则将被排除扫描

```php
<?php

namespace app\controller;

use Landao\WebmanCore\Annotation\Router\Get;
use Landao\WebmanCore\Annotation\Router\Group;
use Landao\WebmanCore\Annotation\Router\Post;
use support\Request;
use support\Response;

#[Group('/user')]
class UserController
{
    #[Get('/index')]
    public function index()
    {
        try {
            // 假设这里有一些业务逻辑
            $response = [
                'status' => 'success',
                'message' => 'Welcome to the user page!'
            ];

            return json($response);
        } catch (\Exception $e) {
            // 记录异常日志
            Log::error($e->getMessage());

            // 返回错误响应
            return json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    #[Post('/test/123')]
    public function test(): Response
    {
        return json('ceshi');
    }


}
```
### 2.2 验证器
#### 2.2.1. 使用`Validation`注解快速开启一个验证器

```php
<?php

namespace app\controller;

use Landao\WebmanCore\Annotation\Router\Get;
use Landao\WebmanCore\Annotation\Router\Group;
use Landao\WebmanCore\Annotation\Router\Post;
use Landao\WebmanCore\Annotation\Validation;
use Landao\WebmanCore\Annotation\Validation\Required;
use Landao\WebmanCore\Annotation\Validation\Regex;
use Landao\WebmanCore\Http\FormRequest;
use support\Log;
use support\Request;
use support\Response;

#[Group('/user')]
class UserController
{

    #[Post('/test/123')]
//    #[Regex("message", ruleValue: "~^\w+$~", attribute: "留言")]
    #[Validation(field: "message", rule: "required", attribute: "留言", message: ":attribute不能为空")]
    #[Validation(field: "title", rule: "required", attribute: "标题", message: ":attribute不能为空")]
    public function test(FormRequest $request): Response
    {
//        $validator = $request->validate([
//            'title' => 'required|unique:posts|max:255',
//            'body' => 'required',
//        ],
//            [
//                'title.required' => ':attribute不能为空',
//            ], [
//                'title' => '标题',
//            ]);
        return json('ceshi');
    }


    #[Post('/test/12')]
    #[Validation(class: UserRequest::class)]
    public function test2(FormRequest $request):Response
    {
        return json('ceshi2');
    }
}
```
#### 2.2.1. 创建验证器类
不支持 unique、exists 等
```php
<?php

namespace app\controller;


use Landao\WebmanCore\Validation\Validate;

class UserRequest extends Validate
{

    protected array $rules = [
        "title" => ["required", "alpha_dash"],
        "message" => 'required|callback:checkSafeTags',
    ], $attributes = [
        "username" => "用户名",
        "message" => "留言",
    ];
    
    /**
    * 自定义回调验证 
    * @return bool
    */
    protected function checkSafeTags() :bool
    {
        if(str_contains($this->data['message'],'<script'))
            return false;
        return true;
    }
}
```

```php
<?php

namespace app\controller;

use Landao\WebmanCore\Annotation\Router\Get;
use Landao\WebmanCore\Annotation\Router\Group;
use Landao\WebmanCore\Annotation\Router\Post;
use Landao\WebmanCore\Annotation\Validation;
use Landao\WebmanCore\Annotation\Validation\Required;
use Landao\WebmanCore\Annotation\Validation\Regex;
use Landao\WebmanCore\Http\FormRequest;
use support\Log;
use support\Request;
use support\Response;

#[Group('/user')]
class UserController
{


    #[Post('/test/12')]
    #[Validation(class: UserRequest::class)]
    public function test2(FormRequest $request):Response
    {
        return json('ceshi2');
    }
}
```
## 3 数据迁移
### 3.1. 生成数据迁移文件

- fields 选项允许你指定表的字段结构
- plain 选项是一个布尔值选项，用于创建一个空白的迁移文件，不包含任何预设的表结构。当你需要编写一些特殊的数据库操作时，这个选项很有用。

```shell
# 创建带多个字段的用户表
php webman landao:make-migrate create_users_table --fields="name:string:nullable,email:string:unique,password:string,age:integer:unsigned"

# 创建带默认值的配置表
php webman landao:make-migrate create_settings_table --fields="key:string:unique,value:text,is_enabled:boolean:default:1"

# 添加字段到现有表
php webman landao:make-migrate add_columns_to_users_table --fields="phone:string:nullable,address:text"

# 删除字段到现有表

php webman landao:make-migrate delete_columns_to_users_table --fields="phone:string:nullable,address:text"

# 删除数据表的迁移文件
php webman landao:make-migrate drop_posts_table --fields="title:string,content:text"
```
### 3.2. 执行数据迁移
```shell

# 运行迁移
php webman landao:migrate

# 运行迁移并填充数据
php webman landao:migrate --seed

# 仅预览 SQL（不执行）
php webman landao:migrate --pretend

# 回滚最后一次迁移
php webman landao:migrate-rollback

# 回滚最后 3 次迁移
php webman landao:migrate-rollback --step=3

# 回滚所有迁移
php webman landao:migrate-rollback --all

```
### 3.3. 执行数据回滚迁移
```shell
# 回滚最后一次迁移
php webman landao:migrate-rollback

# 回滚最后 3 次迁移
php webman landao:migrate-rollback --step=3

# 回滚所有迁移
php webman landao:migrate-rollback --all

```
## 4、数据填充
### 4.1. 生成数据填充文件
> Tips: 首次使用seeder，请手动创建 DatabaseSeeder 基础数据填充类
```shell
# 首次使用seeder，请手动创建 DatabaseSeeder 基础数据填充类
php webman landao:make-seeder DatabaseSeeder


php webman landao:make-seeder UserSeeder


```

### 4.2.执行数据填充

```shell
# 运行默认的 DatabaseSeeder
php webman landao:seeder

# 运行指定的 Seeder
php webman landao:seeder --class=UserSeeder
```

## 5、命令生成文件

### 5.1 生成枚举文件
> 生成枚举类名自动追加后缀 `Enum`
```shell
# 主应用生成枚举, 默认生成在 app/enums 目录下, 
php webman landao:make-enum UserStatus

# 主应用生成枚举, 默认生成在 app/enums 目录下, 并指定子目录
php webman landao:make-enum System\\UserStatus

# webman 插件plugin 生成枚举, foo 生成在 plugin/foo/app/enums 目录下,
php webman landao:make-enum UserStatus --plugin=foo

# 多应用生成枚举,--multi-app=api 生成在 app/api/enums 目录下, 
php webman landao:make-enum UserStatus --multi-app=api
```

### 5.2 生成 Repository
> 注意：该命令会附带生成对应的 Model，生成类名自动追加后缀 `Repo`
```shell
# 主应用生成，默认生成在 app/repositories，app/models 目录下,
php webman landao:make-repo User

# 主应用生成，默认生成在 app/repositories/System，app/models/System 目录下,
php webman landao:make-repo Seytem\\User

# webman 插件plugin 生成, foo 生成在 plugin/foo/app/repositories/System，app/models/System 目录下,
php webman landao:make-repo User --plugin=foo

# 多应用生成,,--multi-app=api 生成在 app/api/repositories,app/api/models 目录下, 
php webman landao:make-repo User --multi-app=api
```
### 5.3 生成 Model
> 注意：该命令生成类名自动追加后缀 `Model`
```shell
# 主应用生成，默认生成在 app/models 目录下,
php webman landao:make-model User

# 主应用生成，默认生成在 app/models/System 目录下,
php webman landao:make-model Seytem\\User

# webman 插件plugin 生成, foo 生成在 papp/models/System 目录下,
php webman landao:make-model User --plugin=foo

# 多应用生成,,--multi-app=api 生成在 app/api/models 目录下, 
php webman landao:make-model User --multi-app=api
```

### 5.4 生成验证器
> 注意：该命令生成类名自动追加后缀 `Request`,注解使用：`#[Validation(class: UserRequest::class)]`
```shell
# 主应用生成，默认生成在 app/requests 目录下,
php webman landao:make-request User

# 主应用生成，默认生成在 app/requests/System 目录下,
php webman landao:make-request Seytem\\User

# webman 插件plugin 生成, foo 生成在 papp/requests/System 目录下,
php webman landao:make-request User --plugin=foo

# 多应用生成,,--multi-app=api 生成在 app/api/requests 目录下, 
php webman landao:make-request User --multi-app=api
```

## 6、Exception 异常

### 6.1. 异常接管
配置 `config/exception.php`
插件配置，例如：`/plugin/system/config/exception.php`
```php

return [
    '' => \Landao\WebmanCore\Exceptions\LanDaoException::class,//support\exception\Handler::class,
];
```

### 6.2. 输出错误信息

配置 `config/app.php` 开启调试模式
```json
{
    "code": 401,
    "msg": "账号或密码错误",
    "data": {
        "domain": "127.0.0.1:8787",
        "method": "POST",
        "request_url": "POST /admin-api/system/auth/login?tenantName=****&username=peadmin&password=123456qwe@A2&code=brmam&captchaKey=$2y$10$bNg0RzZkvzhkBr8XZiSDJOJTjwFc8pdTd7LJ1a5E6ywF4t.KUCwgm",
        "timestamp": "2025-07-14 16:41:21",
        "client_ip": "172.0.0.1",
        "request_param": {
            "tenant_name": "****",
            "username": "peadmin",
            "password": "****",
            "code": "brmam",
            "captcha_key": "$2y$10$bNg0RzZkvzhkBr8XZiSDJOJTjwFc8pdTd7LJ1a5E6ywF4t.KUCwgm"
        },
        "error_message": "账号或密码错误",
        "error_trace": [
            "#0 /www/php/landao/webman/plugin/system/app/controller/admin/auth/AuthController.php(54): plugin\\system\\app\\repositories\\UserRepo->doLogin('\\xE6\\x99\\xBA\\xE6\\xB1\\x87\\xE9\\x80\\x9A\\xE8\\xBD\\xAF\\xE4\\xBB\\xB6', 'peadmin', '****', 'WEB')",
            "#20 {main}"
        ],
        "file": "/www/php/landao/webman/plugin/system/app/repositories/UserRepo.php",
        "line": 64
    }
}
```
不开启调试，返回
```json
{
    "code": 401,
    "msg": "账号或密码错误",
    "data": []
}
```
### 6.3. 自定义业务错误码
```php
throw new UnauthorizedHttpException('账号或密码错误',['errorCode'=>40001]);
```
返回数据
```json
{
    "code": 40001,
    "msg": "账号或密码错误",
    "data": []
}
```

## 7、注解脱敏
```php
<?php

namespace plugin\system\app\controller;

use Landao\WebmanCore\Annotation\Desensitize;
use Landao\WebmanCore\Traits\ApiResponse;
use support\Request;


#[Desensitize(field: 'mobile', rule: 'mobile')]
#[Desensitize(field: 'email', rule: ['pattern' => '/(.{3})[\w\-]+@/', 'replacement' => '$1***@'])]
#[Desensitize(field: 'user_indo.mobile', rule: 'mobile')]
#[Desensitize(field: 'user_indo.email', rule: ['pattern' => '/(.{3})[\w\-]+@/', 'replacement' => '$1***@'])]
#[Desensitize(field: 'user_indo.chaer.mobile', rule: 'mobile')]
#[Desensitize(field: 'user_indo.chaer.email', rule: ['pattern' => '/(.{3})[\w\-]+@/', 'replacement' => '$1***@'])]
class IndexController
{
    use ApiResponse;

//    #[Desensitize(field: 'id_card', rule: ['pattern' => '/(\d{6})\d{8}(\d{4})/', 'replacement' => '$1********$2'])]
    public function index()
    {
//        return $this->success([
//            'mobile' => '13800138000',
//            'email' => 'test@example.com',
//            'id_card' => '110101199001011234',
//            'user_indo'=>[
//                'mobile' => '13800138000',
//                'email' => 'test@example.com',
//                'id_card' => '110101199001011234',
//                'chaer'=>[
//                    'mobile' => '13800138000',
//                    'email' => 'test@example.com',
//                    'id_card' => '110101199001011234',
//                ]
//            ]
//        ]);
//        return $this->success([
//            [
//                'mobile' => '13800138000',
//                'email' => 'test@example.com',
//                'id_card' => '110101199001011234',
//            ],
//            [
//                'mobile' => '13800138000',
//                'email' => 'test@example.com',
//                'id_card' => '110101199001011234',
//            ],
//            [
//                'mobile' => '13800138000',
//                'email' => 'test@example.com',
//                'id_card' => '110101199001011234',
//            ]
//        ]);
        return $this->success([
            'page'=>1,
            'list'=>[
                [
                    'mobile' => '13800138000',
                    'email' => 'test@example.com',
                    'id_card' => '110101199001011234',
                    'user_info'=>[
                        'mobile' => '13800138000',
                        'email' => 'test@example.com',
                    ]
                ],
                [
                    'mobile' => '13800138000',
                    'email' => 'test@example.com',
                    'id_card' => '110101199001011234',
                ],
                [
                    'mobile' => '13800138000',
                    'email' => 'test@example.com',
                    'id_card' => '110101199001011234',
                ]
            ]
        ]);
    }

}

```

