<?php

namespace Landao\WebmanCore\Annotation;

use Attribute;

/**
 * 枚举说明注解
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final class Description
{
    public function __construct(private string $value = '')
    {

    }
}