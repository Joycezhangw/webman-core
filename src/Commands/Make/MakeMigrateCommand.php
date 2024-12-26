<?php

namespace Landao\WebmanCore\Commands\Make;

use Illuminate\Support\Str;
use Landao\WebmanCore\Commands\Support\GenerateConfigReader;
use Landao\WebmanCore\Commands\Support\Migrations\NameParser;
use Landao\WebmanCore\Commands\Support\Migrations\SchemaParser;
use Landao\WebmanCore\Commands\Support\Stub;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeMigrateCommand extends GeneratorCommand
{

    protected static $defaultName = 'landao:make-migrate';

    protected static $defaultDescription = 'Make a new migration file';

    protected function configure()
    {
        $this->setName(static::$defaultName)->setDescription(static::$defaultDescription);
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the migration')
//            ->addArgument('plugin', InputArgument::OPTIONAL, 'The name of plugin will be created.')
            ->addOption('fields', null, InputOption::VALUE_OPTIONAL, 'The specified fields table.')
            ->addOption('plain', null, InputOption::VALUE_NONE, 'Create plain migration.');
    }


    protected function getDestinationFilePath(): string
    {
        $generatorPath = GenerateConfigReader::read('migration')->getPath() ?? 'database/migrations';
        return $generatorPath . '/' . $this->getFileName() . '.php';
    }

    protected function getTemplateContents(): string
    {
        $parser = new NameParser($this->argument('name'));
        if ($parser->isCreate()) {
            return Stub::create('/migration/create.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields' => $this->getSchemaParser()->render()
            ]);
        } elseif ($parser->isAdd()) {
            return Stub::create('/migration/add.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields_up' => $this->getSchemaParser()->up(),
                'fields_down' => $this->getSchemaParser()->down(),
            ]);
        } elseif ($parser->isDelete()) {
            return Stub::create('/migration/delete.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields_down' => $this->getSchemaParser()->up(),
                'fields_up' => $this->getSchemaParser()->down(),
            ]);
        } elseif ($parser->isDrop()) {
            return Stub::create('/migration/drop.stub', [
                'class' => $this->getClass(),
                'table' => $parser->getTableName(),
                'fields' => $this->getSchemaParser()->render(),
            ]);
        }
        return Stub::create('/migration/plain.stub', [
            'class' => $this->getClass(),
        ]);
    }

    public function getSchemaParser()
    {
        return new SchemaParser($this->option('fields'));
    }

    private function getFileName()
    {
        return date('Y_m_d_His_') . $this->argument('name');
    }

    private function getClassName()
    {
        return Str::studly($this->argument('name'));
    }

    public function getClass()
    {
        return $this->getClassName();
    }


}