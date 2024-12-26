<?php

namespace Landao\WebmanCore\Helpers;

class StrHelper
{
    /**
     *  重写ip2long，将ip地址转换为整型
     * @param string $ip
     * @return string
     */
    static function ip2long($ip = '127.0.0.1')
    {
        //ip2long可转换为整型，但会出现携带符号问题。需格式化为无符号的整型，利用sprintf函数格式化字符串。
        //然后用long2ip将整型转回IP字符串
        //MySQL函数转换(无符号整型，UNSIGNED)
        //INET_ATON('218.5.49.94');将IP转为整型 INET_NTOA(3657773406);将整型转为IP
        return sprintf('%u', ip2long($ip));
    }


    /**
     * 字符串匹配替换
     *
     * @param string $search 查找的字符串
     * @param string $replace 替换的字符串
     * @param string $subject 字符串
     * @param null $count
     * @return mixed
     */
    public static function replace(string $search, string $replace, string $subject, &$count = null)
    {
        return str_replace($search, $replace, $subject, $count);
    }

    /**
     * 指定替换最后出现的字符串
     *
     * 例如:<a href="/manage/system/modulelist.html">系统</a><span lay-separator="">&gt;</span><a href="/manage/system/modulelist.html">模块列表</a><span lay-separator="">&gt;</span><a href="/manage/system/editmodule.html">修改模块</a><span lay-separator="">&gt;</span>
     *
     * StrHelper::lreplace('<span lay-separator="">&gt;</span>','',$str)
     *
     * @param $search
     * @param $replace
     * @param $subject
     * @return string
     */
    public static function lreplace($search, $replace, $subject): string
    {
        $pos = strrpos($subject, $search);
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }
        return trim($subject);
    }


    /**
     * 将一个字符串部分字符用*替代隐藏
     * @param string $string 待转换的字符串
     * @param int $begin 起始位置，从0开始计数，当$type=4时，表示左侧保留长度
     * @param int $len 需要转换成*的字符个数，当$type=4时，表示右侧保留长度
     * @param int $type 转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
     * @param string $glue 分割符
     * @return bool|string
     */
    public static function hideStr(string $string, int $begin = 0, int $len = 4, int $type = 0, string $glue = "@")
    {
        if (empty($string)) {
            return false;
        }

        $array = [];
        if ($type == 0 || $type == 1 || $type == 4) {
            $strLen = $length = mb_strlen($string);

            while ($strLen) {
                $array[] = mb_substr($string, 0, 1, "utf8");
                $string = mb_substr($string, 1, $strLen, "utf8");
                $strLen = mb_strlen($string);
            }
        }

        switch ($type) {
            case 0 :
                for ($i = $begin; $i < ($begin + $len); $i++) {
                    isset($array[$i]) && $array[$i] = "*";
                }

                $string = implode("", $array);
                break;
            case 1 :
                $array = array_reverse($array);
                for ($i = $begin; $i < ($begin + $len); $i++) {
                    isset($array[$i]) && $array[$i] = "*";
                }

                $string = implode("", array_reverse($array));
                break;
            case 2 :
                $array = explode($glue, $string);
                $array[0] = self::hideStr($array[0], $begin, $len, 1);
                $string = implode($glue, $array);
                break;
            case 3 :
                $array = explode($glue, $string);
                $array[1] = self::hideStr($array[1], $begin, $len, 0);
                $string = implode($glue, $array);
                break;
            case 4 :
                $left = $begin;
                $right = $len;
                $tem = array();
                for ($i = 0; $i < ($length - $right); $i++) {
                    if (isset($array[$i])) {
                        $tem[] = $i >= $left ? "*" : $array[$i];
                    }
                }

                $array = array_chunk(array_reverse($array), $right);
                $array = array_reverse($array[0]);
                for ($i = 0; $i < $right; $i++) {
                    $tem[] = $array[$i];
                }
                $string = implode("", $tem);
                break;
        }

        return $string;
    }

    /**
     * 判断字符串是否是json格式
     * @param string $str 字符串
     * @param bool $assoc 是否返回关联数组，默认返回对象
     * @return bool|string
     */
    public static function isJson(string $str = '', $assoc = false)
    {
        $data = json_decode($str, $assoc);
        if (($data && is_object($data)) || is_array($data)) {
            return true;
        }
        return false;
    }

    /**
     * 字符串"true"/"false"转成boolean布尔型
     * @param $val
     * @param bool $resultNull
     * @return bool|mixed|null
     */
    public static function isTrue($val, $resultNull = false)
    {
        $boolVal = (is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool)$val);
        return ($boolVal === null && !$resultNull ? false : $boolVal);
    }

}