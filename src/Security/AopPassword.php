<?php

namespace Landao\WebmanCore\Security;

use Illuminate\Support\Str;


/**
 * 密码操作
 * Class AopPassword
 * @package JoyceZ\WebmanCore\Security
 */
class AopPassword
{
    protected $salt = '';

    /**
     * 设置密码加密盐
     * @param string $salt 加密盐
     * @return $this
     */
    public function withSalt(string $salt = '')
    {
        $this->salt = trim($salt) == '' ? config('plugin.landao.webman-core.app.passport.password_salt') : $salt;
        return $this;
    }

    /**
     * 密码加密
     * @param string $password 用户密码
     * @param string $salt 用户注册生成的6位数随机密码
     * @return string
     */
    public function encrypt(string $password, string $salt = '')
    {
        //$salt 为随机字符串，请在调用的时候直接传递
        $encryptSalt = trim($salt) == '' ? Str::random(6) : $salt;
        return md5(md5($password . $encryptSalt) . $this->salt);
    }

    /**
     * 验证密码是否正确
     * @param string $dbPassword
     * @param string $password 被校验密码
     * @param string $salt 用户注册时生成的6位随机密码
     * @return bool
     */
    public function check(string $dbPassword, string $password, string $salt)
    {
        return $this->encrypt($password, $salt) == $dbPassword ? true : false;
    }
}