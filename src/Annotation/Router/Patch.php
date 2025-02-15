<?php

namespace Landao\WebmanCore\Annotation\Router;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Patch extends Route
{
    public function __construct(
        string       $uri,
        ?string      $name,
        array|string $middleware = [],
    )
    {
        parent::__construct(
            methods: ['patch'],
            uri: $uri,
            name: $name,
            middleware: $middleware,
        );
    }
}