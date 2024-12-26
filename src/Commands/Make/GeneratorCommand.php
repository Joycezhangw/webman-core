<?php

namespace Landao\WebmanCore\Commands\Make;

use Landao\WebmanCore\Commands\Traits\PathNamespace;
use Landao\WebmanCore\Exceptions\FileAlreadyExistException;
use Landao\WebmanCore\Generators\FileGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 * 命令基类
 *
 *
 * @author https://github.com/nWidart/laravel-modules
 * @package Landao\WebmanCore\Commands\Make
 */
abstract class GeneratorCommand extends Command
{
    use PathNamespace;

    protected $argumentName = '';

    abstract protected function getTemplateContents();

    abstract protected function getDestinationFilePath();


    public function hasOption($name)
    {
        return $this->input->hasOption($name);
    }

    /**
     * Get the value of a command option.
     *
     * @param string|null $key
     * @return string|array|bool|null
     */
    public function option($key = null)
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Get all of the options passed to the command.
     *
     * @return array
     */
    public function options()
    {
        return $this->option();
    }


    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get all of the arguments passed to the command.
     *
     * @return array
     */
    public function arguments()
    {
        return $this->argument();
    }

    private ?InputInterface $input = null;

    protected function getInput(): InputInterface
    {
        if ($this->input === null) {
            throw new \RuntimeException('Input not initialized');
        }
        return $this->input;
    }

    protected function getOutput(): OutputInterface
    {
        if ($this->output === null) {
            throw new \RuntimeException('Output not initialized');
        }
        return $this->output;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output; // 保存 output 实例

        $path = str_replace('\\', '/', $this->getDestinationFilePath());

        // 确保目录存在
        if (!is_dir($dir = dirname($path))) {
            mkdir($dir, 0777, true);
        }

        $contents = $this->getTemplateContents();

        try {
            // 生成文件
            $overwriteFile = $this->hasOption('force') ? $this->option('force') : false;
            (new FileGenerator($path, $contents))
                ->withFileOverwrite($overwriteFile)
                ->generate();

            $output->writeln("<info>{$this->getName()}:</info> {$path}");
        } catch (FileAlreadyExistException $e) {
            $output->writeln("<error>File : {$path} already exists.</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function getClass()
    {
        return class_basename($this->argument($this->argumentName));
    }

    public function getDefaultNamespace(): string
    {
        return '';
    }

    public function getClassNamespace($plugin)
    {
        $path_namespace = $this->path_namespace(str_replace($this->getClass(), '', $this->argument($this->argumentName)));
        return $this->plugin_namespace($plugin, $this->getDefaultNamespace() . ($path_namespace ? '\\' . $path_namespace : ''));
    }


    /**
     * 执行命令并返回执行结果的状态码。
     *
     * 该方法负责查找并执行给定的命令，处理命令执行过程中的异常，并输出错误信息。
     *
     * @param string $command 要执行的命令名称
     * @param array $arguments 命令参数数组，默认为空数组
     * @return int 命令执行结果的状态码（成功时返回0，失败时返回非0值）
     */
    public function call(string $command, array $arguments = []): int
    {
        try {
            // 查找并初始化命令对象
            $commandInstance = $this->getApplication()->find($command);
            $arguments['command'] =$commandInstance->getName();
            $input = new ArrayInput($arguments);

            // 执行命令并返回状态码
            return $commandInstance->run($input, $this->getOutput());
        } catch (\Throwable $th) {
            // 捕获异常并输出错误信息
            $output = $this->getOutput();
            $output->writeln("<error>Error executing command {$command}: {$th->getMessage()}</error>");

            // 返回命令执行失败的状态码
            return Command::FAILURE;
        }
    }

}