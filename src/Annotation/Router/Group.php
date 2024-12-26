<?php

namespace Landao\WebmanCore\Annotation\Router;

use Attribute;
use Landao\WebmanCore\Annotation\Contracts\RouteAttribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Group implements RouteAttribute
{
    public function __construct(
        public ?string $prefix = null,
        public ?string $as = null,
    ) {
    }
}