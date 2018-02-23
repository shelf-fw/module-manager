<?php

namespace Shelf\ModuleManager\Helper;

use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Composer as ComposerRealObject;

class Composer
{
    private $_composer;

    /**
     * @var Factory
     */
    private $composerFactory;

    public function __construct(
        Factory $composerFactory
    )
    {
        $this->composerFactory = $composerFactory;
    }

    /**
     * @param  bool  $disablePlugins Whether plugins should not be loaded
     * @return ComposerRealObject
     */
    public function getComposer($disablePlugins = false)
    {
        if (null === $this->_composer) {
            $this->_composer = $this->composerFactory->createComposer(new NullIO(), BP . '/composer.json', $disablePlugins);
        }

        return $this->_composer;
    }
}