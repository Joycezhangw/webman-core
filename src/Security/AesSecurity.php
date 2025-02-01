<?php
declare(strict_types=1);

namespace Landao\WebmanCore\Security;

use Landao\WebmanCore\Exceptions\DecryptErrorException;
use InvalidArgumentException;
use Exception;

/**
 * 加密工具
 * Class AopCrypt
 * @author alipay  https://github.com/alipay/alipay-sdk-php-all
 * @package LanDao\WebmanCore\Security
 */
class AesSecurity
{
    // 加密算法
    private const ALGORITHM = 'aes-128-cbc';

    /**
     * 密钥
     * @var string
     */
    protected string $secretKey = '';

    protected string $secretIv = '';


    /**
     * 设置加密密钥和初始化向量
     *
     * 该方法用于设置加密密钥（secretKey）和初始化向量（iv）如果未提供这些参数或者为空，
     * 则使用配置文件中的默认值进行设置
     *
     * @param string $secretKey 加密密钥，默认为空字符串
     * @param string $iv 初始化向量，默认为空字符串
     * @return $this 返回当前实例，支持链式调用
     * @throws InvalidArgumentException 如果参数类型不正确，则抛出异常
     * @throws DecryptErrorException 如果无法读取配置信息，则抛出异常
     */
    public function withSecretKey(string $secretKey = '', string $iv = ''): self
    {
        // 验证输入参数是否为字符串
        if (!is_string($secretKey)) {
            throw new InvalidArgumentException('Secret key must be a string.');
        }
        if (!is_string($iv)) {
            throw new InvalidArgumentException('IV must be a string.');
        }

        // 获取配置文件中的默认值
        try {
            $defaultSecretKey = config('plugin.landao.webman-core.app.security.secret_key');
            $defaultIv = config('plugin.landao.webman-core.app.security.secret_iv');
        } catch (Exception $e) {
            throw new DecryptErrorException('Failed to read configuration: ' . $e->getMessage());
        }

        // 设置 secretKey 和 secretIv
        $this->secretKey = trim($secretKey) === '' ? $defaultSecretKey : $secretKey;
        $this->secretIv = trim($iv) === '' ? $defaultIv : $iv;

        return $this;
    }


    public function hmac_md5($input)
    {
        $key = base64_decode($this->secretKey);
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
        $secretKey = base64_decode($this->secretKey);
        $str = trim($str);
        $str = $this->addPKCS7Padding($str);

        //设置全0的IV
        $iv = $this->secretIv;//str_repeat("\0", 16);
        $encrypt_str = openssl_encrypt($str, self::ALGORITHM, $secretKey, OPENSSL_NO_PADDING, $iv);
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
        $secretKey = base64_decode($this->secretKey);

        //设置全0的IV
        $iv = $this->secretIv;//str_repeat("\0", 16);
        $decrypted = openssl_decrypt($str, self::ALGORITHM, $secretKey, OPENSSL_NO_PADDING, $iv);
        $decrypted = $this->stripPKSC7Padding($decrypted);
        if (!$decrypted) {
            throw new DecryptErrorException(sprintf('解密失败，请检查密钥 %s 密文 %s 是否正确?', $secretKey, $str));
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
        return substr($source, 0, -$num);
    }
}