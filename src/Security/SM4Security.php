<?php

declare(strict_types=1);

namespace Landao\WebmanCore\Security;

use Landao\WebmanCore\Exceptions\DecryptErrorException;
use InvalidArgumentException;
use Exception;
/**
 * @desc 国密 SM4 加解密
 */
class SM4Security
{
    // 加密算法
    private const ALGORITHM = 'SM4-CBC';

    /**
     * 32位秘钥key
     * @var string
     */
    protected string $secretKey = '';

    /**
     * 16位密码iv长度
     * @var string
     */
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

    /**
     * @desc 加密
     * @param string $encryptText
     * @return string
     */
    public function encrypt(string $encryptText): string
    {
        return openssl_encrypt($encryptText, self::ALGORITHM, hex2bin($this->secretKey), OPENSSL_CIPHER_RC2_40, $this->secretIv);
    }

    /**
     * @desc 解密
     * @param string $decryptText
     * @return string
     */
    public function decrypt(string $decryptText): string
    {
        return openssl_decrypt($decryptText, self::ALGORITHM, hex2bin($this->secretKey), OPENSSL_CIPHER_RC2_40, $this->secretIv);
    }

}