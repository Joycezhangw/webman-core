<?php

namespace Landao\WebmanCore\Annotation\Validation;

use Attribute;
use Landao\WebmanCore\Enum\Rule;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class DateEquals extends AbstractValidation
{
    public Rule $rule = Rule::DATE_EQUALS;
}