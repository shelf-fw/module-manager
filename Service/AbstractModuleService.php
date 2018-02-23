<?php

namespace Shelf\ModuleManager\Service;

abstract class AbstractModuleService implements ModuleServiceInterface
{
    /**
     * Collection of Modules
     * @return Collection
     */
    abstract function getCollection();

    /**
     * Load module by composer name ex: loadByComposerName('vendor/name')
     * @param string $composerName
     * @return Module
     */
    public function loadByComposerName($composerName)
    {
        return $this->getCollection()->filter(function ($module) use ($composerName) {
            /** @var Module $module */
            return $module->getComposerName() === $composerName;
        })->first();
    }

}