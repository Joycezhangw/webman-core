<?php

namespace Landao\WebmanCore\Annotation\Validation;

use Landao\WebmanCore\Enum\Rule;

/**
 *
 * 验证器注解基类
 *
 * @property string $field 字段名
 * @property string $attribute 显示名称
 * @property string $message 错误提示
 * @property string $ruleValue 规则值
 *
 * @author  https://github.com/CrastLin/laravel-annotation_v2
 * @package Landao\WebmanCore\Annotation\Validation
 */
abstract class AbstractValidation
{
    public Rule $rule;

    public function __construct(
        public string $field = '',
        public string $attribute = '',
        public string $message = '',
        public ?string $ruleValue = null,
    )
    {
    }
}