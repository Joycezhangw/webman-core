<?php
declare(strict_types=1);

namespace Landao\WebmanCore\Annotation\Validation;

use Attribute;
use Landao\WebmanCore\Enum\Rule;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class RequiredUnless extends AbstractValidation
{
    public Rule $rule = Rule::REQUIRED_UNLESS;
}
