<?php

namespace Landao\WebmanCore\Validation;

use Illuminate\Validation\Validator as Validation;
use Landao\WebmanCore\Enum\Rule;
use Webman\App;

/**
 * 验证器基类
 *
 * @author  https://github.com/CrastLin/laravel-annotation_v2
 * @package Landao\WebmanCore\Validation
 */
class Validate implements \Illuminate\Contracts\Validation\Validator
{
    // 允许访问的对象
    protected array $allowAccessProperties = ['*'];

    protected bool $fails = false;

    protected Validation $validator;

    protected array $data = [], $rules = [],
        $messages = [
        'required' => ':attribute不能为空',
        'numeric' => ':attribute必须为数字类型',
        'regex' => ':attribute格式不正确',
        'alpha_num' => ':attribute必须为字母或数字类型',
        'in' => ':attribute必须为指定数据:values',
        'email' => '邮箱格式不正确',
        'callback' => ':attribute为空或不正确',
        'integer' => ':attribute必须为整数',
        'digits_between' => ':attribute 必须在 :min 和 :max 位之间',
        'between' => ':attribute不在允许范围',
        'mix' => ':attribute最小值为:value',
        'max' => ':attribute最大值为:value',
        'gt' => ':attribute必须大于:value',
        'lt' => ':attribute必须小于:value',
        'gte' => ':attribute必须大于等于:value',
        'lte' => ':attribute必须小于等于:value',
        'chs' => ':attribute必须为中文',
        'mobile' => ':attribute格式不正确，应为国内11位手机号',
        'mobile_international' => ':attribute格式不正确，应为国际电话号码格式',
        'landline_phone' => ':attribute格式不正确，应为国内固定电话格式(如:010-12345678)',
        'array_with_two_dates' => ':attribute必须是包含两个有效日期的一维数组',
        'array_with_two_dates.not_array' => ':attribute必须是数组',
        'array_with_two_dates.not_one_dimensional' => ':attribute必须是一维数组',
        'array_with_two_dates.wrong_length' => ':attribute必须包含两个元素',
        'array_with_two_dates.invalid_date' => ':attribute包含无效的日期格式',
        'id_card' => ':attribute格式不正确，应为15-18位身份证号码',
        'postal_code' => ':attribute格式不正确，应为6位数字邮政编码',
        'bank_card' => ':attribute格式不正确，应为10-22位数字银行卡号',
        'license_plate' => ':attribute格式不正确，应为有效的车牌号格式',
        'organization_code' => ':attribute格式不正确，应为有效的组织机构代码格式',
        'unified_social_credit_code' => ':attribute格式不正确，应为有效的统一社会信用代码格式',
    ],
        $attributes = [],
        $errors;

    public function __construct(array ...$ruleList)
    {
        if (!empty($ruleList)) {
            $rules = $ruleList[0] ?? [];
            $messages = $ruleList[1] ?? [];
            $attributes = $ruleList[2] ?? [];
            $this->rules = !empty($rules) ? array_merge($this->rules, $rules) : $this->rules;
            $this->messages = !empty($messages) ? array_merge($this->messages, $messages) : $this->messages;
            $this->attributes = !empty($attributes) ? array_merge($this->attributes, $attributes) : $this->attributes;
        }
        $this->check();
    }

    /**
     * 创建并返回一个验证器实例
     *
     * 该方法主要用于创建一个验证器实例，用于验证数据是否符合规定的规则
     * 它扩展了验证器的功能，添加了一个自定义的验证规则'mobile'，用于验证手机号格式
     *
     * @return \Illuminate\Contracts\Validation\Validator 返回一个验证器实例，用于执行验证操作
     */
    public function validate(): \Illuminate\Contracts\Validation\Validator
    {
        // 获取验证器工厂实例，用于创建验证器
        $validator = App::container()->get(ValidatorFactory::class);

        // 扩展验证器，添加自定义的'callback'验证规则
        $validator->extend('callback', function (string $attribute, $value, array $parameters) {
            // 从参数中提取出回调动作
            $action = array_shift($parameters);
            // 将验证值和属性名添加到参数列表的开头
            array_unshift($parameters, $value, $attribute);
            // 调用对象的指定方法执行验证逻辑
            return call_user_func_array([$this, $action], $parameters);
        }, ':attribute验证失败');

        // 扩展国内手机号验证
        $validator->extend('mobile', [$this, 'validateMobile'], ':attribute格式不正确');

        // 扩展国际手机号格式验证器
        $validator->extend('mobile_international', [$this, 'validateMobileInternational'], ':attribute格式不正确');

        // 扩展国内固定电话格式验证器
        $validator->extend('landline_phone', [$this, 'validateLandlinePhone'], ':attribute格式不正确');

        // 扩展验证器，添加自定义的'array_with_two_dates'验证规则
        $validator->extend('array_with_two_dates', [$this, 'validateArrayWithTwoDates'], ':attribute必须是一维数组且包含两个日期格式的值');

        // 扩展身份证格式验证器
        $validator->extend('id_card', [$this, 'validateIdCard'], ':attribute格式不正确');

        // 扩展邮政编码格式验证器
        $validator->extend('postal_code', [$this, 'validatePostalCode'], ':attribute格式不正确');

        // 扩展银行卡号格式验证器
        $validator->extend('bank_card', [$this, 'validateBankCard'], ':attribute格式不正确');

        // 扩展车牌号格式验证器
        $validator->extend('license_plate', [$this, 'validateLicensePlate'], ':attribute格式不正确');

        // 扩展组织机构代码格式验证器
        $validator->extend('organization_code', [$this, 'validateOrganizationCode'], ':attribute格式不正确');

        // 扩展统一社会信用代码格式验证器
        $validator->extend('unified_social_credit_code', [$this, 'validateUnifiedSocialCreditCode'], ':attribute格式不正确');

        // 使用数据、规则、错误消息和属性名创建验证器实例
        $this->validator = $validator->make($this->data, $this->rules, $this->messages, $this->attributes);

        // 返回验证器实例
        return $this->validator;
    }

    /**
     * 验证字段是否为一维数组且包含两个日期格式的值
     *
     * @param string $attribute 字段名
     * @param mixed $value 字段值
     * @param array $parameters 验证参数
     * @return bool 验证是否通过
     */
    protected function validateArrayWithTwoDates(string $attribute, $value, array $parameters): bool
    {
        // 提前检查是否为数组
        if (!is_array($value)) {
            return false;
        }

        // 提前检查数组长度
        $count = count($value);
        if ($count !== 2) {
            return false;
        }

        // 检查是否为一维数组
        if ($count !== count($value, COUNT_RECURSIVE)) {
            return false;
        }

        // 获取日期格式，默认为Y-m-d
        $format = $parameters[0] ?? 'Y-m-d H:i:s';

        // 提取数组元素进行验证（避免foreach循环）
        $date1 = reset($value);
        $date2 = next($value);

        // 验证第一个日期
        $dateTime1 = \DateTime::createFromFormat($format, $date1);
        if (!$dateTime1 || $dateTime1->format($format) !== $date1) {
            return false;
        }

        // 验证第二个日期
        $dateTime2 = \DateTime::createFromFormat($format, $date2);
        if (!$dateTime2 || $dateTime2->format($format) !== $date2) {
            return false;
        }

        return true;
    }

    /**
     * 验证手机号格式
     * @param string $attribute 字段名
     * @param mixed $value 字段值
     * @param array $parameters 验证参数
     * @return bool 验证是否通过
     */
    public function validateMobile(string $attribute, $value, array $parameters): bool
    {
        // 使用Rule::MOBILE定义的正则表达式
        $pattern = Rule::MOBILE->value;
        // 提取正则表达式部分（去掉'regex:'前缀）
        $pattern = substr($pattern, 6);
        return is_string($value) && preg_match($pattern, $value);
    }

    /**
     * 验证国际手机号格式
     * @param string $attribute 字段名
     * @param mixed $value 字段值
     * @param array $parameters 验证参数
     * @return bool 验证是否通过
     */
    public function validateMobileInternational(string $attribute, $value, array $parameters): bool
    {
        // 使用Rule::MOBILE_INTERNATIONAL定义的正则表达式
        $pattern = Rule::MOBILE_INTERNATIONAL->value;
        // 提取正则表达式部分（去掉'regex:'前缀）
        $pattern = substr($pattern, 6);
        return is_string($value) && preg_match($pattern, $value);
    }

    /**
     * 验证国内固定电话格式
     * @param string $attribute 字段名
     * @param mixed $value 字段值
     * @param array $parameters 验证参数
     * @return bool 验证是否通过
     */
    public function validateLandlinePhone(string $attribute, $value, array $parameters): bool
    {
        // 使用Rule::LANDLINE_PHONE定义的正则表达式
        $pattern = Rule::LANDLINE_PHONE->value;
        // 提取正则表达式部分（去掉'regex:'前缀）
        $pattern = substr($pattern, 6);
        return is_string($value) && preg_match($pattern, $value);
    }

    /**
     * 验证身份证格式
     * @param string $attribute 字段名
     * @param mixed $value 字段值
     * @param array $parameters 验证参数
     * @return bool 验证是否通过
     */
    public function validateIdCard(string $attribute, $value, array $parameters): bool
    {
        // 使用Rule::ID_CARD定义的正则表达式
        $pattern = Rule::ID_CARD->value;
        // 提取正则表达式部分（去掉'regex:'前缀）
        $pattern = substr($pattern, 6);
        return is_string($value) && preg_match($pattern, $value);
    }


    /**
     * 验证邮政编码格式
     * @param string $attribute 字段名
     * @param mixed $value 字段值
     * @param array $parameters 验证参数
     * @return bool 验证是否通过
     */
    public function validatePostalCode(string $attribute, $value, array $parameters): bool
    {
        // 使用Rule::POSTAL_CODE定义的正则表达式
        $pattern = Rule::POSTAL_CODE->value;
        // 提取正则表达式部分（去掉'regex:'前缀）
        $pattern = substr($pattern, 6);
        return is_string($value) && preg_match($pattern, $value);
    }

    /**
     * 验证银行卡号格式
     * @param string $attribute 字段名
     * @param mixed $value 字段值
     * @param array $parameters 验证参数
     * @return bool 验证是否通过
     */
    public function validateBankCard(string $attribute, $value, array $parameters): bool
    {
        // 使用Rule::BANK_CARD定义的正则表达式
        $pattern = Rule::BANK_CARD->value;
        // 提取正则表达式部分（去掉'regex:'前缀）
        $pattern = substr($pattern, 6);
        return is_string($value) && preg_match($pattern, $value);
    }

    /**
     * 验证车牌号格式
     * @param string $attribute 字段名
     * @param mixed $value 字段值
     * @param array $parameters 验证参数
     * @return bool 验证是否通过
     */
    public function validateLicensePlate(string $attribute, $value, array $parameters): bool
    {
        // 使用Rule::LICENSE_PLATE定义的正则表达式
        $pattern = Rule::LICENSE_PLATE->value;
        // 提取正则表达式部分（去掉'regex:'前缀）
        $pattern = substr($pattern, 6);
        return is_string($value) && preg_match($pattern, $value);
    }

    /**
     * 验证组织机构代码格式
     * @param string $attribute 字段名
     * @param mixed $value 字段值
     * @param array $parameters 验证参数
     * @return bool 验证是否通过
     */
    public function validateOrganizationCode(string $attribute, $value, array $parameters): bool
    {
        // 使用Rule::ORGANIZATION_CODE定义的正则表达式
        $pattern = Rule::ORGANIZATION_CODE->value;
        // 提取正则表达式部分（去掉'regex:'前缀）
        $pattern = substr($pattern, 6);
        return is_string($value) && preg_match($pattern, $value);
    }

    /**
     * 验证统一社会信用代码格式
     * @param string $attribute 字段名
     * @param mixed $value 字段值
     * @param array $parameters 验证参数
     * @return bool 验证是否通过
     */
    public function validateUnifiedSocialCreditCode(string $attribute, $value, array $parameters): bool
    {
        // 使用Rule::UNIFIED_SOCIAL_CREDIT_CODE定义的正则表达式
        $pattern = Rule::UNIFIED_SOCIAL_CREDIT_CODE->value;
        // 提取正则表达式部分（去掉'regex:'前缀）
        $pattern = substr($pattern, 6);
        return is_string($value) && preg_match($pattern, $value);
    }

    public function check(): void
    {
        if (empty($this->rules))
            throw new \Exception(static::class . ': 未配置rules数据');
        if (empty($this->messages))
            throw new \Exception(static::class . ': 未配置messages数据');
    }

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * set validator's messages
     *
     * @param array $message
     * @param bool $recover
     */
    public function setMessage(array $message, bool $recover = false): void
    {
        if (!empty($message))
            $this->messages = $recover ? $message : array_merge($this->messages, $message);
    }

    public function setCallbackMessage(string $field, string $message): void
    {
        $this->validator->setFallbackMessages(["{$field}.callback" => $message]);
    }

    public function append(string $field, $rule, string $attribute, array $message = []): void
    {
        $this->rules = array_merge($this->rules, [$field => $rule]);
        $this->attributes[$field] = $attribute;
        $this->setMessage($message);
    }

    public function __get(string $name)
    {
        if (!empty($this->allowAccessProperties) && (in_array('*', $this->allowAccessProperties) || in_array($name, $this->allowAccessProperties)))
            return !empty($this->{$name}) ? $this->{$name} : null;

        return null;
    }

    public function __isset($name)
    {
        if (!empty($this->allowAccessProperties) && (in_array('*', $this->allowAccessProperties) || in_array($name, $this->allowAccessProperties)))
            return isset($this->{$name});
        return false;
    }

    public function errors()
    {
        // TODO: Implement errors() method.
        return $this;
    }

    public function sometimes($attribute, $rules, callable $callback)
    {
        // TODO: Implement sometimes() method.
    }


    public function validated()
    {
        return empty($this->errors);
    }

    public function failed()
    {
        return $this->fails;
    }

    public function fails()
    {
        return $this->fails;
    }

    public function after($callback)
    {
        // TODO: Implement after() method.
    }


    public function getMessageBag()
    {
        // TODO: Implement getMessageBag() method.
    }

}