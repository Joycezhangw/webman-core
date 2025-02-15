<?php

namespace Landao\WebmanCore\Annotation\Router;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Any extends Route
{
    public function __construct(
        string       $uri,
        ?string      $name = null,
        array|string $middleware = [],
    )
    {
        parent::__construct(
            methods: $this->verbs,
            uri: $uri,
            name: $name,
            middleware: $middleware
        );
    }
}