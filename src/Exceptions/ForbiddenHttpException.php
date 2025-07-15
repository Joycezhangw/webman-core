<?php
/**
 * @desc 访问接口资源授权（authorization）：指允许访问某一个资源的权限
 *
 * @see https://tools.ietf.org/html/rfc7231#section-6.5.3
 * @author Tinywan(ShaoBo Wan)
 * @email 756684177@qq.com
 * @date 2022/3/6 14:14
 */
declare(strict_types=1);

namespace Landao\WebmanCore\Exceptions;

class ForbiddenHttpException extends BaseException
{
    /**
     * @var int
     */
    public int $statusCode = 403;

    /**
     * 业务错误码
     * @var int
     */
    public int $errorCode = 403;

    /**
     * @link 解决跨域问题
     * @var array
     */
    public array $header = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Allow-Headers' => 'Authorization,Content-Type,If-Match,If-Modified-Since,If-None-Match,If-Unmodified-Since,X-Requested-With,Origin',
        'Access-Control-Allow-Methods' => 'GET,POST,PUT,DELETE,OPTIONS',
    ];

    /**
     * @var string
     */
    public string $errorMessage = '对不起，您没有该接口访问权限，请联系管理员';
}