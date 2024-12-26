<?php

namespace Landao\WebmanCore\Helpers;

class FiltersHelper
{
    /**
     * 将图片中的相对路径换成绝对路径，在小程序中使用，将所有物理路径的图片转成url路径图片
     * @param string $html_content
     * @return string|string[]|null
     */
    public static function richTextAbsoluteUrl($html_content = '')
    {

        $pregRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
        $content = preg_replace($pregRule, '<img src="' . asset('${1}') . '" style="max-width:100%;height:auto;display:block">', $html_content);

        return $content;
    }

    /**
     * 表单提交字符过滤
     * @param $string
     * @return string|string[]|null
     */
    public static function stringFilter($string)
    {
        $regArr = array(
            "/\s+/", //过滤多余空白
            //过滤 <script>等可能引入恶意内容或恶意改变显示布局的代码,如果不需要插入flash等,还可以加入<object>的过滤
            "/<(\/?)(script|i?frame|style|html|body|title|link|meta|\?|\%)([^>]*?)>/isU",
            "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU", //过滤javascript的on事件
        );
        $tarr = array(
            " ",
            " ", //如果要直接清除不安全的标签，这里可以留空
            " ",
        );
        $string = preg_replace($regArr, $tarr, $string);
        return $string;
    }

    /**
     * 过滤提交字符串，不含空格
     * @param string $string
     * @return string|string[]|null
     */
    public static function stringSpecialHtmlFilter(string $string)
    {
        $regArr = array(
            //过滤 <script>等可能引入恶意内容或恶意改变显示布局的代码,如果不需要插入flash等,还可以加入<object>的过滤
            "/<(\/?)(script|i?frame|style|html|body|title|link|meta|\?|\%)([^>]*?)>/isU",
            "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU", //过滤javascript的on事件
        );
        $tarr = array(
            " ", //如果要直接清除不安全的标签，这里可以留空
            " ",
        );
        $string = preg_replace($regArr, $tarr, $string);
        return $string;
    }

    /**
     * 友好显示学员运动积分排名
     * @param int $num
     * @return int|string
     */
    public static function filterRank($num = 0)
    {
        if ($num >= 10000) {
            return round($num / 10000 * 100) / 100 . ' W';
        } elseif ($num >= 1000) {
            return round($num / 1000 * 100) / 100 . ' K';
        } else {
            return $num;
        }
    }


    /**
     * 过滤手机号中间几位数
     * @param $mobile
     * @return string
     */
    public static function filterMobile($mobile)
    {
        return preg_replace("/(\d{3})\d{4}(\d{4})/", "\$1****\$2", $mobile);
    }

    /**
     * 隐去身份证号中间几位数字
     * @param string $identity_no
     * @return string
     */
    public static function filterIdentityNo(string $identity_no): string
    {
        return strlen($identity_no) == 15 ? substr_replace($identity_no, "****", 8, 4) : (strlen($identity_no) == 18 ? substr_replace($identity_no, "****", 10, 4) : "");
    }

    /**
     * 将数组转为string 并带有引号
     * @param array $data
     * @return string
     */
    public static function arrayToString(array $data)
    {
        return "'" . join("','", $data) . "'";
    }

    /**
     * 将 [script|link|style] 标签过滤掉
     * @param $string
     * @return string|string[]|null
     */
    public static function replaceHTML(string $string)
    {
        $preg = "/<script[\s\S]*?<\/script>/i";
        $str = preg_replace($preg, "", $string);
        $preg = "/<link[\s\S]*?<\/link>/i";
        $str = preg_replace($preg, "", $string);
        $preg = "/<style[\s\S]*?<\/style>/i";
        $str = preg_replace($preg, "", $string);
        return $str;
    }

    /**
     * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
     * @param string $str 字符串
     * @param int $head 左侧保留位数
     * @param int $foot 右侧保留位数
     * @return string 格式化后的string
     */
    public static function subStrCut(string $str, $head = 1, $foot = 1)
    {
        $strLen = mb_strlen($str, 'UTF-8');
        $firstStr = mb_substr($str, 0, $head, 'UTF-8');
        $lastStr = mb_substr($str, -$foot, $foot, 'UTF-8');
        return $strLen == 2 ? $firstStr . str_repeat('*', 1) : $firstStr . str_repeat("*", $strLen - 2) . $lastStr;
    }

    /**
     * 生成图片url地址
     * @param string $img
     * @param string $default
     * @return string
     */
    public static function buildImageUri(string $img = '', string $default = '')
    {
        if (!empty ($img)) {
            if (preg_match('/(http:\/\/)|(https:\/\/)/i', $img)) {
                return $img; // 直接粘贴地址
            } else {
                return '';//UrlHelper::asset($img);
            }
        } else {
            if (empty ($default)) {
                return '';//UrlHelper::asset('/static/images/default-avatar.png');
            } else {
                if (preg_match('/(http:\/\/)|(https:\/\/)/i', $default)) {
                    return $default; // 直接粘贴地址
                } else {
                    return '';//UrlHelper::asset($default);
                }
            }
        }
    }

    /**
     * xss过滤函数
     * @param string $string
     * @return string|string[]|null
     */
    public static function filterXSS(string $string)
    {
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);
        $param_one = ['javascript', 'vbscript', 'expression', 'script', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base'];
        $param_two = ['onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'];
        $param = array_merge($param_one, $param_two);
        for ($i = 0; $i < sizeof($param); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($param[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[x|X]0([9][a][b]);?)?';
                    $pattern .= '|(&#0([9][10][13]);?)?';
                    $pattern .= ')?';
                }
                $pattern .= $param[$i][$j];
            }
            $pattern .= '/i';
            $string = preg_replace($pattern, ' ', $string);
        }
        return $string;
    }

    /**
     * 数据脱敏
     * @param mixed $string  需要脱敏的值
     * @param int $start  开始
     * @param int $length  结束
     * @param string $re 脱敏替代字符
     * @return bool|string
     *
     * 例子:
     * FiltersHelper::dataDesensitization('18811113683', 3, 4); //188****3683
     * FiltersHelper::dataDesensitization('杨乐迪', 0, -1); //**迪
     */
    public static function dataDesensitization($string, $start = 0, $length = 0, $re = '*')
    {
        if(empty($string) || empty($length) || empty($re)) return $string;
        $end = $start + $length;
        $strlen = mb_strlen($string);
        $str_arr = array();
        for($i=0; $i<$strlen; $i++) {
            if($i>=$start && $i<$end)
                $str_arr[] = $re;
            else
                $str_arr[] = mb_substr($string, $i, 1);
        }
        return implode('',$str_arr);
    }
}