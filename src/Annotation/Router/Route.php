<?php

namespace Landao\WebmanCore\Annotation\Router;

use Attribute;
use Illuminate\Support\Arr;
use Landao\WebmanCore\Annotation\Contracts\RouteAttribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route implements RouteAttribute
{
    public array|string $methods;
    public string $uri;
    public ?string $name = null;
    public array $middleware;
    /**
     *
     * @var string[]
     */
    public array $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    public function __construct(
        array|string   $methods,
        string  $uri,
        ?string $name = null,
        array|string   $middleware = [],
    )
    {
        $this->methods = array_map(
            static function (string $verb)  {
                return in_array($upperVerb = strtoupper($verb), ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'])
                    ? $upperVerb
                    : $verb;
            },
            Arr::wrap($methods)
        );
        $this->uri = $uri;
        $this->name = $name;
        $this->middleware = Arr::wrap($middleware);
    }
}