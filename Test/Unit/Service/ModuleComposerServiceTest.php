<?php

namespace Shelf\Config\Test\Unit\Service;

use PHPUnit\Framework\TestCase;
use Shelf\ModuleManager\Model\ModuleCollection;
use Shelf\ModuleManager\Service\ModuleComposerService;
use Shelf\ModuleManager\Test\Unit\Common\HelpersTrait;

/**
 * @runInSeparateProcess
 */
class ModuleComposerServiceTest extends TestCase
{
    use HelpersTrait;

    /**
     * @var ModuleComposerService
     */
    private $moduleComposerService;

    protected function setUp()
    {
        $composerHelperMock = $this->getHelper('Composer');
        $this->moduleComposerService = new ModuleComposerService($composerHelperMock);
    }

    public function testInstanceOfGetCollection()
    {
        $collection = $this->moduleComposerService->getCollection();
        $this->assertInstanceOf(ModuleCollection::class, $collection);
    }

}