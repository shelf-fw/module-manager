<?php

namespace Shelf\ModuleManager\Service;


use Doctrine\Common\Collections\Collection;
use Shelf\ModuleManager\Model\Module;

interface ModuleServiceInterface
{
    /**
     * Collection of Modules
     * @return Collection
     */
    public function getCollection();

    /**
     * Load module by composer name ex: loadByComposerName('vendor/name')
     * @param string $composerName
     * @return Module
     */
    public function loadByComposerName($composerName);
}