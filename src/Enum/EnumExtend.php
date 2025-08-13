<?php

namespace Landao\WebmanCore\Enum;

use Landao\WebmanCore\Exceptions\EnumException;
use ReflectionEnumUnitCase;

/**
 * 枚举注解
 * trait EnumExtend
 * @author Joycezhangw  https://github.com/Joycezhangw
 * @package Landao\WebmanCore\Enum
 */
trait EnumExtend
{
    /**
     * 获取全部枚举
     * 以 ['key'=>'value',...] 形式返回枚举内容
     * @return array
     */
    public static function getMap(): array
    {
        $enums = [];
        foreach (self::cases() as $enum) {
            $enums[$enum->name] = $enum->value;
        }
        return $enums;
    }

    /**
     * 返回枚举所有keys
     * @return array
     */
    public static function getKeys(): array
    {
        return array_map(fn($enum): string => $enum->name, self::cases());
    }

    /**
     * 返回枚举所有值
     * @return array
     */
    public static function getValues(): array
    {
        return array_map(fn($enum): mixed => $enum->value, self::cases());
    }

    /**
     * 判断key是否包含在枚举中
     * @param string $value
     * @return bool
     */
    public static function include(string $value): bool
    {
        return in_array($value, self::getKeys());
    }

    /**
     * 判断多个key是否包含在枚举中
     * @param array $names
     * @return bool
     */
    public static function includeAll(array $names): bool
    {
        $enums = self::getKeys();
        foreach ($names as $name) {
            if (!in_array($name, $enums)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 判断值是否包含在枚举value中
     * @param mixed $value
     * @return bool
     */
    public static function hasValue(mixed $value): bool
    {
        return in_array($value, self::getValues());
    }


    /**
     * 判断多个值是否都包含在枚举value中
     * @param array $values
     * @return bool
     */
    public static function hasAllValues(array $values): bool
    {
        $enumValues = self::getValues();
        foreach ($values as $value) {
            if (!in_array($value, $enumValues)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 判断持久层返回的是否相同
     * @param string $value
     * @return bool
     */
    public function equal(string $value): bool
    {
        return self::tryFrom($value) === $this;
    }

    /**
     * 比较两个枚举实例是否相同
     * @param self $enum
     * @return bool
     */
    public function equals(self $enum): bool
    {
        return $this === $enum;
    }

    /**
     * 比较枚举值是否与给定值相同
     * @param mixed $value
     * @return bool
     */
    public function valueEquals(mixed $value): bool
    {
        return $this->value === $value;
    }

    /**
     * 获取枚举注解说明
     * @return string
     * @throws EnumException
     */
    public function getEnumLabel(): string
    {
        $ref = new ReflectionEnumUnitCase(self::class, $this->name);
        $attributes = $ref->getAttributes();
        if (!$attributes) throw new EnumException('未配置 Enum 注解说明');
        foreach ($attributes as $attribute) {
            $args = $attribute->getArguments();
            return $args[0];
        }
        return '';
    }

    /**
     * 获取枚举值和注解说明数组
     * @return array
     * @throws EnumException
     */
    public static function getEnumMapLabel(): array
    {
        $enums = [];
        foreach (self::cases() as $enum) {
            $enums[$enum->value] = $enum->getEnumLabel();
        }
        return $enums;
    }
}