<?php

namespace Landao\WebmanCore\Security;

use Landao\WebmanCore\Exceptions\DecryptErrorException;

class Crypt
{
    /**
     * @var string
     */
    private static $key;

    /**
     * @var string
     */
    private static $iv;

    /**
     * @var string
     */
    private static $cipher;

    /**
     * 初始化加密配置
     */
    private static function init(): void
    {
        if (self::$key !== null) {
            return;
        }

        $config = config('plugin.landao.webman-core.app.crypt');
        if (empty($config['key'])) {
            throw new DecryptErrorException('Encryption key not configured. Please set app.crypt.key in config.');
        }

        // 处理base64编码的密钥
        self::$key = str_starts_with($config['key'], 'base64:') ? base64_decode(substr($config['key'], 7)) : $config['key'];
        self::$iv = base64_decode($config['iv'] ?? '');
        self::$cipher = $config['cipher'] ?? 'aes-256-cbc';

        // 验证密钥和IV长度
        $keyLength = openssl_cipher_iv_length(self::$cipher);
        if (strlen(self::$key) !== 32) {
            throw new DecryptErrorException(sprintf('Invalid key length for %s. Expected 32 bytes, got %d.', self::$cipher, strlen(self::$key)));
        }
        if (strlen(self::$iv) !== $keyLength) {
            throw new DecryptErrorException(sprintf('Invalid IV length for %s. Expected %d bytes, got %d.', self::$cipher, $keyLength, strlen(self::$iv)));
        }
    }

    /**
     * 加密数据
     * @param string $value
     * @return string
     * @throws DecryptErrorException
     */
    public static function encrypt(string $value): string
    {
        self::init();

        $encrypted = openssl_encrypt(
            $value,
            self::$cipher,
            self::$key,
            OPENSSL_RAW_DATA,
            self::$iv
        );

        if ($encrypted === false) {
            throw new DecryptErrorException('Encryption failed: ' . openssl_error_string());
        }

        // 添加HMAC校验
        $hmac = hash_hmac('sha256', $encrypted, self::$key, true);
        return base64_encode($hmac . $encrypted);
    }

    /**
     * 解密数据
     * @param string $value
     * @return string
     * @throws DecryptErrorException
     */
    public static function decrypt(string $value): string
    {
        self::init();

        $decoded = base64_decode($value);
        if ($decoded === false) {
            throw new DecryptErrorException('Invalid base64 encoded data');
        }

        // 分离HMAC和密文
        $hmacSize = 32; // SHA256输出32字节
        $hmac = substr($decoded, 0, $hmacSize);
        $encrypted = substr($decoded, $hmacSize);

        // 验证HMAC
        $calculatedHmac = hash_hmac('sha256', $encrypted, self::$key, true);
        if (!hash_equals($hmac, $calculatedHmac)) {
            throw new DecryptErrorException('Data integrity check failed');
        }

        $decrypted = openssl_decrypt(
            $encrypted,
            self::$cipher,
            self::$key,
            OPENSSL_RAW_DATA,
            self::$iv
        );

        if ($decrypted === false) {
            throw new DecryptErrorException('Decryption failed: ' . openssl_error_string());
        }

        return $decrypted;
    }
}