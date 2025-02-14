<?php

namespace Landao\WebmanCore\Commands\Database;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use support\Db;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\ConnectionResolver;

class MigrateCommand extends Command
{
    protected static $defaultName = 'landao:migrate';
    protected static $defaultDescription = 'Run database migration.';
    protected ?InputInterface $input = null;

    protected function configure()
    {
        $this->setName(static::$defaultName)
            ->setDescription(static::$defaultDescription)
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Path to migrate files.')
            ->addOption('realpath', null, InputOption::VALUE_NONE, 'Use absolute path')
            ->addOption('pretend', null, InputOption::VALUE_NONE, 'Only output what will be executed SQL.')
            ->addOption('seed', null, InputOption::VALUE_NONE, 'Do you want to run data filling.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->input = $input;

            // 获取迁移器实例
            $migrator = $this->getMigrator();

            // 获取迁移文件路径
            $path = $this->getMigrationPath($input);

            // 执行迁移
            $this->runMigration($migrator, $path, $output);

            // 如果指定了填充选项
            if ($input->getOption('seed')) {
                $this->runSeeder($output);
            }
            $output->writeln('<info>数据迁移成功!</info>');
            return Command::SUCCESS;
        }catch (\Throwable $e){
            $output->writeln('<error>数据迁移失败!</error>');
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Command::FAILURE;
        }

    }

    protected function getMigrator(): Migrator
    {
        // 创建连接解析器
        $resolver = new ConnectionResolver([
            'default' => Db::connection()
        ]);
        $resolver->setDefaultConnection('default');

        // 创建迁移仓库
        $repository = new DatabaseMigrationRepository($resolver, 'migrations');

        // 创建迁移器
        return new Migrator($repository, $resolver, new \Illuminate\Filesystem\Filesystem());
    }

    protected function getMigrationPath(InputInterface $input): string
    {
        if ($input->getOption('path')) {
            return $input->getOption('realpath')
                ? $input->getOption('path')
                : base_path($input->getOption('path'));
        }

        return base_path('database/migrations');
    }

    protected function runMigration(Migrator $migrator, string $path, OutputInterface $output)
    {
        $migrator->setOutput($output);

        if (!$migrator->repositoryExists()) {
            $migrator->getRepository()->createRepository();
        }

        $migrator->run([$path], [
            'pretend' => $this->input->getOption('pretend')
        ]);
    }

    protected function runSeeder(OutputInterface $output)
    {
        $this->getApplication()->find('landao:seeder')->run(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            $output
        );
    }
}