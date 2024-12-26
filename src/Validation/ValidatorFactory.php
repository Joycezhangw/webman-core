<?php

namespace Landao\WebmanCore\Validation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation;
use Illuminate\Validation;

class ValidatorFactory
{
    private $factory;

    public function __construct()
    {
        $this->factory = new Validation\Factory($this->loadTranslator());
    }

    protected function loadTranslator()
    {
        $path = config('translation.path');
        $locale = config('translation.locale');

        $filesystem = new Filesystem();
        $loader = new Translation\FileLoader($filesystem, $path);
        $loader->addNamespace('lang', $path);
        $loader->load($locale, 'validation', 'lang');

        return new Translation\Translator($loader, $locale);
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->factory, $method], $args);
    }
}