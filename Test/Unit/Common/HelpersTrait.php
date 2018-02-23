<?php

namespace Shelf\ModuleManager\Test\Unit\Common;

use Composer\Composer;
use Composer\Factory;
use Composer\Package\PackageInterface;
use Composer\Repository\RepositoryInterface;
use Composer\Repository\RepositoryManager;
use Shelf\ModuleManager\Helper\Composer as ComposerHelper;
use Shelf\ModuleManager\Model\Module;

trait HelpersTrait
{
    /**
     * @var ComposerHelper
     */
    private $_composerHelper;

    /**
     * @param string $helperName
     * @return mixed
     */
    public function getHelper($helperName)
    {
        return call_user_func(array($this, 'get' . $helperName . 'Helper'));
    }

    /**
     * @return ComposerHelper
     */
    public function getComposerHelper()
    {
        if (null === $this->_composerHelper) {
            /** @var \PHPUnit_Framework_MockObject_MockObject $composerFactoryMock */
            $composerFactoryMock = $this->createMock(Factory::class);

            $packageMock = $this->createMock(PackageInterface::class);
            $packageMock
                ->method('getType')
                ->willReturn(Module::MODULE_COMPOSER_TYPE);

            /** @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject $writableRepository */
            $repository = $this->createMock(RepositoryInterface::class);
            $repository
                ->method('getPackages')
                ->willReturn([
                    $packageMock
                ]);

            /** @var RepositoryManager|\PHPUnit_Framework_MockObject_MockObject $repositoryManagerMock */
            $repositoryManagerMock = $this->createMock(RepositoryManager::class);
            $repositoryManagerMock
                ->method('getLocalRepository')
                ->willReturn($repository);

            /** @var \PHPUnit_Framework_MockObject_MockObject $composerMock */
            $composerMock = $this->createMock(Composer::class);
            $composerMock->method('getRepositoryManager')
                ->willReturn($repositoryManagerMock);
            $composerFactoryMock
                ->method('createComposer')
                ->willReturn($composerMock);
            $this->_composerHelper = new ComposerHelper($composerFactoryMock);
        }

        return $this->_composerHelper;
    }

}