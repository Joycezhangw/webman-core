<?php
declare (strict_types=1);

namespace Landao\WebmanCore\ServiceProvider;


use Landao\WebmanCore\LanDaoBootstrap;
use Landao\WebmanCore\ParseAnnotation\InteractsWithRoute;

class RouteServiceProvider
{
    public static function register(): void
    {
        // 检查PHP版本
        if (floatval(PHP_VERSION) < 8.1) {
            throw new \RuntimeException('PHP version must be greater than 8.0. Your current version is ' . PHP_VERSION);
        }
        if (!LanDaoBootstrap::isIgnoreProcess()) {
            //路由注解
            $withRoute = new InteractsWithRoute();
            $withRoute->registerRoutes();
        }
    }

}