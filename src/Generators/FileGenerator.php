<?php

namespace Landao\WebmanCore\Generators;

use Illuminate\Filesystem\Filesystem;
use Landao\WebmanCore\Exceptions\FileAlreadyExistException;

class FileGenerator
{
    protected $path;

    protected $contents;

    protected $filesystem;

    private $overwriteFile;

    public function __construct($path, $contents, $filesystem = null)
    {
        $this->path = $path;
        $this->contents = $contents;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function setContents($contents)
    {
        $this->contents = $contents;
        return $this;
    }

    public function getFilesystem()
    {
        return $this->filesystem;
    }

    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function withFileOverwrite(bool $overwrite): FileGenerator
    {
        $this->overwriteFile = $overwrite;
        return $this;
    }

    public function generate()
    {
        $path = $this->getPath();
        if (!$this->filesystem->exists($path)) {
            return $this->filesystem->put($path, $this->getContents());
        }
        if ($this->overwriteFile === true) {
            return $this->filesystem->put($path, $this->getContents());
        }
        throw new FileAlreadyExistException('File already exists!');
    }
}