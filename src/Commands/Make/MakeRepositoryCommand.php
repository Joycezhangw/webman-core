<?php

namespace Landao\WebmanCore\Commands\Make;

use Illuminate\Support\Str;
use Landao\WebmanCore\Commands\Support\Artisan;
use Landao\WebmanCore\Commands\Support\GenerateConfigReader;
use Landao\WebmanCore\Commands\Support\Stub;
use Landao\WebmanCore\Commands\Traits\PluginCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Webman\Console\Command;

class MakeRepositoryCommand extends GeneratorCommand
{
    use PluginCommandTrait;

    protected $argumentName = 'name';

    protected static $defaultName = 'landao:make-repo';

    protected static $defaultDescription = 'Create a new Repositories class for the specified plugin.';

    protected function configure()
    {
        $this->setName(static::$defaultName)->setDescription(static::$defaultDescription);
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the repository class..')
            ->addArgument('plugin', InputArgument::OPTIONAL, 'The name of plugin will be used.')
            ->addOption('multi-app', null, InputOption::VALUE_REQUIRED, 'Create repository classes in multiple applications')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Create the class even if the enum already exists');
    }


    public function getDestinationFilePath(): string
    {
        $pluginName = $this->getPluginName();
        $path = $this->getpluginPath($pluginName);
        $multiApp = $this->option('multi-app');
        $filePath = GenerateConfigReader::read('repository', $multiApp)->getPath() ?? 'app/repository';
        return $path . $filePath . '/' . $this->getRepositoryName() . 'Repo.php';
    }

    protected function getTemplateContents(): string
    {
        $pluginName = $this->getPluginName();
        $module = $pluginName !== 'app' ? $pluginName : 'app';
        $namespace = $this->getClassNamespace($module);
        $className = $this->getClassNameWithoutNamespace();

        $arguments = [
            'name' => $this->argument('name'),
            'plugin' => $this->argument('plugin'),
        ];

        // 只有当 multi-app 有值时才添加到参数中
        if ($this->option('multi-app')) {
            $arguments['--multi-app'] = $this->option('multi-app');
        }

        // 执行 make:model 命令生成对应的模型
        $this->call('landao:make-model', $arguments);
        return (new Stub($this->getStubName(), [
            'CLASS_NAMESPACE' => $namespace,
            'CLASS' => $className,
            'MODEL_CLASS_NAMESPACE' => Str::replace('repositories', 'models', $namespace) . '\\' . $className . 'Model',
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
        return 'repositories';
    }

    protected function getStubName(): string
    {
        return '/repository.stub';
    }
}