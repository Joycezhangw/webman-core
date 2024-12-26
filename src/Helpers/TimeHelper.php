<?php

namespace Landao\WebmanCore\Helpers;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class TimeHelper
{
    /**
     * 获取今日开始时间戳和结束时间戳
     *
     * 语法：mktime(hour,minute,second,month,day,year) => (小时,分钟,秒,月份,天,年)
     */
    public static function today()
    {
        try {
            // 创建 DateTime 对象并设置时区为 UTC
            $dateTime = new DateTime('now', new DateTimeZone('UTC'));

            // 设置时间为当天的开始时间 (00:00:00)
            $startOfDay = clone $dateTime;
            $startOfDay->setTime(0, 0, 0);

            // 设置时间为当天的结束时间 (23:59:59)
            $endOfDay = clone $dateTime;
            $endOfDay->setTime(23, 59, 59);

            // 返回开始和结束时间的时间戳
            return [
                $startOfDay->getTimestamp(),
                $endOfDay->getTimestamp()
            ];
        } catch (Exception $e) {
            // 处理异常情况，可以记录日志或抛出自定义异常
            throw new Exception("Failed to get today's timestamps: " . $e->getMessage());
        }
    }

    /**
     * 昨日
     *
     * @return array
     */
    public static function yesterday()
    {

        // 获取昨天的时间戳
        $yesterdayStartOfDay = strtotime('yesterday');
        $yesterdayEndOfDay = strtotime('yesterday 23:59:59');

        // 检查是否成功获取时间戳
        if ($yesterdayStartOfDay === false || $yesterdayEndOfDay === false) {
            throw new Exception("Failed to calculate yesterday's timestamp");
        }

        return [$yesterdayStartOfDay, $yesterdayEndOfDay];
    }

    /**
     * 本周
     * @return array
     */
    public static function week()
    {
        try {
            // 获取当前日期
            $now = new DateTime();
            $year = (int)$now->format('Y');
            $month = (int)$now->format('m');
            $day = (int)$now->format('d');
            $weekDay = (int)$now->format('w'); // 星期几，0 表示周日

            if ($weekDay == 0) {
                $weekDay = 7;
            }

            // 计算本周的开始时间和结束时间
            $startDay = $day - $weekDay + 1;
            $endDay = $startDay + 6;

            // 创建开始时间和结束时间的 DateTime 对象
            $startDate = new DateTime("$year-$month-$startDay");
            $endDate = new DateTime("$year-$month-$endDay");

            // 设置结束时间为当天的最后一秒
            $endDate->setTime(23, 59, 59);

            // 返回时间戳
            return [
                $startDate->getTimestamp(),
                $endDate->getTimestamp()
            ];
        } catch (Exception $e) {
            // 处理异常情况
            throw new Exception("日期处理失败: " . $e->getMessage());
        }
    }


    /**
     * 上周
     *
     * @return array
     */
    public static function lastWeek()
    {
        try {
            // 获取当前时间
            $now = new DateTime();

            // 设置时区（根据需要调整）
            $now->setTimezone(new DateTimeZone('UTC'));

            // 计算上周一的时间戳
            $now->modify('last week monday');
            $startOfWeek = $now->getTimestamp();

            // 计算上周日的时间戳
            $now->modify('+6 days');
            $endOfWeek = $now->getTimestamp() + 24 * 3600 - 1;

            return [$startOfWeek, $endOfWeek];
        } catch (Exception $e) {
            // 异常处理
            throw new Exception("Time calculation failed: " . $e->getMessage());
        }
    }


    /**
     * 本月
     *
     * @return array
     */
    public static function thisMonth()
    {
        try {
            // 创建当前时间的 DateTime 对象
            $now = new DateTime();

            // 设置月初时间
            $firstDay = clone $now;
            $firstDay->modify('first day of this month');
            $firstDay->setTime(0, 0, 0);

            // 设置月末时间
            $lastDay = clone $now;
            $lastDay->modify('last day of this month');
            $lastDay->setTime(23, 59, 59);

            // 返回月初和月末的时间戳
            return [
                $firstDay->getTimestamp(),
                $lastDay->getTimestamp()
            ];
        } catch (Exception $e) {
            // 处理异常情况
            throw new Exception("Date processing error: " . $e->getMessage());
        }
    }


    /**
     * 上个月
     *
     * @return array
     */
    public static function lastMonth()
    {
        try {
            // 获取当前时间
            $now = new DateTime();

            // 修改为上个月的第一天
            $begin = clone $now;
            $begin->modify('first day of previous month');
            $begin->setTime(0, 0, 0);

            // 修改为上个月的最后一天
            $end = clone $now;
            $end->modify('last day of previous month');
            $end->setTime(23, 59, 59);

            return [$begin->getTimestamp(), $end->getTimestamp()];
        } catch (Exception $e) {
            // 处理异常，可以根据实际需求修改
            throw new Exception("日期处理失败: " . $e->getMessage());
        }
    }


    /**
     * 几个月前
     *
     * @param integer $month 月份
     * @return array
     */
    public static function monthsAgo($month)
    {
        // 验证输入参数
        if (!is_int($month) || $month <= 0 || $month > 12) {
            throw new InvalidArgumentException("Month must be a positive integer between 1 and 12.");
        }

        // 获取当前时间并设置时区
        $timezone = new DateTimeZone(date_default_timezone_get());
        $now = new DateTime('now', $timezone);

        // 计算几个月前的日期
        $start = clone $now;
        $start->modify("-$month month")->modify('first day of this month')->setTime(0, 0, 0);

        $end = clone $start;
        $end->modify('last day of this month')->setTime(23, 59, 59);

        // 确保起始和结束时间在同一月份
        if ($start->format('m') != $end->format('m')) {
            $end->modify('-1 day');
        }

        return [
            'start' => $start->getTimestamp(),
            'end' => $end->getTimestamp(),
        ];
    }


    /**
     * 返回今年开始和结束的时间戳
     *
     * @return array
     */
    public static function year()
    {
        $timezone = new DateTimeZone('UTC'); // 显式指定时区
        $year = (int)date('Y', time());

        $startOfYear = (new DateTime("{$year}-01-01", $timezone))->getTimestamp();
        $endOfYear = (new DateTime("{$year}-12-31 23:59:59", $timezone))->getTimestamp();

        return [$startOfYear, $endOfYear];
    }


    /**
     * 返回去年开始和结束的时间戳
     *
     * @return array
     */
    public static function lastYear()
    {
        try {
            // 设置时区为 UTC，确保一致性
            $timezone = new DateTimeZone('UTC');

            // 获取当前年份并减去一年
            $currentYear = (int)date('Y', time());
            $lastYear = $currentYear - 1;

            // 创建去年的第一天和最后一天的 DateTime 对象
            $startOfYear = new DateTime("$lastYear-01-01", $timezone);
            $endOfYear = new DateTime("$lastYear-12-31 23:59:59", $timezone);

            // 返回时间戳数组
            return [
                $startOfYear->getTimestamp(),
                $endOfYear->getTimestamp()
            ];
        } catch (Exception $e) {
            // 处理异常情况
            throw new Exception("Failed to calculate last year's timestamps: " . $e->getMessage());
        }
    }


    /**
     * 获取几天前零点到现在/昨日结束的时间戳
     *
     * @param int $day 天数
     * @param bool $now 返回现在或者昨天结束时间戳
     * @return array
     */
    public static function dayToNow($day = 1, $now = true)
    {
        // 校验参数
        if (!is_int($day) || $day < 0) {
            throw new \InvalidArgumentException('Parameter $day must be a non-negative integer.');
        }

        // 设置时区
        $timezone = new DateTimeZone('UTC'); // 或者根据需要选择其他时区
        $currentTime = new DateTime('now', $timezone);

        // 获取结束时间
        if ($now) {
            $endTime = clone $currentTime;
        } else {
            $endTime = (new DateTime('yesterday', $timezone));
        }

        // 计算开始时间
        $startTime = clone $currentTime;
        $startTime->modify("-$day days")->setTime(0, 0, 0);

        return [
            $startTime->getTimestamp(),
            $endTime->getTimestamp()
        ];
    }


    /**
     * 返回几天前的时间戳
     *
     * @param int $day
     * @return int
     */
    public static function daysAgo($day = 1)
    {
        // 验证输入参数
        if (!is_int($day) || $day < 0) {
            throw new \InvalidArgumentException('The parameter "day" must be a non-negative integer.');
        }

        // 获取当前时间戳
        $nowTime = time();
        return $nowTime - self::daysToSecond($day);
    }

    /**
     * 返回几天后的时间戳
     *
     * @param int $day
     * @return int
     */
    public static function daysAfter($day = 1, $nowTime = 0)
    {
        // 输入验证
        if (!is_numeric($day) || $day < 0) {
            throw new \InvalidArgumentException('Parameter $day must be a non-negative number.');
        }

        if ($nowTime !== 0 && !is_numeric($nowTime)) {
            throw new \InvalidArgumentException('Parameter $nowTime must be a numeric timestamp or 0 for current time.');
        }

        try {
            // 使用 DateTime 处理日期和时间，避免时间戳溢出
            $dateTime = new DateTime();
            if ($nowTime !== 0) {
                $dateTime->setTimestamp($nowTime);
            } else {
                $dateTime->setTimestamp(time());
            }

            // 增加指定天数
            $dateTime->modify("+$day days");

            // 返回新的时间戳
            return $dateTime->getTimestamp();
        } catch (Exception $e) {
            // 异常处理
            throw new RuntimeException('Error occurred while calculating days after: ' . $e->getMessage());
        }
    }


    /**
     * 将天数转换成秒数
     *
     * @param int $day 天数，默认为1
     * @return int 转换后的秒数
     * @throws InvalidArgumentException 如果参数不是正整数
     */
    public static function daysToSecond(int $day = 1): int
    {
        if ($day < 0) {
            throw new InvalidArgumentException('天数不能为负数');
        }
        return $day * 86400;
    }

    /**
     * 周数转换成秒数
     *
     * @param int $week 周数，默认为1
     * @return int 转换后的秒数
     * @throws InvalidArgumentException 如果参数不是正整数
     */
    public static function weekToSecond(int $week = 1): int
    {
        if ($week < 0) {
            throw new InvalidArgumentException('周数不能为负数');
        }
        return self::daysToSecond(7) * $week;
    }

    /**
     * 某年
     * @param $year
     * @return array
     */
    public static function someYear($year)
    {
        // 输入验证
        if (!is_numeric($year) || $year < 1000 || $year > 9999) {
            throw new InvalidArgumentException("Invalid year: $year");
        }

        // 使用 DateTime 处理日期
        $start_time = new DateTime("$year-01-01 00:00:00");
        $end_time = (new DateTime("$year-12-31 23:59:59"));

        return [
            $start_time->getTimestamp(),
            $end_time->getTimestamp()
        ];
    }


    /**
     * 某月
     *
     * @param int $year
     * @param int $month
     * @return array
     */
    public static function aMonth($year = 0, $month = 0): array
    {
        // 输入验证
        if (!is_int($year) || !is_int($month)) {
            throw new InvalidArgumentException('Year and month must be integers.');
        }

        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');

        $year = ($year == 0) ? $currentYear : $year;
        $month = ($month == 0) ? $currentMonth : $month;

        if ($year < 1 || $month < 1 || $month > 12) {
            throw new InvalidArgumentException('Invalid year or month.');
        }

        try {
            // 使用 DateTime 处理日期
            $startDate = new DateTime("$year-$month-01");
            $endDate = clone $startDate;
            $endDate->modify('last day of this month');
            $endDate->setTime(23, 59, 59);

            return [
                $startDate->getTimestamp(),
                $endDate->getTimestamp()
            ];
        } catch (Exception $e) {
            throw new InvalidArgumentException('Failed to create date: ' . $e->getMessage());
        }
    }


    /**
     * 根据日期获取是星期几
     * @param int $time
     * @param string $format
     * @return mixed
     */
    public static function getWeekName(int $time, $format = "周")
    {
        // 检查时间戳是否有效
        if (!checkdate(date('m', $time), date('d', $time), date('Y', $time))) {
            throw new InvalidArgumentException("Invalid timestamp provided");
        }

        // 获取星期几（0-6 对应 周日到周六）
        $week = (int)date('w', $time);

        // 定义星期名称数组
        $weekName = ['日', '一', '二', '三', '四', '五', '六'];

        // 返回格式化后的星期名称
        return $format . $weekName[$week];
    }


    /**
     * 获取指定开始日期到结束日期
     * @param int $start_day 开始天数，以当天开始：0，每增加一天，在当天基础上 +1
     * @param int $end_day
     * @return array
     */
    public static function getFutureHowManyDays(int $start_day = 0, int $end_day = 7): array
    {
        // 边界条件检查
        if ($start_day >= $end_day) {
            throw new InvalidArgumentException('Start day must be less than end day.');
        }

        $dateArr = [];
        $currentDate = new DateTime();

        for ($i = $start_day; $i < $end_day; $i++) {
            $futureDate = clone $currentDate;
            $futureDate->modify("+$i days");
            $dateArr[$i] = $futureDate->format('Y-m-d');
        }

        return $dateArr;
    }


    /**
     * 根据开始/结束时间，并以分钟为分界点生成 时间范围
     * @param string $start_time
     * @param string $end_time
     * @param int $minute
     * @return array
     */
    public static function buildEveryDayTimeRange(string $start_time = '09:00', string $end_time = '22:30', int $minute = 30)
    {
        // 验证时间格式
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $start_time) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $end_time)) {
            throw new InvalidArgumentException('Invalid time format');
        }

        $startTime = strtotime($start_time);
        $endTime = strtotime($end_time);

        // 检查时间范围是否有效
        if ($startTime === false || $endTime === false || $startTime > $endTime) {
            throw new InvalidArgumentException('Invalid time range');
        }

        $arr = [];
        for ($i = $startTime; $i < $endTime + 60 * $minute; $i += 60 * $minute) {
            $currentTime = date("H:i", $i);
            $nextTime = date("H:i", $i + 60 * $minute);
            if (strtotime($nextTime) <= $endTime || $i == $startTime) {
                $arr[] = $currentTime . '-' . $nextTime;
            }
        }

        return array_values(array_slice($arr, 0, count($arr) - 1));
    }


    /**
     * 格式化时间戳
     *
     * @param $time
     * @return string
     */
    public static function formatTimestamp(int $time): string
    {
        // 输入验证
        if (!is_numeric($time) || $time < 0) {
            throw new InvalidArgumentException("时间戳必须是非负数字");
        }

        $time = (int)$time;

        // 边界条件处理
        if ($time === 0) {
            return "0 天 0 小时 0 分钟";
        }

        // 计算天、小时、分钟
        $days = intdiv($time, 86400); // 一天有 86400 秒
        $time %= 86400;
        $hours = intdiv($time, 3600); // 一小时有 3600 秒
        $time %= 3600;
        $min = intdiv($time, 60); // 一分钟有 60 秒

        return $days . " 天 " . $hours . " 小时 " . $min . " 分钟 ";
    }


    /**
     * 格式化时间戳为指定日期格式
     *
     * @param int $time 时间戳，默认为0表示不输出
     * @param string $format 日期格式，默认为'Y-m-d H:i:s'
     * @return string 格式化后的日期字符串或空字符串
     */
    public static function formatParseTime(int $time = 0, string $format = 'Y-m-d H:i:s'): string
    {
        // 验证时间戳是否有效
        if ($time < 0 || $time > PHP_INT_MAX) {
            return '';
        }

        // 验证日期格式是否合法
        $allowedFormats = ['Y-m-d', 'Y-m-d H:i:s', 'Ymd', 'YmdHis'];
        if (!in_array($format, $allowedFormats)) {
            return '';
        }

        try {
            return $time > 0 ? date($format, $time) : '';
        } catch (Exception $e) {
            // 记录日志或采取其他措施
            error_log("Error formatting timestamp: " . $e->getMessage());
            return '';
        }
    }


    /**
     * 生成时间戳
     * @param int $accuracy 精度 默认微妙
     * @return string
     */
    public static function buildTimestamp($accuracy = 1000)
    {
        // 输入验证
        if (!is_numeric($accuracy) || $accuracy <= 0) {
            throw new InvalidArgumentException("Accuracy must be a positive number.");
        }

        // 获取当前时间戳（浮点数形式）
        $timestamp = microtime(true);

        // 计算并返回精确的时间戳
        return round($timestamp * $accuracy);
    }

    const SECOND = 1;
    const MINUTE = 60 * self::SECOND;
    const HOUR = 60 * self::MINUTE;
    const DAY = 24 * self::HOUR;

    /**
     * 当前时间多久之前，以天核算
     * @param int $curTime
     * @return false|string
     */
    public static function formatDateLongAgo(int $curTime)
    {
        // 验证输入参数
        if ($curTime < 0 || $curTime > time()) {
            throw new InvalidArgumentException('Invalid timestamp provided.');
        }

        $now = new DateTime();
        $curDateTime = new DateTime('@' . $curTime);

        // 计算今天的最后一秒
        $todayLast = clone $now;
        $todayLast->setTime(23, 59, 59);
        $agoTime = $todayLast->getTimestamp() - $curTime;
        $agoDay = intval(floor($agoTime / self::DAY));

        // 格式化时间部分
        $timePart = $curDateTime->format('H:i');

        if ($agoDay === 0) {
            return '今天 ' . $timePart;
        } elseif ($agoDay === 1) {
            return '昨天 ' . $timePart;
        } elseif ($agoDay === 2) {
            return '前天 ' . $timePart;
        } elseif ($agoDay >= 3 && $agoDay <= 15) {
            return $agoDay . '天前 ' . $timePart;
        } else {
            $yearFormat = $curDateTime->format('Y') != $now->format('Y') ? 'Y-' : '';
            return $yearFormat . $curDateTime->format('m-d H:i');
        }
    }


    /**
     * 当前时间多久之前，以分钟到天核算
     * @param int $curTime
     * @return false|string
     */
    public static function formatTimeLongAgo(int $curTime)
    {
        if ($curTime < 0 || !is_int($curTime)) {
            throw new InvalidArgumentException('Invalid timestamp provided.');
        }
        $todayLast = strtotime(date('Y-m-d 23:59:59'));
        $agoTimestamp = time() - $curTime;
        $agoTime = $todayLast - $curTime;
        $agoDay = intval(floor($agoTime / self::DAY));

        if ($agoTimestamp < self::MINUTE) {
            return '刚刚';
        } elseif ($agoTimestamp < self::HOUR) {
            return ceil($agoTimestamp / self::MINUTE) . '分钟前';
        } elseif ($agoTimestamp < 12 * self::HOUR) {
            return ceil($agoTimestamp / self::HOUR) . '小时前';
        } elseif ($agoDay === 0) {
            return '今天 ' . date('H:i', $curTime);
        } elseif ($agoDay === 1) {
            return '昨天 ' . date('H:i', $curTime);
        } elseif ($agoDay === 2) {
            return '前天 ' . date('H:i', $curTime);
        } elseif ($agoDay >= 2 && $agoDay <= 15) {
            return $agoDay . '天前 ' . date('H:i', $curTime);
        } else {
            $format = date('Y') != date('Y', $curTime) ? 'Y-m-d H:i' : 'm-d H:i';
            return date($format, $curTime);
        }
    }

    /**
     * 每周重复、隔周重复
     * 根据开始日期至结束日期以及指定周几获得对应重复日期
     * @param string $startDate 开始日期 Y-m-d
     * @param string $endDate 结束日期 Y-m-d
     * @param array $week 选中数字周几 [1,2,3,4,5,6,7] 或中文周几 ['周天', '周一', '周二', '周三', '周四', '周五', '周六']
     * @param bool $isApartWeek 是否隔周排期 true | false
     * @param bool $isNumWeek 是否是数字周几 true | false
     * @return array
     */
    public static function generateDateWeek(string $startDate, string $endDate, array $week = [], bool $isApartWeek = false, bool $isNumWeek = true): array
    {
        // 输入验证
        if (!self::isValidDate($startDate) || !self::isValidDate($endDate)) {
            throw new InvalidArgumentException("Invalid date format");
        }
        if (strtotime($startDate) > strtotime($endDate)) {
            throw new InvalidArgumentException("Start date must be before or equal to end date");
        }
        if (empty($week)) {
            return [];
        }

        $start_date = strtotime($startDate);
        $end_date = strtotime($endDate);
        $weekArr = $isNumWeek ? ['7', '1', '2', '3', '4', '5', '6'] : ['周天', '周一', '周二', '周三', '周四', '周五', '周六'];

        // 组建数组格式 $dataWeek['日期'] => 星期
        $dateWeek = [];
        for ($current_date = $start_date; $current_date <= $end_date; $current_date += 86400) {
            $num_week = date('w', $current_date);
            $dateWeek[date('Y-m-d', $current_date)] = $weekArr[$num_week];
        }

        if ($isApartWeek) {
            // 以周日为节点，将每周日期规整在一起
            $index = 0;
            $separateDateWeek = [];
            foreach ($dateWeek as $key => $item) {
                $separateDateWeek[$index][] = [$key => $item];
                if ((string)$item == (string)$weekArr[0]) {
                    $index++;
                }
            }

            // 对以每周日期规整一起数组取偶，提出隔周日期数据
            $evenDateWeek = [];
            foreach ($separateDateWeek as $key => $item) {
                if (!($key & 1)) {
                    foreach ($item as $subItem) {
                        $evenDateWeek = array_merge($evenDateWeek, $subItem);
                    }
                }
            }

            // 更新 $dateWeek 为隔周日期
            $dateWeek = $evenDateWeek;
        }

        // 查找两个数组的交集，即获取提交的星期对应的日期
        $newDate = [];
        foreach ($dateWeek as $date => $day) {
            if (in_array($day, $week)) {
                $newDate[] = $date;
            }
        }

        return $newDate;
    }

    private static function isValidDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }


    /**
     * 对比两组时间交叉
     * @param int $beginTime1
     * @param int $endTime1
     * @param int $beginTime2
     * @param int $endTime2
     * @return bool
     */
    public static function isDateTimeCross($beginTime1 = 0, $endTime1 = 0, $beginTime2 = 0, $endTime2 = 0)
    {
        // 参数类型检查
        if (!is_int($beginTime1) || !is_int($endTime1) || !is_int($beginTime2) || !is_int($endTime2)) {
            throw new InvalidArgumentException('All parameters must be integers.');
        }

        // 检查时间范围有效性
        if ($beginTime1 > $endTime1 || $beginTime2 > $endTime2) {
            throw new InvalidArgumentException('Invalid time range: beginTime should not be greater than endTime.');
        }

        // 判断时间段是否有交集
        return max($beginTime1, $beginTime2) <= min($endTime1, $endTime2);
    }


    /**
     * 指定年月，获取日期，并按每周分组。开始时间周一至周日排序
     * @param string $date 年月格式，如 'YYYY-MM'
     * @param string $format 输出日期格式，默认 'Y-m-d'
     * @param string $timezone 时区，默认 'Asia/Shanghai'
     * @return array
     * @throws \Exception
     */
    public static function getWeekAMonth(string $date, string $format = 'Y-m-d', string $timezone = 'Asia/Shanghai')
    {
        // 输入验证
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $date)) {
            throw new \InvalidArgumentException('Invalid date format, should be YYYY-MM');
        }

        try {
            // 初始化时间
            $time = strtotime($date . '-01');
            $month = intval(date('m', $time));
            $carbon = new Carbon($date . '-01', $timezone);

            $weeks = [];
            while (intval($carbon->month) == $month) {
                $weekNumber = $carbon->weekNumberInMonth;
                if (!isset($weeks[$weekNumber])) {
                    $weeks[$weekNumber] = [];
                }
                $weeks[$weekNumber][] = $carbon->format($format);
                $carbon->addDay();
            }

            return $weeks;
        } catch (\Exception $e) {
            throw new \Exception('Error processing date: ' . $e->getMessage());
        }
    }


    /**
     * 指定年月，获取每周起止日期，并按每周分组
     * @param $date
     * @param string $format
     * @return array
     * @throws \Exception
     */
    public static function getWeekStartEndAMoth($date, string $format = 'Y-m-d')
    {
        // 验证输入参数
        if (!is_string($date) || !DateTime::createFromFormat($format, $date)) {
            throw new InvalidArgumentException('Invalid date or format.');
        }

        try {
            // 获取每周的日期范围
            $weeks = self::getWeekAMonth($date, $format);

            // 检查 weeks 是否为数组
            if (!is_array($weeks)) {
                throw new InvalidArgumentException('getWeekAMonth should return an array.');
            }

            $weekData = [];
            foreach ($weeks as $key => $item) {
                // 确保每个 item 是一个有效的数组
                if (!is_array($item) || empty($item)) {
                    continue;
                }
                $weekData[$key] = [$item[0], end($item)];
            }

            return $weekData;
        } catch (Exception $e) {
            // 记录异常日志
            error_log("Error in getWeekStartEndAMoth: " . $e->getMessage());
            throw $e; // 重新抛出异常以便上层处理
        }
    }

    /**
     * 指定日期获取，所在月份周、年、季度
     * @param  $timestamp 时间
     * @param bool $isWeekMonday true ： 一个月以周一开始，为7天；false :一个月以周日开始
     * @return array
     */
    public static function getWeekAndQInAMonth($timestamp, bool $isWeekMonday = true, string $timezone = 'Asia/Shanghai'): array
    {
        // 检查 $isWeekMonday 是否为布尔值
        if (!is_bool($isWeekMonday)) {
            throw new InvalidArgumentException('The parameter $isWeekMonday must be a boolean.');
        }

        try {
            // 解析时间戳并设置时区
            $dt = Carbon::parse($timestamp)->setTimezone($timezone);
        } catch (\Exception $e) {
            // 处理解析失败的情况
            throw new InvalidArgumentException('Invalid timestamp format: ' . $e->getMessage());
        }

        return [
            'week' => $isWeekMonday ? $dt->weekNumberInMonth : $dt->weekOfMonth,
            'year' => $dt->year,
            'quarter' => intval(($dt->month - 1) / 3 + 1)
        ];
    }

}