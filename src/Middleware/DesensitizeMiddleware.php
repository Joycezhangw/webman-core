<?php

namespace Landao\WebmanCore\Middleware;

use Landao\WebmanCore\Helpers\DesensitizeHelper;
use Landao\WebmanCore\ParseAnnotation\DesensitizeParse;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * 注解脱敏中间件
 */
class DesensitizeMiddleware implements MiddlewareInterface
{
    /**
     * 响应数据的根路径
     * @var string
     */
    protected $rootPath = 'data';

    public function process(Request $request, callable $handler): Response
    {
        // 先执行后续中间件和控制器，获取响应
        $response = $handler($request);

        // 获取当前控制器和方法
        $controller = $request->controller;
        $action = $request->action;

        // 获取脱敏规则
        $desensitizeRules = DesensitizeParse::getDesensitizeRules($controller, $action);
        if (empty($desensitizeRules)) {
            return $response;
        }

        // 尝试解析响应内容
        $content = $response->rawBody();
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $response;
        }
        // 支持根路径配置（如API响应格式为 {code:200, data:{...}}）
        $targetData = &$data;
        if (!empty($this->rootPath)) {
            $rootFields = explode('.', $this->rootPath);
            foreach ($rootFields as $field) {
                if (!isset($targetData[$field])) {
                    // 根路径不存在，记录日志并返回原始响应
                    return $response;
                }
                $targetData = &$targetData[$field];
            }
        }
        // 应用脱敏规则
        if (is_array($targetData)) {
            // 根数据是数组，遍历每个元素处理
            foreach ($targetData as &$item) {
                if (is_array($item)) {
                    $this->processArrayItem($item, $desensitizeRules);
                }
            }
        } else {
            // 根数据是对象，直接处理
            foreach ($desensitizeRules as $rule) {
                $this->applyDesensitize($targetData, $rule['field'], $rule['rule']);
            }
        }

        $response->withBody(json_encode($data, JSON_UNESCAPED_UNICODE));
        // 更新响应内容
        return $response;
    }

    /**
     * 处理数组元素的脱敏
     */
    private function processArrayItem(array &$item, array $rules): void
    {
        foreach ($rules as $rule) {
            $this->applyDesensitize($item, $rule['field'], $rule['rule']);
        }

        // 递归处理数组中的嵌套数组
        foreach ($item as &$value) {
            if (is_array($value)) {
                if (isset($value[0]) || array_keys($value) === range(0, count($value) - 1)) {
                    // 处理索引数组
                    foreach ($value as &$nestedItem) {
                        if (is_array($nestedItem)) {
                            $this->processArrayItem($nestedItem, $rules);
                        }
                    }
                } else {
                    // 处理关联数组
                    $this->processArrayItem($value, $rules);
                }
            }
        }
    }

    /**
     * 递归应用脱敏规则
     * @param array $data
     * @param string $field 支持点分表示法，如user.mobile
     * @param mixed $rule
     */
    private function applyDesensitize(array &$data, string $field, mixed $rule): void
    {
        $fields = explode('.', $field);
        $currentField = array_shift($fields);


        if (!isset($data[$currentField])) {
            return;
        }

        if (empty($fields)) {
            // 到达最终字段，应用脱敏
            $data[$currentField] = DesensitizeHelper::desensitize($data[$currentField], $rule);
            return;
        }

        // 处理嵌套字段（同时支持数组和对象）
        $nestedData = &$data[$currentField];
        if (is_array($nestedData)) {
            // 如果是数组，遍历每个元素处理
            if (isset($nestedData[0]) || array_keys($nestedData) === range(0, count($nestedData) - 1)) {
                // 索引数组：遍历每个元素
                foreach ($nestedData as &$item) {
                    if (is_array($item)) {
                        $this->applyDesensitize($item, implode('.', $fields), $rule);
                    }
                }
            } else {
                // 关联数组（对象）：直接递归处理
                $this->applyDesensitize($nestedData, implode('.', $fields), $rule);
            }
        }
    }
}