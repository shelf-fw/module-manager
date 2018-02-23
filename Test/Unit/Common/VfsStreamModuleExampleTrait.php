<?php

namespace Shelf\ModuleManager\Test\Unit\Common;

use org\bovigo\vfs\vfsStream;

trait VfsStreamModuleExampleTrait
{
    private $rootDir;

    private $appDir;

    private $codeDir;

    private $vendorDir;

    private $moduleDir;

    private $composerFile;

    protected function createFakeModule()
    {
        $this->rootDir = VfsStream::setup();
        $this->appDir = VfsStream::newDirectory('app')->at($this->rootDir);
        $this->codeDir = VfsStream::newDirectory('code')->at($this->appDir);
        $this->vendorDir = VfsStream::newDirectory('Vendor')->at($this->codeDir);
        $this->moduleDir = VfsStream::newDirectory('Module')->at($this->vendorDir);
        $this->composerFile = VfsStream::newFile('composer.json')->at($this->moduleDir);

        $this->composerFile->setContent(json_encode([
            'name' => 'vendor/module',
            'type' => 'shelf-module',
            'authors' => [
                [
                    'name' => 'John Due',
                    'email' => 'jonh@due.com'
                ]
            ]
        ], JSON_PRETTY_PRINT));

    }

}