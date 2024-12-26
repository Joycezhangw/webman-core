<?php

namespace Landao\WebmanCore\Commands\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Landao\WebmanCore\Database\Seeder;

class SeederCommand extends Command
{
    protected static $defaultName = 'landao:seeder';
    protected static $defaultDescription = '运行数据填充';

    protected function configure()
    {
        $this->setName(static::$defaultName)
            ->setDescription(static::$defaultDescription)
            ->addOption('class', null, InputOption::VALUE_OPTIONAL, '要运行的填充器类');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->runSeeder($input, $output);
            $output->writeln('<info>数据填充成功!</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>数据填充失败: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }

    protected function runSeeder(InputInterface $input, OutputInterface $output)
    {
        $class = $input->getOption('class') ?? 'DatabaseSeeder';
        $class = "\\database\\seeders\\{$class}";

        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Seeder [{$class}] 不存在。");
        }

        $seeder = new $class();

        if ($seeder instanceof Seeder) {
            $seeder->run();
        }
    }
}