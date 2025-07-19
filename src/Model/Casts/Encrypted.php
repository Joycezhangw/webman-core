<?php

namespace Landao\WebmanCore\Model\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Landao\WebmanCore\Exceptions\DecryptErrorException;
use Landao\WebmanCore\Security\Crypt;

/**
 * 模型字段加密处理
 */
class Encrypted implements CastsAttributes
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
                return Crypt::encrypt($value);
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
                return Crypt::decrypt($value);
            }
            return $value;
        } catch (DecryptErrorException $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}