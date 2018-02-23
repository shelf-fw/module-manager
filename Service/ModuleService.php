<?php

namespace Shelf\ModuleManager\Service;

use Doctrine\Common\Collections\Collection;
use Shelf\ModuleManager\Model\ModuleCollection;

class ModuleService
    extends AbstractModuleService
    implements ModuleServiceInterface
{
    /**
     * @var Collection
     */
    private $_collection;

    /**
     * @var ModuleComposerService
     */
    private $moduleComposerService;

    /**
     * @var ModuleLocalService
     */
    private $moduleLocalService;

    public function __construct(
        ModuleComposerService $moduleComposerService,
        ModuleLocalService $moduleLocalService
    )
    {
        $this->moduleComposerService = $moduleComposerService;
        $this->moduleLocalService = $moduleLocalService;
    }

    /**
     * Collection of Modules
     * @return Collection
     */
    public function getCollection()
    {
        if (null === $this->_collection) {
            $this->_collection = $this->buildCollection();
        }

        return $this->_collection;
    }

    /**
     * @return Collection
     */
    private function buildCollection()
    {
        $composerCollection = $this->moduleComposerService->getCollection()->toArray();
        $localCollection = $this->moduleLocalService->getCollection()->toArray();
        return new ModuleCollection(array_merge($composerCollection, $localCollection));
    }

    /**
     * @return ModuleComposerService
     */
    public function getModuleComposerService()
    {
        return $this->moduleComposerService;
    }

    /**
     * @return ModuleLocalService
     */
    public function getModuleLocalService()
    {
        return $this->moduleLocalService;
    }
}