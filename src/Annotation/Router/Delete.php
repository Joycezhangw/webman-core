<?php

namespace Landao\WebmanCore\Annotation\Router;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Delete extends Route
{
    public function __construct(
        string       $uri,
        ?string      $name = null,
        array|string $middleware = [],
    )
    {
        parent::__construct(
            methods: ['delete'],
            uri: $uri,
            name: $name,
            middleware: $middleware,
        );
    }
}