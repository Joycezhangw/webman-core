<?php

namespace Landao\WebmanCore\Helpers;

class IDCardHelper
{
    /**
     * 根据身份证号获取性别
     * @param string $idCard 身份证号
     * @return string|null
     */
    public static function gender(string $idCard)
    {
        if (empty($idCard)) return null;
        $ext = (int)substr($idCard, 16, 1);
        return $ext % 2 === 0 ? 2 : 1;
    }

    /**
     * 根据身份证号获取出生日期
     * @param string $idCard 身份证号
     * @return string|null
     */
    public static function birthday(string $idCard)
    {
        if (empty($idCard)) return null;
        $bir = substr($idCard, 6, 8);
        $year = (int)substr($bir, 0, 4);
        $month = (int)substr($bir, 4, 2);
        $day = (int)substr($bir, 6, 2);
        return $year . "-" . $month . "-" . $day;
    }

    /**
     * 根据身份证号码计算年龄
     * @param string $idCard 身份证号
     * @return float|int|null
     */
    public static function age(string $idCard)
    {
        if (empty($idCard)) return null;
        #  获得出生年月日的时间戳
        $date = strtotime(substr($idCard, 6, 8));
        #  获得今日的时间戳
        $today = strtotime('today');
        #  得到两个日期相差的大体年数
        $diff = floor(($today - $date) / 86400 / 365);
        #  strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
        $age = strtotime(substr($idCard, 6, 8) . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;
        return $age;
    }

    /**
     * 根据身份证号获取所在区域
     * @param string $idCard 身份证号
     * @return mixed|string|null
     */
    public static function region(string $idCard)
    {
        if (empty($idCard)) return null;
        return substr($idCard, 0, 6);
    }

    /**
     * 判断身份证号是否正确
     * @param $idCard 身份证号
     * @return string
     */
    static function isIdentity($idCard)
    {
        if (strlen($idCard) != 18) return false;
        #  转化为大写，如出现x
        $idCard = strtoupper($idCard);
        #  加权因子
        $wi = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        $ai = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        #  按顺序循环处理前17位
        $sigma = 0;
        #  提取前17位的其中一位，并将变量类型转为实数
        for ($i = 0; $i < 17; $i++) {
            $b = (int)$idCard{$i};
            #  提取相应的加权因子
            $w = $wi[$i];
            #  把从身份证号码中提取的一位数字和加权因子相乘，并累加
            $sigma += $b * $w;
        }
        #  计算序号
        $sidcard = $sigma % 11;
        #  按照序号从校验码串中提取相应的字符。
        $check_idcard = $ai[$sidcard];
        if ($idCard{17} == $check_idcard) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 根据身份证详细地址拆分省市县区地址
     * @param string $address 包含省市县详细地址
     * @return array
     */
    public static function parseAddressInfo(string $address): array
    {
        $address = trim($address);
        if ($address == '') {
            return [];
        }
        preg_match('/(.*?(省|自治区|北京市|天津市|上海市|重庆市))/', $address, $matches);
        if (count($matches) > 1) {
            $province = $matches[count($matches) - 2];
            $address = str_replace($province, '', $address);
        }
        preg_match('/(.*?(市|自治州|地区|区划|县))/', $address, $matches);
        if (count($matches) > 1) {
            $city = $matches[count($matches) - 2];
            $address = str_replace($city, '', $address);
        }
        preg_match('/(.*?(区|县|镇|乡|街道))/', $address, $matches);
        if (count($matches) > 1) {
            $area = $matches[count($matches) - 2];
            $address = str_replace($area, '', $address);
        }
        return [
            'province' => isset($province) ? $province : '',
            'city' => isset($city) ? $city : '',
            'area' => isset($area) ? $area : '',
            'address' => isset($address) ? $address : '',
        ];
    }
}