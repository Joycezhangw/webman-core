<?php

namespace Landao\WebmanCore\Http;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use InvalidArgumentException;

/**
 * Class HttpClient
 * @package Landao\WebmanCore\Http
 */
class HttpClient
{
    /**
     * @var Factory
     */
    protected static $factory;

    /**
     * 初始化 HTTP 客户端工厂
     */
    protected static function factory(): Factory
    {
        if (!static::$factory) {
            static::$factory = new Factory;
        }
        return static::$factory;
    }

    /**
     * 创建一个新的请求实例
     */
    public static function new(): PendingRequest
    {
        return static::factory()->beforeSending(function ($request) {
            // 这里可以添加全局请求前的处理逻辑
        })->withOptions([
            'verify' => false, // 默认禁用 SSL 验证，可以根据需要修改
        ]);
    }

    /**
     * 发起异步请求
     * @param callable $callback
     * @return array
     */
    public static function pool(callable $callback): array
    {
        return static::factory()->pool($callback);
    }

    /**
     * 发起 GET 请求
     * @param string $url
     * @param array|null $query
     * @return Response
     */
    public static function get(string $url, ?array $query = null): Response
    {
        return static::new()->get($url, $query);
    }

    /**
     * 发起 POST 请求
     * @param string $url
     * @param array $data
     * @return Response
     */
    public static function post(string $url, array $data = []): Response
    {
        return static::new()->post($url, $data);
    }

    /**
     * 发起 PUT 请求
     * @param string $url
     * @param array $data
     * @return Response
     */
    public static function put(string $url, array $data = []): Response
    {
        return static::new()->put($url, $data);
    }

    /**
     * 发起 PATCH 请求
     * @param string $url
     * @param array $data
     * @return Response
     */
    public static function patch(string $url, array $data = []): Response
    {
        return static::new()->patch($url, $data);
    }

    /**
     * 发起 DELETE 请求
     * @param string $url
     * @param array $data
     * @return Response
     */
    public static function delete(string $url, array $data = []): Response
    {
        return static::new()->delete($url, $data);
    }

    /**
     * 发起 HEAD 请求
     * @param string $url
     * @param array|null $query
     * @return Response
     */
    public static function head(string $url, ?array $query = null): Response
    {
        return static::new()->head($url, $query);
    }

    /**
     * 发起异步 GET 请求
     * @param string $url
     * @param array|null $query
     * @return PromiseInterface
     */
    public static function async(string $url, ?array $query = null): PromiseInterface
    {
        return static::new()->async()->get($url, $query);
    }

    /**
     * 发起带超时的请求
     * @param int $seconds
     * @return PendingRequest
     */
    public static function timeout(int $seconds): PendingRequest
    {
        return static::new()->timeout($seconds);
    }

    /**
     * 发起带重试的请求
     * @param int $times
     * @param int $sleep
     * @param callable|null $when
     * @return PendingRequest
     */
    public static function retry(int $times, int $sleep = 0, ?callable $when = null): PendingRequest
    {
        return static::new()->retry($times, $sleep, $when);
    }

    /**
     * 设置请求头
     * @param array $headers
     * @return PendingRequest
     */
    public static function withHeaders(array $headers): PendingRequest
    {
        return static::new()->withHeaders($headers);
    }

    /**
     * 设置基础 URL
     * @param string $url
     * @return PendingRequest
     */
    public static function baseUrl(string $url): PendingRequest
    {
        return static::new()->baseUrl($url);
    }

    /**
     * 设置认证信息
     * @param string $username
     * @param string $password
     * @return PendingRequest
     */
    public static function withBasicAuth(string $username, string $password): PendingRequest
    {
        return static::new()->withBasicAuth($username, $password);
    }

    /**
     * 设置 Bearer Token
     * @param string $token
     * @return PendingRequest
     */
    public static function withToken(string $token): PendingRequest
    {
        return static::new()->withToken($token);
    }

    /**
     * 发送 JSON 请求
     * @param string $method
     * @param string $url
     * @param array $data
     * @return Response
     */
    public static function json(string $method, string $url, array $data = []): Response
    {
        return static::new()->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->send($method, $url, ['json' => $data]);
    }

    /**
     * 发送表单请求
     * @param string $method
     * @param string $url
     * @param array $data
     * @return Response
     */
    public static function form(string $method, string $url, array $data = []): Response
    {
        return static::new()->asForm()->send($method, $url, ['form_params' => $data]);
    }

    /**
     * 发送多部分表单请求（文件上传）
     * @param string $method
     * @param string $url
     * @param array $data
     * @return Response
     */
    public static function multipart(string $method, string $url, array $data = []): Response
    {
        return static::new()->asMultipart()->send($method, $url, ['multipart' => $data]);
    }
}