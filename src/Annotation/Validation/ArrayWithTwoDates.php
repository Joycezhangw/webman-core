<?php

namespace Landao\WebmanCore\Annotation\Validation;

use Attribute;
use Landao\WebmanCore\Enum\Rule;

/**
 * 验证字段是否为一维数组且包含两个日期格式的值
 * 默认时间格式为Y-m-d H:i:s
 *
 * 使用示例：
 *  1、默认时间格式：#[Validation(field: "created_at", rule: "array_with_two_dates", attribute: "创建时间", message: ":attribute必须包含两个时间数组")]
 *  2、可自定义时间格式：#[ArrayWithTwoDates(field: "created_at", attribute: "日期范围", message: "日期范围必须是包含两个日期的一维数组", format: "Y-m-d")]
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class ArrayWithTwoDates extends AbstractValidation
{
    public Rule $rule = Rule::ARRAY_WITH_TWO_DATES;

    /**
     * @param string $field 字段名
     * @param string $attribute 字段描述
     * @param string $message 错误消息
     * @param string $format 日期格式，默认为Y-m-d H:i:s
     */
    public function __construct(
        public string $field = '',
        public string $attribute = '',
        public string $message = '',
        public string $format = 'Y-m-d H:i:s'
    ) {
        // 设置验证规则的值，如果有格式参数则添加
        $this->ruleValue = $format ?  $format : 'Y-m-d H:i:s';
    }
}