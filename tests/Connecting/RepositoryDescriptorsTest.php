<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Connecting;

use PHPCR\Test\BaseCase;

class RepositoryDescriptorsTest extends BaseCase
{
    public static function setupBeforeClass($fixtures = false)
    {
        // Don't care about fixtures
        parent::setupBeforeClass($fixtures);
    }

    // Those constants need to be defined in the bootstrap file
    protected $expectedDescriptors = [
        SPEC_VERSION_DESC,
        SPEC_NAME_DESC,
        REP_VENDOR_DESC,
        REP_VENDOR_URL_DESC,
        REP_NAME_DESC,
        REP_VERSION_DESC,
        OPTION_TRANSACTIONS_SUPPORTED,
        OPTION_VERSIONING_SUPPORTED,
        OPTION_OBSERVATION_SUPPORTED,
        OPTION_LOCKING_SUPPORTED,
        // TODO: complete with the list from jcr 2
    ];

    // 24.2 Repository Descriptors
    public function testDescriptorKeys()
    {
        $rep = self::$loader->getRepository();
        $keys = $rep->getDescriptorKeys();
        $this->assertInternalType('array', $keys);
        $this->assertNotEmpty($keys);
        foreach ($this->expectedDescriptors as $descriptor) {
            $this->assertContains($descriptor, $keys);
        }
    }

    //TODO: Check if the values are compatible to the spec
    public function testDescription()
    {
        $rep = self::$loader->getRepository();
        foreach ($this->expectedDescriptors as $descriptor) {
            $str = $rep->getDescriptor($descriptor);
            $this->assertTrue(is_string($str) || is_bool($str));
            if (!is_bool($str)) {
                $this->assertNotEmpty($str);
            }
        }
    }

    public function testIsStandardDescriptor()
    {
        $rep = self::$loader->getRepository();
        foreach ($this->expectedDescriptors as $descriptor) {
            $this->assertTrue($rep->isStandardDescriptor($descriptor), "Not considered $descriptor a standard descriptor");
        }
        // there is probably no obligation for an implementation to have any non-standard descriptors
    }
}
