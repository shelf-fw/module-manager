<?php

namespace Shelf\ModuleManager\Service;

use Doctrine\Common\Collections\Collection;
use Shelf\ModuleManager\Model\Module;
use Shelf\ModuleManager\Model\ModuleCollection;
use Shelf\ModuleManager\Model\ModuleInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ModuleLocalService
    extends AbstractModuleService
    implements ModuleServiceInterface, ModuleLocalServiceInterface
{
    /**
     * @var array
     */
    private $_items;

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
     * Load from Composer Local Module Settings to Module Instance
     */
    private function _loadItems()
    {
        $localModuleComposerSettings = $this->getLocalModulesComposerSettings();

        foreach ($localModuleComposerSettings as $composerConfig) {
            $module = new Module();
            $module
                ->setComposerName($composerConfig['name'])
                ->setInstallationType(Module::INSTALLATION_TYPE_LOCAL)
                ->setComposerVersion(
                    isset($composerConfig['version']) ? $composerConfig['version'] : ''
                );
            if (isset($composerConfig['authors'])) {
                $module->setComposerAuthors($composerConfig['authors']);
            }

            $this->_items[] = $module;
        }
    }

    /**
     * Get all local Modules Composer Settings (app/code)
     * @return array
     */
    private function getLocalModulesComposerSettings()
    {
        if (! is_dir(BP . DIRECTORY_SEPARATOR . Module::MODULE_LOCAL_PATH)) {
            return [];
        }

        $finder = new Finder();
        $localModulesComposerSettings = array_map(function ($composerFile) {
            $composerSettingsArray = json_decode(file_get_contents($composerFile), true);
            $composerSettingsArray['installation_type'] = Module::INSTALLATION_TYPE_LOCAL;
            return $composerSettingsArray;
        },
            iterator_to_array(
                $finder
                    ->name('composer.json')
                    ->in(BP . DIRECTORY_SEPARATOR . Module::MODULE_LOCAL_PATH)
                    ->files(), false));

        return $localModulesComposerSettings;
    }

    /**
     * @return array
     */
    public function getLocalVendorFolders()
    {
        $finder = new Finder();

        return array_map(function (SplFileInfo $dir) {
            return $dir->getFilename();
        },iterator_to_array(
            $finder
                ->in(BP . DIRECTORY_SEPARATOR . Module::MODULE_LOCAL_PATH)
                ->depth('< 1'),
            false
        ));
    }
}