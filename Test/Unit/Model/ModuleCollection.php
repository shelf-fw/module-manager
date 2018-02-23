<?php

namespace Shelf\ModuleManager\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Shelf\Config\Composer\ComposerHelper;
use Shelf\Dev\Tests\Unit\PHPUnitCommonTrait;
use Shelf\ModuleManager\Model\Module;
use Shelf\ModuleManager\Model\ModuleCollection;
use Shelf\ModuleManager\Test\Unit\Common\VfsStreamModuleExampleTrait;

class ModuleCollectionTest extends TestCase
{
    use VfsStreamModuleExampleTrait;
    use PHPUnitCommonTrait;

    /**
     * @var ModuleCollection
     */
    private $moduleCollection;

    protected function setUp()
    {
        $this->createFakeModule();

        /** @var ComposerHelper $composerHelperMock */
        $composerHelperMock = $this->getMockBuilder(ComposerHelper::class)->getMock();
        $composerHelperMock
            ->method('getInstalledModules')
            ->willReturn([
                json_decode(file_get_contents($this->composerFile->url()), true)
            ]);

        $this->moduleCollection = new ModuleCollection(
            $composerHelperMock,
            $this->rootDir->url()
        );
    }

    public function testGetLocalModulesComposerSettings()
    {
        $localModulesComposerSettings = $this->callMethod($this->moduleCollection, 'getLocalModulesComposerSettings');
        $this->assertArrayHasKey('name', $localModulesComposerSettings[0]);
        $this->assertEquals(Module::INSTALLATION_TYPE_LOCAL, $localModulesComposerSettings[0]['installation_type']);
    }

    public function testGetComposerModuleComposerSettings()
    {
        $composerModulesComposerSettings = $this->callMethod($this->moduleCollection, 'getComposerModulesComposerSettings');
        $this->assertArrayHasKey('name', $composerModulesComposerSettings[0]);
        $this->assertEquals(Module::INSTALLATION_TYPE_COMPOSER, $composerModulesComposerSettings[0]['installation_type']);
    }

    public function testCollectionWithModuleObjects()
    {
        $collection = $this->moduleCollection->getValues();
        $this->assertInstanceOf(Module::class, $collection[0]);
    }
}