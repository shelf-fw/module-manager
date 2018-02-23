<?php

namespace Shelf\ModuleManager\Service;

use Doctrine\Common\Collections\Collection;
use Shelf\ModuleManager\Helper\Composer as ComposerHelper;
use Shelf\ModuleManager\Model\Module;
use Shelf\ModuleManager\Model\ModuleCollection;

class ModuleComposerService
    extends AbstractModuleService
    implements ModuleServiceInterface
{
    /**
     * @var ComposerHelper
     */
    private $composerHelper;

    /**
     * @var array
     */
    private $_items;

    public function __construct(
        ComposerHelper $composerHelper
    )
    {
        $this->composerHelper = $composerHelper;
    }

    /**
     * Collection of Modules
     * @return Collection
     */
    public function getCollection()
    {
        if (null === $this->_items) {
            $this->_loadItems();
        }

        return new ModuleCollection($this->_items);
    }

    /**
     * Load from Composer Packages to Module Instance
     */
    private function _loadItems()
    {
        $composer = $this->composerHelper->getComposer();
        $localRepo = $composer->getRepositoryManager()->getLocalRepository();
        $packages = array_filter($localRepo->getPackages(), function ($package) {
            return $package->getType() == Module::MODULE_COMPOSER_TYPE;
        });

        foreach ($packages as $package) {
            $module = new Module();
            $module
                ->setComposerName($package->getName())
                ->setComposerVersion($package->getVersion())
                ->setInstallationType(Module::INSTALLATION_TYPE_COMPOSER)
                ->setComposerAuthors($package->getNames());

            $this->_items[] = $module;
        }
    }
}