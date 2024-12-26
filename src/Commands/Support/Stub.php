<?php

namespace Landao\WebmanCore\Commands\Support;

class Stub
{
    /**
     * stub 路径
     * @var string
     */
    protected $path;

    /**
     * 根文件路径
     * @var null
     */
    protected static $basePath = null;

    /**
     * 需要替换字符
     * @var array
     */
    protected $replaces = [];

    public function __construct($path, array $replaces = [])
    {
        $this->path = $path;
        $this->replaces = $replaces;
    }

    /**
     * 创建新的 instance
     * @param $path
     * @param array $replaces
     * @return static
     */
    public static function create($path, array $replaces = [])
    {
        return new static($path, $replaces);
    }

    /**
     * 设置 stub 路径
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * 获取 stub 路径
     * @return string
     */
    public function getPath()
    {
        return __DIR__ . '/../../Commands/stubs' . $this->path;
    }

    /**
     * 设置根路径,在注册服务的时候，要配置，此项目不用
     * @param string $path
     * @return void
     */
    public static function setBasePath($path)
    {
        static::$basePath = $path;
    }

    /**
     * 获取根路径
     * @return mixed|string
     */
    public function getBasePath()
    {
        return static::$basePath;
    }

    /**
     * 获取 stub 内容
     * @return mixed｜string
     */
    public function getContents()
    {
        $contents = file_get_contents($this->getPath());
        foreach ($this->replaces as $search => $replace) {
            $contents = str_replace('$' . strtoupper($search) . '$', $replace, $contents);
        }
        return $contents;
    }

    /**
     * 渲染
     * @return mixed｜string
     */
    public function render()
    {
        return $this->getContents();
    }

    /**
     * 将生成的实例保存到指定文件
     * @param string $path
     * @param string $filename
     * @return bool
     */
    public function saveTo($path, $filename)
    {
        return file_put_contents($path . '/', $filename, $this->getContents());
    }

    /**
     * Set replacements array.
     * @param array $replaces
     * @return $this
     */
    public function replace(array $replaces = [])
    {
        $this->replaces = $replaces;
        return $this;
    }

    public function getReplaces()
    {
        return $this->replaces;
    }

    public function __toString()
    {
        return $this->render();
    }
}