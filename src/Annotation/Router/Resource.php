<?php

namespace Landao\WebmanCore\Annotation\Router;

use Attribute;
use Landao\WebmanCore\Annotation\Contracts\RouteAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Resource implements RouteAttribute
{
    public function __construct(
        public string            $resource,
        public bool              $apiResource = false,
        public array|string|null $except = null,
        public array|string|null $only = null,
        public array|string|null $names = null,
        public array|string|null $parameters = null,
        public bool|null         $shallow = null,
    )
    {

    }
}