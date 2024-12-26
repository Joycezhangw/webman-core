<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Validation;

namespace Landao\WebmanCore\Annotation\Validation;

use Attribute;
use Landao\WebmanCore\Enum\Rule;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class Same extends AbstractValidation
{
    public Rule $rule = Rule::SAME;
}
