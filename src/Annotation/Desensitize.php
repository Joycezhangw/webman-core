<?php

namespace Landao\WebmanCore\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Desensitize
{
    public function __construct(
        public string       $field,
        public string|array $rule
    )
    {
    }
}