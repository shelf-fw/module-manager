<?php

namespace Shelf\ModuleManager\Model;

interface ModuleInterface
{
    const MODULE_LOCAL_PATH = 'app/code';
    const MODULE_COMPOSER_PATH = '/vendor';
    const INSTALLATION_TYPE_LOCAL = 'local';
    const INSTALLATION_TYPE_COMPOSER = 'composer';
    const MODULE_COMPOSER_TYPE = 'shelf-module';
    const MODULE_COMMAND_PATH = 'Console';

    /**
     * @return string
     */
    public function getVendorName();

    /**
     * @return string
     */
    public function getModuleName();

    /**
     * @return string
     */
    public function getComposerName();

    /**
     * @return string
     */
    public function getComposerAuthors();

    /**
     * @return string
     */
    public function getComposerVersion();

    /**
     * @return string
     */
    public function getInstallationType();

    /**
     * @return string
     */
    public function getLocalPathName();

    /**
     * @return string
     */
    public function getNameSpace();

    /**
     * Reset all object properties
     */
    public function reset();
}