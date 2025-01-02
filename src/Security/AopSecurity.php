<?php

namespace Landao\WebmanCore\Security;

use Landao\WebmanCore\Exceptions\DecryptErrorException;

/**
 * 加密工具
 * Class AopCrypt
 * @author alipay  https://github.com/alipay/alipay-sdk-php-all
 * @package LanDao\WebmanCore\Security
 */
class AopSecurity
{
    /**
     * 密钥
     * @var string
     */
    protected $screctKey = '';

    protected $scretIv = '';


    /**
     * 设置密码加密盐
     * @param string $screctKey 加密盐
     * @return $this
     */
    public function withScrectKey(string $screctKey = '', $iv='')
    {
        $this->screctKey = trim($screctKey) == '' ? config('plugin.landao.webman-core.app.security.security_key') : $screctKey;
        $this->scretIv = trim($iv) == '' ? config('plugin.landao.webman-core.app.security.security_iv') : $iv;
        return $this;
    }

    public function hmac_md5($input)
    {
        $key = base64_decode($this->screctKey);
        return hash_hmac('md5', $input, $key, true);
    }

    /**
     * 加密方法
     * @param string $str
     * @return string
     */
    public function encrypt($str)
    {
        //AES, 128 模式加密数据 CBC
        $screct_key = base64_decode($this->screctKey);
        $str = trim($str);
        $str = $this->addPKCS7Padding($str);

        //设置全0的IV
        $iv = $this->scretIv;//str_repeat("\0", 16);
        $encrypt_str = openssl_encrypt($str, 'aes-128-cbc', $screct_key, OPENSSL_NO_PADDING, $iv);
        return base64_encode($encrypt_str);
    }


    /**
     * 解密方法
     * @param $str
     * @return false|string
     * @throws DecryptErrorException
     */
    public function decrypt($str)
    {
        //AES, 128 模式加密数据 CBC
        $str = base64_decode($str);
        $screct_key = base64_decode($this->screctKey);

        //设置全0的IV
        $iv = $this->scretIv;//str_repeat("\0", 16);
        $decrypted = openssl_decrypt($str, 'aes-128-cbc', $screct_key, OPENSSL_NO_PADDING, $iv);
        $decrypted = $this->stripPKSC7Padding($decrypted);
        if (!$decrypted) {
            throw new DecryptErrorException(sprintf('解密失败，请检查密钥 %s 密文 %s 是否正确?', $screct_key, $str));
        }
        return $decrypted;
    }

    /**
     * 填充算法
     * @param string $source
     * @return string
     */
    private function addPKCS7Padding($source)
    {
        $source = trim($source);
        $block = 16;

        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }


    /**
     * 移去填充算法
     * @param string $source
     * @return string
     */
    private function stripPKSC7Padding($source)
    {
        $char = substr($source, -1);
        $num = ord($char);
        if ($num == 62) return $source;
        $source = substr($source, 0, -$num);
        return $source;
    }
}