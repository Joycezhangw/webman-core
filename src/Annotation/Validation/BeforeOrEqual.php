<?php

namespace Landao\WebmanCore\Annotation\Validation;

use Attribute;
use Landao\WebmanCore\Enum\Rule;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class BeforeOrEqual extends AbstractValidation
{
    public Rule $rule = Rule::BEFORE_OR_EQUAL;
}