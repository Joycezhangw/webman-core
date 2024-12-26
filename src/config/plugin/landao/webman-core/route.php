<?php

//Todo:配置名称需要命名为 route.php 否则 Webman/Route.php 中 static::$collector 将是 null，无法调用 addRoute 方法
\Landao\WebmanCore\ServiceProvider\RouteServiceProvider::register();