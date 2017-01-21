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

use PHPCR\RepositoryFactoryInterface;
use PHPCR\RepositoryInterface;
use PHPCR\Test\BaseCase;

class RepositoryFactoryTest extends BaseCase
{
    public static function setupBeforeClass($fixtures = false)
    {
        // Don't care about fixtures
        parent::setupBeforeClass($fixtures);
    }

    // 4.1 Repository
    public function testRepositoryFactory()
    {
        $class = self::$loader->getRepositoryFactoryClass();
        /** @var $factory RepositoryFactoryInterface */
        $factory = new $class();
        $repo = $factory->getRepository(self::$loader->getRepositoryFactoryParameters());
        $this->assertInstanceOf(RepositoryInterface::class, $repo);
    }
}
