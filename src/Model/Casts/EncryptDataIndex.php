<?php

namespace Landao\WebmanCore\Model\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Database\Eloquent\Model;
use LanDao\WebmanCore\Security\AopEncryptDataIndex;

/**
 * 可模糊搜索的加密字段进行拆字加密，使其支持模糊搜索
 * Class EncryptDataIndex
 * @author Joycezhangw  https://github.com/Joycezhangw
 * @package LanDao\WebmanCore\Model\Casts
*/
class EncryptDataIndex implements CastsInboundAttributes
{
    /**
     * 加密类型，分别为：nick,phone,idCard
     * @var mixed|string
     */
    protected $indexType;

    public function __construct($indexType = '')
    {
        $this->indexType = $indexType;
    }

    /**
     * 转换成将要存储的值
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public function set($model, string $key, $value, array $attributes): string
    {
        if (trim($value) == '') {
            return "";
        }
        $encryptDataIndex = new AopEncryptDataIndex();
        return trim($this->indexType) != '' ? $encryptDataIndex->encrypt($value, $this->indexType) : $value;
    }
}