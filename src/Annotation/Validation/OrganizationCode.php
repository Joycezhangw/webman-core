<?php

declare(strict_types=1);

namespace Landao\WebmanCore\Annotation\Validation;

use Attribute;
use Landao\WebmanCore\Enum\Rule;

/**
 * 组织机构代码格式验证器
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class OrganizationCode extends AbstractValidation
{
    public Rule $rule = Rule::ORGANIZATION_CODE;
}