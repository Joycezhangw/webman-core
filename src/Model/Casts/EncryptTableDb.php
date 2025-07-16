<?php

namespace Landao\WebmanCore\Model\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Landao\WebmanCore\Exceptions\DecryptErrorException;
use Landao\WebmanCore\Security\AesSecurity;

/**
 * 对需要加密的字段进行加解密处理
 * 用于需要对称加解密，且需要条件查询字段
 * Class EncryptTableDb
 * @author Joycezhangw  https://github.com/Joycezhangw
 * @package LanDao\WebmanCore\Model\Casts
 */
class EncryptTableDb implements CastsAttributes
{
    /**
     * 将不为空的值进行加密
     * @param $model
     * @param string $key
     * @param $value
     * @param array $attributes
     * @return mixed|string|null
     * @throws \Exception
     */
    public function set($model, string $key, $value, array $attributes)
    {
        try {
            //值为空不进行加密
            if (!is_null($value) && $value !== '') {
                return (new AesSecurity())->withSecretKey()->encrypt($value);
            }
            return $value;
        } catch (DecryptErrorException $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 将取出的值进行解密
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return false|string|null
     * @throws \Exception
     */
    public function get($model, string $key, $value, array $attributes)
    {
        try {
            if (!is_null($value) && $value !== '') {
                return (new AesSecurity())->withSecretKey()->decrypt($value);
            }
            return $value;
        } catch (DecryptErrorException $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}