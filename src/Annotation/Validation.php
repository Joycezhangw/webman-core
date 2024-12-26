<?php
declare(strict_types=1);

namespace Landao\WebmanCore\Annotation;

use Attribute;
use Landao\WebmanCore\Enum\Rule;

//https://github.com/CrastLin/laravel-annotation_v2/blob/main/src/Extra/Validate.php

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class Validation
{
    public function __construct(
        public string      $field = '',
        public Rule|string $rule = '',
        public string      $ruleValue = '',
        public string      $attribute = '',
        public string      $message = '',
        public string      $class = '',
        public array       $rules = [],
        public array       $messages = [],
        public array       $attributes = [],
    )
    {

    }
}