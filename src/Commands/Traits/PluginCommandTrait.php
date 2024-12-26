<?php

namespace Landao\WebmanCore\Commands\Traits;


trait PluginCommandTrait
{
    public function getPluginName(): string
    {
        return $this->argument('plugin') ?: 'app';
    }

    public function getPath(): string
    {
        return base_path('plugin');
    }

    /**
     * 获取webman应用插件下路径
     * @param $pluginName
     * @return string
     */
    public function getpluginPath($pluginName)
    {
        try {
            $class = "\\plugin\\$pluginName\\api\\Install";
            if (!method_exists($class, 'uninstall')) {
                throw new \RuntimeException("Method $class::uninstall not exists");
            }
            return $this->getPath() . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR;
        } catch (\RuntimeException $e) {
            return '';//base_path('app') . DIRECTORY_SEPARATOR;
        }
    }

}