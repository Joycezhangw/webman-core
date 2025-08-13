<?php

namespace Landao\WebmanCore\Validation;

use Landao\WebmanCore\Annotation\Validation;
use Landao\WebmanCore\Enum\Rule;
use Landao\WebmanCore\Exceptions\AnnotationException;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use ReflectionMethod;

class ValidatorMiddleware implements MiddlewareInterface
{
    /**
     * 处理请求并应用验证器
     *
     * 本函数用于处理传入的请求，在执行请求的处理器之前，根据配置和注解对请求数据进行验证
     * 如果验证失败，则返回错误信息；如果验证成功，则继续执行请求的处理器
     *
     * @param Request $request 请求对象，包含请求的各种信息，如控制器、动作等
     * @param callable $handler 请求的处理器，一个可调用的函数或方法
     * @return Response 返回处理后的响应对象
     */
    public function process(Request $request, callable $handler): Response
    {
        //检查是否启用了验证器功能，如果未启用则直接执行请求的处理器
        if (!config('plugin.landao.webman-core.app.annotation.validator.enable')) {
            return $handler($request);
        }
        //检查请求的控制器和动作是否存在，如果不存在则直接执行请求的处理器
        if (!$request->controller || !method_exists($request->controller, $request->action)) {
            return $handler($request);
        }
        //获取验证器列表
        $reflectionMethod = new ReflectionMethod($request->controller, $request->action);
        $parameterName = $reflectionMethod->getName();
        $attributes = $reflectionMethod->getAttributes();

        //初始化参数验证数组
        $parameterValidation = [];
        //遍历方法的所有注解，寻找验证器注解
        foreach ($attributes as $attribute) {
            //实例化注解对象
            $annotation = $attribute->newInstance();
            //如果不是对象则跳过
            if (!is_object($annotation)) continue;
            //如果是验证器注解，则创建一个新的验证器对象，并添加到参数验证数组中
            if ($attribute->getName() == Validation::class || $annotation instanceof Validation\AbstractValidation) {
                $validate = new \stdClass();
                $this->matchAllValidation($validate, $annotation, $attribute, $parameterName);
                $parameterValidation[] = $validate;
            }
        }
        //如果有参数验证，则执行验证逻辑
        if (!empty($parameterValidation)) {
            //执行验证并获取结果
            $result = $this->runValidation($parameterValidation, $request->all());
            //如果验证失败，则返回错误信息
            if (!empty($result))
                return (config('plugin.landao.webman-core.app.annotation.validator.fail_handle') ?? function (Request $request, string $message) {
                    return response(json_encode(['code' => 400, 'msg' => $message]), 200, ['Content-Type' => 'application/json;charset=utf-8']);
                })($request, (string)$result);
        }
        //验证通过，执行请求的处理器
        return $handler($request);
    }

    /**
     * 根据注解和反射属性初始化验证器对象的属性
     *
     * 此函数的目的是将注解信息和反射属性映射到验证器对象的相应属性上
     * 它处理的是验证规则及其相关属性的设置，确保验证器对象可以根据这些信息执行正确的验证逻辑
     *
     * @param \stdClass $validate 验证器对象，其属性将被初始化
     * @param object $annotation 注解对象，包含验证规则及其属性
     * @param \ReflectionAttribute $attribute 反射属性对象，用于获取验证器类名
     * @param string $name 字段名，用作备用验证字段名
     */
    private function matchAllValidation(\stdClass $validate, object $annotation, \ReflectionAttribute $attribute, string $name): void
    {
        // 设置验证器的类名
        $validate->validator = $attribute->getName();

        // 设置验证字段名，优先使用注解中的field属性，若为空则使用$name参数值
        $validate->field = !empty($annotation->field) ? $annotation->field : $name;

        // 设置验证规则，如果rule属性是Rule实例，则使用其value属性，否则直接使用rule属性值
        $validate->rule = $annotation->rule instanceof Rule ? $annotation->rule->value : $annotation->rule;

        // 设置验证属性名，优先使用注解中的attribute属性，若为空则使用field属性或$name参数值
        $validate->attribute = !empty($annotation->attribute) ? $annotation->attribute : $annotation->field;

        // 设置验证错误消息，如果注解中未提供，则默认为空字符串
        $validate->message = $annotation->message ?? '';

        // 如果注解中提供了ruleValue属性，则设置验证规则值
        if (isset($annotation->ruleValue))
            $validate->ruleValue = $annotation->ruleValue;

        // 如果反射属性的类名是Validation，则设置验证器的类、规则、消息和属性
        if ($attribute->getName() == Validation::class) {
            // 设置验证器类名，如果注解中未提供，则默认为空字符串
            $validate->class = $annotation->class ?? '';

            // 设置验证规则数组，如果注解中未提供，则默认为空数组
            $validate->rules = $annotation->rules ?? [];

            // 设置验证错误消息数组，如果注解中未提供，则默认为空数组
            $validate->messages = $annotation->messages ?? [];

            // 设置验证属性名数组，如果注解中未提供，则默认为空数组
            $validate->attributes = $annotation->attributes ?? [];
        }
    }

    /**
     * 执行验证逻辑
     *
     * 该方法接收一个验证器列表和一组数据，然后根据列表中的验证规则对数据进行验证
     * 如果验证失败，返回第一条错误信息；如果验证成功，返回空字符串
     *
     * @param array $validatorList 验证器列表，包含一系列验证规则和配置
     * @param array $data 需要验证的数据，通常是一个关联数组
     * @return string 返回第一条错误信息或者空字符串，表示验证通过
     */
    private function runValidation(array $validatorList, array $data): string
    {
        // 初始化规则、消息和属性数组
        $rules = $messages = $attributes = [];

        // 遍历验证器列表，处理每个验证器
        foreach ($validatorList as $validator) {
            // 分解验证器类名，获取规则类名
            $cs = explode('\\', $validator->validator);
            $ruleClass = array_pop($cs);

            // 判断是否为Validation类
            if ($ruleClass == 'Validation') {
                // 如果指定了验证类，进行验证类存在性和正确性检查
                if (!empty($validator->class)) {
                    $class = '\\' . $validator->class;
                    if (!class_exists($class)) {
                        throw new AnnotationException("Validation Class: {$class} does not exist");
                    }
                    $validate = new $class();
                    if (!$validate instanceof Validate) {
                        throw new AnnotationException("Validation Class: {$class} must implement \landao\WebmanCore\Validation\Validate");
                    }
                    $validate = $validate->setData($data)->validate();
                    if ($validate->fails()) {
                        return $validate->errors()->first();
                    }
                    continue;
                }

                // 处理规则
                if (!empty($validator->rules)) {
                    foreach ($validator->rules as $field => $rule) {
                        $ruleList = !empty($rule) ? (is_array($rule) ? $rule : explode('|', $rule)) : [];
                        if (isset($rules[$field])) {
                            $rules[$field] = array_merge($rules[$field], $ruleList);
                        } else {
                            $rules[$field] = $ruleList;
                        }
                    }
                } else {
                    $rulesList = $rules[$validator->field] ?? [];
                    $ruleList = !empty($validator->rule) ? explode('|', $validator->rule) : [];
                    $rules[$validator->field] = array_merge($rulesList, $ruleList);
                }

                // 处理消息
                if (!empty($validator->messages)) {
                    $messages = array_merge($messages, $validator->messages);
                } elseif (!empty($validator->message)) {
                    $messages["{$validator->field}.{$validator->rule}"] = $validator->message;
                }

                // 处理属性
                if (!empty($validator->attributes)) {
                    foreach ($validator->attributes as $field => $attribute) {
                        $attributes[$field] = $attribute ?: ($attributes[$field] ?? '');
                    }
                    $attributes = array_merge($attributes, $validator->attributes);
                } elseif (!empty($validator->attribute)) {
                    $attributes[$validator->field] = $validator->attribute;
                }
            } else {
                // 如果不是Validation类，处理其他类型的验证规则
                $rule = $validator->rule ?? $this->humpToUnderline($ruleClass);
                if (!isset($rules[$validator->field])) {
                    $rules[$validator->field] = [];
                }
                if (isset($validator->ruleValue)) {
                    $rule .= ":{$validator->ruleValue}";
                }
                $rules[$validator->field][] = $rule;
                $ruleName = explode(':', $rule)[0];
                $messages["{$validator->field}.{$ruleName}"] = $validator->message;
                if (empty($attributes[$validator->field]) || $attributes[$validator->field] == $validator->field) {
                    $attributes[$validator->field] = !empty($validator->attribute) ? $validator->attribute : $validator->field;
                }
            }
        }

        // 如果存在规则，进行验证
        if (!empty($rules)) {
            $messages = array_filter($messages, function ($message) {
                return !empty($message);
            });
            $validate = new Validate($rules, $messages, $attributes);
            $validate = $validate->setData($data)->validate();
            if ($validate->fails()) {
                return $validate->errors()->first();
            }
        }

        // 如果所有验证通过，返回空字符串
        return '';
    }


    /**
     * 将驼峰命名转换为下划线命名
     *
     * 该函数接受一个字符串参数，并将其从驼峰命名法转换为下划线命名法
     * 如果$toUpper参数为true，则将结果转换为大写，否则转换为小写
     *
     * @param string $string 需要转换的字符串
     * @param bool|null $toUpper 是否将转换后的字符串转为大写，默认为false
     *
     * @return string 转换后的字符串
     */
    private function humpToUnderline(string $string, ?bool $toUpper = false): string
    {
        // 在每个大写字母前添加下划线，并保持原字母大小写不变
        $string = preg_replace('/(?<=[a-z0-9])([A-Z])/', '_${1}', $string);
        // 根据$toUpper参数决定是否将字符串转为大写，是则转为大写，否则转为小写
        return $toUpper ? strtoupper($string) : strtolower($string);
    }
}