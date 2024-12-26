<?php

namespace Landao\WebmanCore\Commands\Traits;

use Illuminate\Support\Str;

trait PathNamespace
{
    public function studly_path(string $path, $ds = '/'): string
    {
        return collect(explode($ds, $this->clean_path($path, $ds)))->map(fn($path) => ($path))->implode($ds);
    }

    public function studly_namespace(string $namespace, $ds = '\\'): string
    {
        return $this->studly_path($namespace, $ds);
    }

    public function path_namespace(string $path): string
    {
        return Str::of($this->studly_path($path))->replace('/', '\\')->trim('\\');
    }

    public function plugin_namespace(string $plugin, ?string $path = null): string
    {
        // 判断是否为多应用场景
        if (method_exists($this, 'option') && $this->option('multi-app')) {
            // 多应用场景下，命名空间为 app\{multi-app名称}
            $plugin_namespace = 'app\\' . $this->option('multi-app');
        } else {
            // 主应用或者webman应用插件
            $plugin_namespace = $plugin !== 'app' ? 'plugin' . '\\' . ($plugin) . '\\app' : 'app';
        }
        $plugin_namespace .= strlen($path) ? '\\' . $this->path_namespace($path) : '';
        return $this->studly_namespace($plugin_namespace);
    }

    public function clean_path(string $path, $ds = '/'): string
    {
        return Str::of($path)->explode($ds)->reject(empty($path))->implode($ds);
    }

    public function app_path(?string $path = null): string
    {
        $config_path = 'app/';
        $app_path = strlen($config_path) ? trim($config_path, '/') : 'app';
        $app_path .= strlen($path) ? '/' . $path : '';

        return $this->clean_path($app_path);
    }
}