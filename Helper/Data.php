<?php

namespace Shelf\ModuleManager\Helper;

class Data
{
    /**
     * @param string $name
     * @return string
     */
    public function normalizeCamelcase($name)
    {
        $name = trim($name);
        $name = ucwords($name);
        $name = str_replace(array('-', '_', ' '), '', $name);

        return $name;
    }

    /**
     * @param string $vendorName
     * @param string $moduleName
     * @return string
     */
    public function getComposerNameByVendorModuleName($vendorName, $moduleName)
    {
        $vendorNameArray = preg_split('/(?=[A-Z])/', $vendorName);
        $moduleNameArray = preg_split('/(?=[A-Z])/', $moduleName);

        unset($vendorNameArray[0]);
        unset($moduleNameArray[0]);

        $vendorName = strtolower(implode('-', $vendorNameArray));
        $moduleName = strtolower(implode('-', $moduleNameArray));

        return $vendorName . '/' . $moduleName;
    }
}