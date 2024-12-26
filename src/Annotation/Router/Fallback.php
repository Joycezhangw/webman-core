<?php

namespace Landao\WebmanCore\Annotation\Router;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Fallback
{
    public function __construct()
    {
    }
}