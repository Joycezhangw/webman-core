<?php

namespace Landao\WebmanCore\Commands\Make;

use Illuminate\Support\Str;
use Landao\WebmanCore\Commands\Support\GenerateConfigReader;
use Landao\WebmanCore\Commands\Support\Stub;
use Landao\WebmanCore\Commands\Traits\PluginCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeEnumCommand extends GeneratorCommand
{
    use PluginCommandTrait;

    protected $argumentName = 'name';

    protected static $defaultName = 'landao:make-enum';

    protected static $defaultDescription = 'Create a new enum class for the specified plugin.';

    protected function configure()
    {
        $this->setName(static::$defaultName)->setDescription(static::$defaultDescription);
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the enum class.')
            ->addOption('plugin', null, InputOption::VALUE_REQUIRED, 'The name of plugin will be used.')
            ->addOption('multi-app', null, InputOption::VALUE_REQUIRED, 'Create enumeration classes in multiple applications')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Create the class even if the enum already exists');
    }


    public function getDestinationFilePath(): string
    {
        $pluginName = $this->getPluginName();
        $path = $this->getpluginPath($pluginName);
        $multiApp = $this->option('multi-app');
        $filePath = GenerateConfigReader::read('enum', $multiApp)->getPath() ?? 'app/enum';
        return $path . $filePath . '/' . $this->getEnumName() . 'Enum.php';
    }

    protected function getTemplateContents(): string
    {
        $pluginName = $this->getPluginName();
        $module = $pluginName !== 'app' ? $pluginName : 'app';
        return (new Stub($this->getStubName(), [
            'CLASS_NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClassNameWithoutNamespace(),
        ]))->render();
    }


    public function getEnumName(): string|array
    {
        return Str::studly($this->argument('name'));
    }

    private function getClassNameWithoutNamespace(): array|string
    {
        return class_basename($this->getEnumName());
    }

    public function getDefaultNamespace(): string
    {
        return 'enum';
    }

    protected function getStubName(): string
    {
        return '/enum.stub';
    }
}