<?php

namespace Landao\WebmanCore\Annotation\Router;

use Attribute;
use Landao\WebmanCore\Annotation\Contracts\RouteAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Prefix implements RouteAttribute
{
    public function __construct(public string $prefix)
    {

    }
}