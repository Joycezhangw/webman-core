<?php

namespace Landao\WebmanCore\Commands\Make;

use Illuminate\Support\Str;
use Landao\WebmanCore\Commands\Support\GenerateConfigReader;
use Landao\WebmanCore\Commands\Support\Stub;
use Landao\WebmanCore\Commands\Traits\PluginCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeModelCommand extends GeneratorCommand
{
    use PluginCommandTrait;

    protected $argumentName = 'name';

    protected static $defaultName = 'landao:make-model';

    protected static $defaultDescription = 'Create a new Model class for the specified plugin.';

    protected function configure()
    {
        $this->setName(static::$defaultName)->setDescription(static::$defaultDescription);
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the model class..')
            ->addOption('plugin', null, InputOption::VALUE_REQUIRED, 'The name of plugin will be used.')
            ->addOption('multi-app', null, InputOption::VALUE_REQUIRED, 'Create model classes in multiple applications')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Create the class even if the enum already exists');
    }


    public function getDestinationFilePath(): string
    {
        $pluginName = $this->getPluginName();
        $path = $this->getpluginPath($pluginName);
        $multiApp = $this->option('multi-app');
        $filePath = GenerateConfigReader::read('model', $multiApp)->getPath() ?? 'app/model';
        return $path . $filePath . '/' . $this->getRepositoryName() . 'Model.php';
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


    public function getRepositoryName(): string|array
    {
        return Str::studly($this->argument('name'));
    }

    private function getClassNameWithoutNamespace(): array|string
    {
        return class_basename($this->getRepositoryName());
    }

    public function getDefaultNamespace(): string
    {
        return 'model';
    }

    protected function getStubName(): string
    {
        return '/model.stub';
    }
}