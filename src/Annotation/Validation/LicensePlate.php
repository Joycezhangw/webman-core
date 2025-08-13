<?php

declare(strict_types=1);

namespace Landao\WebmanCore\Annotation\Validation;

use Attribute;
use Landao\WebmanCore\Enum\Rule;

/**
 * 车牌号格式验证器
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class LicensePlate extends AbstractValidation
{
    public Rule $rule = Rule::LICENSE_PLATE;
}