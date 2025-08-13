<?php

namespace Landao\WebmanCore\Annotation\Validation;

use Attribute;
use Landao\WebmanCore\Enum\Rule;

/**
 * 邮政编码
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class PostalCode extends AbstractValidation
{
    public Rule $rule = Rule::POSTAL_CODE;
}