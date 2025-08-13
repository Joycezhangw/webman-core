<?php

declare(strict_types=1);

namespace Landao\WebmanCore\Annotation\Validation;

use Attribute;
use Landao\WebmanCore\Enum\Rule;

/**
 * 匹配统一社会信用代码
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class UnifiedSocialCreditCode extends AbstractValidation
{
    public Rule $rule = Rule::UNIFIED_SOCIAL_CREDIT_CODE;
}