<?php

namespace Landao\WebmanCore\Security;

/**
 * 对加密字段进行拆分加密搜索
 * Class AopEncryptDataIndex
 * @author Joycezhangw  https://github.com/Joycezhangw
 * @package LanDao\WebmanCore\Security
 */
class AopEncryptDataIndex
{
    private $SEPARATOR_CHAR_MAP;
    private $BASE64_ARRAY = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';

    private const PHONE_SEPARATOR_CHAR = '$';//手机号
    private const ID_CARD_SEPARATOR_CHAR = '#';//身份号
    private const NORMAL_SEPARATOR_CHAR = '~';//其他字符

    protected $aopSecurity = null;

    public function __construct()
    {
        $this->SEPARATOR_CHAR_MAP['normal'] = self::NORMAL_SEPARATOR_CHAR;
        $this->SEPARATOR_CHAR_MAP['nick'] = self::NORMAL_SEPARATOR_CHAR;
        $this->SEPARATOR_CHAR_MAP['phone'] = self::PHONE_SEPARATOR_CHAR;
        $this->SEPARATOR_CHAR_MAP['idCard'] = self::ID_CARD_SEPARATOR_CHAR;
        //数据加密类
        $this->aopSecurity = (new AopSecurity())->withScrectKey(config('plugin.landao.webman-core.app.security.security_key'));
    }

    /**
     * 加密检索逻辑
     * @param string $data 加密原文
     * @param string $type 加密原文类型
     * @param int $compressLen 压缩长度
     * @param int $slideSize 分词个数，默认半角
     * @return bool|string
     */
    function encrypt(string $data, $type, $compressLen = 3, $slideSize = 4)
    {
        if (!is_string($data)) {
            return false;
        }
        $separator = $this->SEPARATOR_CHAR_MAP[$type];
        if ('phone' == $type) {
            return $this->encryptPhoneIndex($data, $separator);
        } elseif ('idCard' == $type) {
            return $this->encryptIdCardIndex($data, $separator);
        } else {
//            $compressLen = $this->getArrayValue([], 'encrypt_index_compress_len', 3);
//            //先对字符进行固定长度的分组，将一个字段拆分为多个，比如说根据4位英文字符（半角），2个中文字符（全角）为一个检索条件，举个例子：
//            $slideSize = $this->getArrayValue([], 'encrypt_slide_size', 4);

            return $this->encryptNormalIndex($data, $compressLen, $slideSize, $separator);
        }
    }


    /**
     * @param $data
     * @param $type
     * @param $secretContext
     * @param int $compressLen
     * @param int $slideSize
     * @return string
     * @throws \Exception
     */
    public function search($data, $type, $compressLen = 3, $slideSize = 4)
    {
        $separator = $this->SEPARATOR_CHAR_MAP[$type];
        if ('phone' == $type) {
            $phoneLen = strlen($data);
            if ($phoneLen != 11 & $phoneLen != 4) {
                throw new \Exception("phoneNumber error");
            }
            if ($phoneLen == 4) {
                return $separator . $this->hmacMD5EncryptToBase64($data) . $separator;
            } else {
                return $separator . $this->aopSecurity->encrypt($data) . $separator;
            }
        } elseif ('idCard' == $type) {
            $idCardLen = strlen($data);
            if ($idCardLen != 18 & $idCardLen != 4) {
                throw new \Exception("idCard error");
            }
            if ($idCardLen == 4) {
                return $separator . $this->hmacMD5EncryptToBase64($data) . $separator;
            } else {
                return $separator . $this->aopSecurity->encrypt($data) . $separator;
            }
        } else {
            $slideList = $this->getSlideWindows($data, $slideSize);
            $builder = '';
            foreach ($slideList as $slide) {
                $builder .= $this->hmacMD5EncryptToBase64($slide, $compressLen);
            }
            return $builder;
        }
    }


    /**
     * 加密逻辑，手机号格式。精准查询。
     * @param string $data
     * @param $separator
     * @return string
     */
    public function encryptPhone(string $data, $separator)
    {
        $len = strlen($data);
        if ($len < 11) {
            return $data;
        }
        $prefixNumber = substr($data, 0, $len - 8);
        $last8Number = substr($data, $len - 8, $len);
        return $separator . $prefixNumber . $separator . $this->aopSecurity->encrypt($last8Number) . $separator;
    }

    /**
     * 手机号模糊搜索加密
     * @param string $data
     * @param $separator
     * @return string
     */
    public function encryptPhoneIndex(string $data, $separator)
    {
        $len = strlen($data);
        if ($len < 11) {
            return $data;
        }
        //后八位
        $last4Number = substr($data, $len - 4, $len);
        return $separator . $this->hmacMD5EncryptToBase64($last4Number) . $separator . $this->aopSecurity->encrypt($data) . $separator;
    }

    /**
     * 二代身份证号模糊搜索
     * @param string $data
     * @param $separator
     * @return string
     */
    public function encryptIdCardIndex(string $data, $separator)
    {
        $len = strlen($data);
        if ($len < 18) {
            return $data;
        }
        //后八位
        $last4Number = substr($data, $len - 4, $len);
        return $separator . $this->hmacMD5EncryptToBase64($last4Number) . $separator . $this->aopSecurity->encrypt($data) . $separator;
    }

    public function encryptNormalIndex($data, $compressLen, $slideSize, $separator)
    {
        //分词，https://ningyu1.github.io/20201230/encrypted-data-fuzzy-query.html#%E5%B8%B8%E8%A7%84%E4%BA%8C
        $slideList = $this->getSlideWindows($data, $slideSize);
        $builder = "";
        foreach ($slideList as $slide) {
            $builder .= $this->hmacMD5EncryptToBase64($slide, $compressLen);
        }

        return $separator . $this->aopSecurity->encrypt($data) . $separator . $builder . $separator;
    }


    /**
     * 判断是否密文数据
     * @param $dataArray
     * @return bool
     */
    public function checkEncryptData($dataArray)
    {
        if (count($dataArray) == 2) {
            return $this->isBase64Str($dataArray[0]);
        } else {
            return $this->isBase64Str($dataArray[0]) && $this->isBase64Str($dataArray[1]);
        }
    }

    /**
     * 判断是否是加密数据
     * @param $array
     * @param $type
     * @return bool
     */
    public function isEncryptDataArray($array, $type)
    {
        foreach ($array as $value) {
            if (!$this->isEncryptData($value, $type)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 判断是否是已加密的数据，数据必须是同一个类型
     * @param $array
     * @param $type
     * @return bool
     */
    function isPartEncryptData($array, $type)
    {
        $result = false;
        foreach ($array as $value) {
            if ($this->isEncryptData($value, $type)) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * 判断是否加密数据
     * @param $data
     * @param $type
     * @return bool
     */
    public function isEncryptData($data, $type)
    {
        if (!is_string($data) || strlen($data) < 4) {
            return false;
        }

        $separator = $this->SEPARATOR_CHAR_MAP[$type];
        $strLen = strlen($data);
        if ($data[0] != $separator || $data[$strLen - 1] != $separator) {
            return false;
        }

        $dataArray = explode($separator, $this->trimBySep($data, $separator));
        $arrayLength = count($dataArray);

        if ($separator == self::PHONE_SEPARATOR_CHAR) {
            if ($arrayLength != 3) {
                return false;
            }
            if ($data[$strLen - 2] == $separator) {
                return $this->checkEncryptData($dataArray);
            } else {
                $version = $dataArray[$arrayLength - 1];
                if (is_numeric($version)) {
                    $base64Val = $dataArray[$arrayLength - 2];

                    return $this->isBase64Str($base64Val);
                }
            }
        } else {
            if ($data[strlen($data) - 2] == $separator && $arrayLength == 3) {
                return $this->checkEncryptData($dataArray);
            } elseif ($arrayLength == 2) {
                return $this->checkEncryptData($dataArray);
            } else {
                return false;
            }
        }
    }

    /**
     * 生成滑动窗口
     * @param $input
     * @param int $slideSize
     * @return array
     */
    public function getSlideWindows($input, $slideSize = 4)
    {
        $endIndex = 0;
        $startIndex = 0;
        $currentWindowSize = 0;
        $currentWindow = null;
        $dataLength = $this->utf8_strlen($input);
        $windows = array();
        while ($endIndex < $dataLength || $currentWindowSize > $slideSize) {
            $startsWithLetterOrDigit = false;
            if (!empty($currentWindow)) {
                $startsWithLetterOrDigit = $this->isLetterOrDigit($this->utf8_str_at($currentWindow, 0));
            }
            if ($endIndex == $dataLength && $startsWithLetterOrDigit == false) {
                break;
            }
            if ($currentWindowSize == $slideSize &&
                $startsWithLetterOrDigit == false &&
                $this->isLetterOrDigit($this->utf8_str_at($input, $endIndex))) {
                $endIndex++;
                $currentWindow = $this->utf8_substr($input, $startIndex, $endIndex);
                $currentWindowSize = 5;
            } else {
                if ($endIndex != 0) {
                    if ($startsWithLetterOrDigit) {
                        $currentWindowSize -= 1;
                    } else {
                        $currentWindowSize -= 2;
                    }
                    $startIndex++;
                }

                while ($currentWindowSize < $slideSize && $endIndex < $dataLength) {
                    $currentChar = $this->utf8_str_at($input, $endIndex);
                    if ($this->isLetterOrDigit($currentChar)) {
                        $currentWindowSize += 1;
                    } else {
                        $currentWindowSize += 2;
                    }
                    $endIndex++;
                }
                $currentWindow = $this->utf8_substr($input, $startIndex, $endIndex);
            }
            array_push($windows, $currentWindow);
        }
        return $windows;
    }

    /**
     * 判断是否是base64格式的数据
     * @param string $str
     * @return bool
     */
    public function isBase64Str($str)
    {
        $strLen = strlen($str);
        for ($i = 0; $i < $strLen; $i++) {
            if (!$this->isBase64Char($str[$i])) {
                return false;
            }
        }
        return true;
    }


    /**
     * 判断是否是base64格式的字符
     * @param string $char
     * @return bool
     */
    public function isBase64Char($char)
    {
        return strpos($this->BASE64_ARRAY, $char) !== false;
    }


    /**
     * 使用sep字符进行trim
     * @param $str
     * @param $sep
     * @return false|string
     */
    public function trimBySep($str, $sep)
    {
        $start = 0;
        $end = strlen($str);
        for ($i = 0; $i < $end; $i++) {
            if ($str[$i] == $sep) {
                $start = $i + 1;
            } else {
                break;
            }
        }
        for ($i = $end - 1; $i >= 0; $i--) {
            if ($str[$i] == $sep) {
                $end = $i - 1;
            } else {
                break;
            }
        }

        return substr($str, $start, $end);
    }


    /**
     * @param string $encryptText 被签名的字符串
     * @param int $compressLen 压缩长度
     * @return string
     */
    public function hmacMD5EncryptToBase64($encryptText, $compressLen = 0)
    {
        $encryptResult = $this->aopSecurity->hmac_md5($encryptText);
        if ($compressLen != 0) {
            $encryptResult = $this->compress($encryptResult, $compressLen);
        }

        return base64_encode($this->toStr($encryptResult));
    }


    public function compress($input, $toLength)
    {
        if ($toLength < 0) {
            return null;
        }
        $output = array();
        for ($i = 0; $i < $toLength; $i++) {
            $output[$i] = chr(0);
        }
        $input = $this->getBytes($input);
        $inputLength = count($input);
        for ($i = 0; $i < $inputLength; $i++) {
            $index_output = $i % $toLength;
            $output[$index_output] = intval($output[$index_output]) ^ $input[$i];
        }
        return $output;
    }


    public function getBytes($string)
    {
        $bytes = [];
        for ($i = 0; $i < strlen($string); $i++) {
            $bytes[] = ord($string[$i]);
        }

        return $bytes;
    }

    public function toStr($bytes)
    {
        if (!is_array($bytes)) {
            return $bytes;
        }
        $str = '';
        foreach ($bytes as $ch) {
            $str .= chr($ch);
        }

        return $str;
    }

    function getArrayValue($array, $key, $default)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        return $default;
    }

    public function isLetterOrDigit($ch)
    {
        $code = ord($ch);
        if (0 <= $code && $code <= 127) {
            return true;
        }
        return false;
    }

    public function utf8_strlen($string = null)
    {
        // 将字符串分解为单元
        preg_match_all("/./us", $string, $match);
        // 返回单元个数
        return count($match[0]);
    }

    public function utf8_substr($string, $start, $end)
    {
        // 将字符串分解为单元
        preg_match_all("/./us", $string, $match);
        // 返回单元个数
        $result = "";
        for ($i = $start; $i < $end; $i++) {
            $result .= $match[0][$i];
        }
        return $result;
    }

    public function utf8_str_at($string, $index)
    {
        // 将字符串分解为单元
        preg_match_all("/./us", $string, $match);
        // 返回单元个数
        return $match[0][$index];
    }
}