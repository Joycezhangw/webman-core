<?php

namespace Landao\WebmanCore\Facades;

use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\PendingRequest;
use Landao\WebmanCore\Http\HttpClient;

/**
 * @method static Response get(string $url, array $query = null)
 * @method static Response post(string $url, array $data = [])
 * @method static Response put(string $url, array $data = [])
 * @method static Response patch(string $url, array $data = [])
 * @method static Response delete(string $url, array $data = [])
 * @method static Response head(string $url, array $query = null)
 * @method static PendingRequest timeout(int $seconds)
 * @method static PendingRequest retry(int $times, int $sleep = 0, callable $when = null)
 * @method static PendingRequest withHeaders(array $headers)
 * @method static PendingRequest baseUrl(string $url)
 * @method static PendingRequest withToken(string $token)
 * @method static Response json(string $method, string $url, array $data = [])
 * @method static Response form(string $method, string $url, array $data = [])
 * @method static Response multipart(string $method, string $url, array $data = [])
 */
class Http
{
    public static function __callStatic($name, $arguments)
    {
        return HttpClient::$name(...$arguments);
    }
}