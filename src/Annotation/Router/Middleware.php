<?php

namespace Landao\WebmanCore\Annotation\Router;

use Attribute;
use Illuminate\Support\Arr;
use Landao\WebmanCore\Annotation\Contracts\RouteAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Middleware implements RouteAttribute
{
    public array $middleware = [];

    public function __construct(string|array $middleware = [])
    {
        $this->middleware = Arr::wrap($middleware);
    }
}