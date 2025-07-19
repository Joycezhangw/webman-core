<?php

namespace Landao\WebmanCore\ParseAnnotation;

use ReflectionClass;
use ReflectionMethod;
use Landao\WebmanCore\Annotation\Desensitize;

class DesensitizeParse
{
    
    public static function getDesensitizeRules($controller, ?string $action = null): array
    {
        $rules = [];
        $reflection = new ReflectionClass($controller);

        // 类级别注解
        foreach ($reflection->getAttributes(Desensitize::class) as $attribute) {
            $desensitize = $attribute->newInstance();
            $rules[] = [
                'field' => $desensitize->field,
                'rule' => $desensitize->rule
            ];
        }

        // 方法级别注解（优先级更高）
        if ($action && $reflection->hasMethod($action)) {
            $method = new ReflectionMethod($controller, $action);
            foreach ($method->getAttributes(Desensitize::class) as $attribute) {
                $desensitize = $attribute->newInstance();
                $rules[] = [
                    'field' => $desensitize->field,
                    'rule' => $desensitize->rule
                ];
            }
        }

        return $rules;
    }
}