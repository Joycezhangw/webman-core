<?php

namespace Landao\WebmanCore\Commands\Support;

class GenerateConfigReader
{
    public static function read(string $value, ?string $multiApp = null): GeneratorPath
    {
        $config = config("plugin.landao.webman-core.app.generator.paths.$value");

        // 处理多应用情况
        if ($multiApp) {
            // 获取原始路径或默认路径
            $originalPath = $config ?? ['path' => 'app/'.$value, 'generate' => false];
            // 直接构建多应用路径，使用 basename 获取最后的路径部分
            $config = [
                'path' => "app/{$multiApp}/" . basename($originalPath['path']),
                'generate' => $originalPath['generate'] ?? false
            ];
        }
        return new GeneratorPath($config);
    }
}