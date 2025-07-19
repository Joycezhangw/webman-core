<?php

namespace Landao\WebmanCore\Model\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * 时间戳转换处理器
 *
 * 将数据库存储的时间戳转换为指定格式的日期字符串，支持自定义日期格式
 * 当时间戳为0时返回'-'，其他情况按指定格式格式化
 *
 * @author Your Name
 * @version 1.0
 * @since 2025-07
 *
 * @example
 * // 默认格式 (Y-m-d H:i:s)
 * protected $casts = ['expire_at' => TimestampCast::class];
 *
 * // 自定义格式 (Y-m-d)
 * protected $casts = ['create_date' => TimestampCast::class . ':Y-m-d'];
 *
 * // 中文格式 (Y年m月d日 H:i)
 * protected $casts = ['update_time' => TimestampCast::class . ':Y年m月d日 H:i'];
 */
class TimestampCast implements CastsAttributes
{
    /**
     * 日期格式化格式
     * @var string
     */
    private string $format;

    /**
     * 构造函数
     * @param string $format 日期格式，默认'Y-m-d H:i:s'
     */
    public function __construct(string $format = 'Y-m-d H:i:s')
    {
        $this->format = $format;
    }

    public function get($model, $key, $value, $attributes)
    {
        return $value == 0 ? '-' : date($this->format, $value);
    }

    public function set($model, $key, $value, $attributes)
    {
        return is_numeric($value) ? $value : strtotime($value);
    }
}