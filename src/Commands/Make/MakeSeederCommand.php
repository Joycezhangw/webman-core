<?php

namespace Landao\WebmanCore\Commands\Make;

use Illuminate\Support\Str;
use Landao\WebmanCore\Commands\Support\GenerateConfigReader;
use Landao\WebmanCore\Commands\Support\Stub;
use Symfony\Component\Console\Input\InputArgument;

class MakeSeederCommand extends GeneratorCommand
{
    protected static $defaultName = 'landao:make-seeder';

    protected static $defaultDescription = 'Make a new seeder file';

    protected function configure()
    {
        $this->setName(static::$defaultName)->setDescription(static::$defaultDescription);
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the seeder');
//            ->addArgument('plugin', InputArgument::OPTIONAL, 'The name of plugin will be created.')
    }


    protected function getDestinationFilePath(): mixed
    {
        $generatorPath = GenerateConfigReader::read('seeder')->getPath() ?? 'database/seeders';
        return $generatorPath.'/'.$this->getSeederName().'.php';
    }

    protected function getTemplateContents(): mixed
    {
        return (new Stub('/seeder.stub', [
            'NAME' => $this->getSeederName(),
            'NAMESPACE' => 'database\seeders'

        ]))->render();
    }

    /**
     * Get the seeder name.
     */
    private function getSeederName(): string
    {
        $string = $this->argument('name');
//        $string .= $this->option('master') ? 'Database' : '';
        $suffix = 'Seeder';

        if (strpos($string, $suffix) === false) {
            $string .= $suffix;
        }
        return Str::studly($string);
    }

}