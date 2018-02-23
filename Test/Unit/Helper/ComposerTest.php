<?php

namespace Shelf\Config\Test\Unit\Helper;

use Composer\Composer;
use PHPUnit\Framework\TestCase;
use Shelf\ModuleManager\Test\Unit\Common\HelpersTrait;

class ComposerTest extends TestCase
{
    use HelpersTrait;

    public function testGetComposer()
    {
        $this->assertInstanceOf(Composer::class, $this->getHelper('Composer')->getComposer());
    }
}