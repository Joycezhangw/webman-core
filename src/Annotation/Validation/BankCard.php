<?php

declare(strict_types=1);

namespace Landao\WebmanCore\Annotation\Validation;

use Attribute;
use Landao\WebmanCore\Enum\Rule;

/**
 * 银行卡
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class BankCard extends AbstractValidation
{
    public Rule $rule = Rule::BANK_CARD;
}