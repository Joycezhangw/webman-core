<?php

namespace Landao\WebmanCore\Validation;

use Illuminate\Validation\Validator as Validation;
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

        // 使用数据、规则、错误消息和属性名创建验证器实例
        $this->validator = $validator->make($this->data, $this->rules, $this->messages, $this->attributes);

        // 返回验证器实例
        return $this->validator;
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