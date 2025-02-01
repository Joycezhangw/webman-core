<?php

namespace Landao\WebmanCore;

use Landao\WebmanCore\Log\ListenOrm;
use Webman\Bootstrap;
use Workerman\Worker;

class LanDaoBootstrap implements Bootstrap
{

    /**
     * 进程名称
     * @var string
     */
    protected static string $workerName = '';

    /**
     * 忽略的进程名称
     * @var string[]
     */
    public static array $ignoreProcess = [
        '',
        'monitor',
    ];


    public static function start(?Worker $worker): void
    {
        // 检查PHP版本
        if (floatval(PHP_VERSION) < 8.1) {
            throw new \RuntimeException('PHP version must be greater than 8.0. Your current version is ' . PHP_VERSION);
        }

        $config = config('plugin.landao.webman-core.app', [
            'enable' => true
        ]);
        if (!$config['enable']) {
            return;
        }

        // 跳过忽略的进程
        if (isset($worker->name) && self::isIgnoreProcess(self::$workerName = $worker->name)) {
            return;
        }
        //监听Sql
        ListenOrm::start();
    }


    /**
     * 是否为忽略的进程
     * @param string|null $name
     * @return bool
     */
    public static function isIgnoreProcess(string $name = null): bool
    {
        if (empty($name)) {
            $name = self::$workerName;
        }

        return in_array($name, self::$ignoreProcess);
    }
}