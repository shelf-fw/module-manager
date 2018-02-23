<?php

namespace Shelf\ModuleManager\Service;

use Shelf\ModuleManager\Model\ModuleInterface;

interface ModuleLocalServiceInterface extends ModuleServiceInterface
{
    /**
     * @return array
     */
    public function getLocalVendorFolders();
}