<?php

namespace Landao\WebmanCore\Security;
/**
 * 前后端对称加解密
 */
class AopCryptoJs
{
    protected $screctKey = '';

    private $supportedCiphers = [
        'aes-128-cbc' => ['size' => 16, 'aead' => false],
        'aes-256-cbc' => ['size' => 32, 'aead' => false],
        'aes-128-gcm' => ['size' => 16, 'aead' => true],
        'aes-256-gcm' => ['size' => 32, 'aead' => true],
    ];


    public function __construct(string $screctKey = '')
    {
        //TODO：目前暂时不使用 AopCrypt。此处要使用用token作为密钥对数据进行加解密。要跟前端保持一致
        //TODO:使用 AopCrypt，就要用  base64_encode(random_bytes(16)) 生成 24 位 16进制 密钥
        $this->screctKey = md5($screctKey);
    }

    /**
     * 指定 AES 创建一个加密密钥
     * @param string $cipher $this->supportedCiphers[key]
     * @return string
     * @throws \Exception
     */
    public function generateKey(string $cipher)
    {
        return random_bytes($this->supportedCiphers[strtolower($cipher)]['size'] ?? 32);
    }

    /**
     * 对返回的 字段 进行加密
     * @param string $value
     * @return false|string
     */
    public function encrypt(string $value)
    {
        $iv = str_repeat("0", 16);
        return openssl_encrypt($value, 'aes-256-cbc', $this->screctKey, 0, $iv);
    }

    /**
     * 对返回的 字段 进行加密
     * @param string $value
     * @return string
     */
    public function decrypt(string $value): string
    {
        $replace = ['+', '/'];
        $search = ['-', '_'];
        $iv = str_repeat("0", 16);
        $str = openssl_decrypt(str_replace($search, $replace, $value), 'aes-256-cbc', $this->screctKey, 0, $iv);
        return (string)$this->specialFilter(trim($str));
    }

    /**
     * 根据ascii码过滤控制字符
     * @param $string
     * @return string
     */
    private function specialFilter($string): string
    {
        if (!$string) return '';
        $new_string = '';
        for ($i = 0; isset($string[$i]); $i++) {
            $asc_code = ord($string[$i]);    //得到其asc码
            //以下代码旨在过滤非法字符
            if ($asc_code == 9 || $asc_code == 10 || $asc_code == 13) {
                $new_string .= ' ';
            } else if ($asc_code > 31 && $asc_code != 127) {
                $new_string .= $string[$i];
            }
        }
        return trim($new_string);
    }
}