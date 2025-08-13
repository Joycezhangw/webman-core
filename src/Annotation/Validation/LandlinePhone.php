<?php

namespace Landao\WebmanCore\Annotation\Validation;

use Attribute;
use Landao\WebmanCore\Enum\Rule;

/**
 * 国内固定电话
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class LandlinePhone extends AbstractValidation
{
    public Rule $rule = Rule::MOBILE;
}