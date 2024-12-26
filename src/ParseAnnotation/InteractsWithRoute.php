<?php

namespace Landao\WebmanCore\ParseAnnotation;

use Illuminate\Support\Arr;

class InteractsWithRoute
{
    public function registerRoutes(): void
    {
        if ($this->shouldRegisterRoutes()) {
            $routeRegistrar = new RouteRegistrar();//)->useMiddleware(config('plugin.landao.webman-core.app.annotation.route.middleware') ?? []);
            $controllerSuffix = (string)config('app.controller_suffix');

            collect($this->getRouteDirectories())->each(function (string|array $directory, string|int $namespace) use ($routeRegistrar, $controllerSuffix) {
                if (is_array($directory)) {
                    // 命名空间后缀
                    $options = Arr::except($directory, ['namespace', 'base_path', 'patterns', 'not_patterns']);
                    $routeRegistrar
                        ->useRootNamespace($directory['namespace'] ?? 'App\\')
                        ->useBasePath($directory['base_path'] ?? (isset($directory['namespace']) ? $namespace : app_path()))
                        ->group($options, fn() => $routeRegistrar->registerDirectory($namespace, $directory['patterns'] ?? ['*' . $controllerSuffix . '.php'], $directory['not_patterns'] ?? []));
                }
            });

        }
    }

    private function shouldRegisterRoutes(): bool
    {
        if (!config('plugin.landao.webman-core.app.annotation.route.enable')) {
            return false;
        }

        return true;
    }

    private function getRouteDirectories(): array
    {
        return config('plugin.landao.webman-core.app.annotation.route.directories');
    }
}