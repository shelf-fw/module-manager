<?php

namespace Shelf\ModuleManager\Model;

class Module implements ModuleInterface
{
    /**
     * @var string
     */
    private $vendorName;

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var string
     */
    private $composerName;

    /**
     * @var string
     */
    private $composerAuthors;

    /**
     * @var string
     */
    private $composerVersion;

    /**
     * @var string
     */
    private $installationType;

    /**
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Reset all properties
     */
    public function reset()
    {
        array_map(function ($property) {
            $this->{$property} = NULL;
        }, get_object_vars($this));

    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        $composerName = $this->getComposerName();

        if ($composerName) {
            $composerNameArray = explode('/', $composerName);
            $this->moduleName = $this->normalizeName($composerNameArray[1]);
        }

        return $this->moduleName;
    }

    /**
     * @return string
     */
    public function getVendorName()
    {
        $composerName = $this->getComposerName();

        if ($composerName) {
            $composerNameArray = explode('/', $composerName);
            $this->vendorName = $this->normalizeName($composerNameArray[0]);
        }

        return $this->vendorName;
    }

    /**
     * @param string $composerName
     * @return Module
     */
    public function setComposerName($composerName)
    {
        $this->composerName = $composerName;
        return $this;
    }

    /**
     * @return string
     */
    public function getComposerName()
    {
        return $this->composerName;
    }

    /**
     * @param array $composerAuthors
     * @return Module
     */
    public function setComposerAuthors($composerAuthors)
    {
        $this->composerAuthors = $composerAuthors;
        return $this;
    }

    /**
     * @return array
     */
    public function getComposerAuthors()
    {
        return $this->composerAuthors;
    }

    /**
     * @param string $composerVersion
     * @return Module
     */
    public function setComposerVersion($composerVersion)
    {
        $this->composerVersion = $composerVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getComposerVersion()
    {
        return $this->composerVersion ? $this->composerVersion : 'dev-master';
    }

    /**
     * @param string $installationType
     * @return Module
     */
    public function setInstallationType($installationType)
    {
        $this->installationType = $installationType;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstallationType()
    {
        return $this->installationType;
    }

    /**
     * @param string $name
     * @return string
     */
    private function normalizeName($name)
    {
        return str_replace(' ', '', ucwords(str_replace(array('_', '-'), ' ', $name)));

    }

    /**
     * @return string
     */
    public function getLocalPathName()
    {
        return self::MODULE_LOCAL_PATH . '/' . $this->getVendorName() . '/' . $this->getModuleName();
    }

    /**
     * @return string
     */
    public function getNameSpace()
    {
        return $this->getVendorName() . '\\' . $this->getModuleName() . '\\';
    }

}