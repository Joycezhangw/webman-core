<?php

namespace Landao\WebmanCore\Database;

abstract class Seeder
{
    /**
     * 运行其他填充器
     */
    public function call($classes)
    {
        $classes = is_array($classes) ? $classes : func_get_args();

        foreach ($classes as $class) {
            if (is_string($class)) {
                $seeder = new $class();

                if (method_exists($seeder, 'run')) {
                    $seeder->run();
                }
            }
        }
    }

    /**
     * 运行填充
     */
    abstract public function run(): void;
}