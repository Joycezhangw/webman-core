<?php

namespace Landao\WebmanCore\Log;

use support\Db;
use support\Log;

class ListenOrm
{
    public static function start(): void
    {
        $config = config('plugin.landao.webman-core.app.listenORM', [
            'enable' => true,
            'console' => false,
            'file' => true,
        ]);
        if (!$config['enable']) {
            return;
        }
        Db::listen(function ($query) use ($config) {
            $sql = $query->sql;
            $time = $query->time;
            if ($sql === 'select 1') {
                // 心跳
                return;
            }
            $bindings = [];
            if ($query->bindings) {
                foreach ($query->bindings as $v) {
                    if (is_numeric($v)) {
                        $bindings[] = $v;
                    } else {
                        $bindings[] = '"' . strval($v) . '"';
                    }
                }
            }
            $sql = self::replacePlaceholders($sql, $bindings);
            $log = $sql . " [{$time}ms]";
            // 打印到控制台
            if ($config['console']) {
                echo "[" . date("Y-m-d H:i:s") . "]" . "\033[32m" . $log . "\033[0m" . PHP_EOL;
            }
            // 记录到日志文件
            if ($config['file']) {
                Log::channel('plugin.landao.webman-core.sql')->info($log);
            }
        });
    }

    /**
     * 字符串处理
     * @param $sql
     * @param $params
     * @return mixed|string
     */
    public static function replacePlaceholders($sql, $params)
    {
        if (empty($params)) {
            return $sql;
        }
        $parts = explode('?', $sql);
        $result = $parts[0];
        $paramCount = count($params);
        for ($i = 0; $i < $paramCount && $i < count($parts) - 1; $i++) {
            $value = $params[$i];
            $result .= $value . $parts[$i + 1];
        }
        if (count($parts) - 1 > $paramCount) {
            $result .= implode('?', array_slice($parts, $paramCount + 1));
        }
        return $result;
    }

}