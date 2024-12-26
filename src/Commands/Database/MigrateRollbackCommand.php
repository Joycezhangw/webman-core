<?php

namespace Landao\WebmanCore\Commands\Database;

use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use support\Db;
use Webman\Config;

class MigrateRollbackCommand extends Command
{
    protected static $defaultName = 'landao:migrate-rollback';
    protected static $defaultDescription = '回滚上一次或多次的数据库迁移';
    protected ?InputInterface $input = null;

    protected function configure()
    {
        $this->setName(static::$defaultName)
            ->setDescription(static::$defaultDescription)
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, '迁移文件的路径')
            ->addOption('realpath', null, InputOption::VALUE_NONE, '使用绝对路径')
            ->addOption('pretend', null, InputOption::VALUE_NONE, '仅输出将要执行的 SQL')
            ->addOption('step', null, InputOption::VALUE_OPTIONAL, '要回滚的迁移数量', 1)
            ->addOption('all', null, InputOption::VALUE_NONE, '回滚所有迁移');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;

        $migrator = $this->getMigrator();
        $path = $this->getMigrationPath($input);
        $this->rollback($migrator, $path, $output);

        return Command::SUCCESS;
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

    protected function rollback(Migrator $migrator, string $path, OutputInterface $output)
    {
        $migrator->setOutput($output);

        if (!$migrator->repositoryExists()) {
            $output->writeln('<error>迁移表不存在</error>');
            return;
        }

        $options = [
            'pretend' => $this->input->getOption('pretend'),
            'step' => (int)$this->input->getOption('step')
        ];

        if ($this->input->getOption('all')) {
            $migrator->reset([$path], $options);
            $output->writeln('<info>所有迁移已回滚</info>');
            return;
        }

        $migrator->rollback([$path], $options);

        if ($options['step'] > 1) {
            $output->writeln("<info>已回滚 {$options['step']} 次迁移</info>");
        } else {
            $output->writeln('<info>已回滚最后一次迁移</info>');
        }
    }
}