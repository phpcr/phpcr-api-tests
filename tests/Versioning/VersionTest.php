<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Versioning;

use PHPCR\NodeInterface;
use PHPCR\NodeType\ConstraintViolationException;
use PHPCR\RepositoryException;
use PHPCR\Test\BaseCase;
use PHPCR\Version\VersionInterface;
use PHPCR\Version\VersionManagerInterface;

/**
 * Testing whether getting predecessor / successor works correctly.
 *
 * Covering jcr-283 spec $15.1
 */
class VersionTest extends BaseCase
{
    /**
     * @var VersionManagerInterface
     */
    private $vm;

    /**
     * @var VersionInterface
     */
    private $version;

    public static function setupBeforeClass($fixtures = '15_Versioning/base'): void
    {
        parent::setupBeforeClass($fixtures);

        //have some versions
        $vm = self::$staticSharedFixture['session']->getWorkspace()->getVersionManager();

        $node = self::$staticSharedFixture['session']->getNode('/tests_version_base/versioned');
        $vm->checkpoint('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar');
        self::$staticSharedFixture['session']->save();
        $vm->checkpoint('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar2');
        self::$staticSharedFixture['session']->save();
        $vm->checkpoint('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar3');
        self::$staticSharedFixture['session']->save();
        $vm->checkin('/tests_version_base/versioned');
        self::$staticSharedFixture['session'] = self::$loader->getSession(); //reset the session
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->vm = $this->session->getWorkspace()->getVersionManager();

        $this->version = $this->vm->getBaseVersion('/tests_version_base/versioned');

        $this->assertInstanceOf(VersionInterface::class, $this->version);
    }

    public function testGetContainingHistory()
    {
        $this->assertSame($this->vm->getVersionHistory('/tests_version_base/versioned'), $this->version->getContainingHistory());
    }

    public function testGetCreated()
    {
        $date = $this->version->getCreated();
        $diff = time() - $date->getTimestamp();
        $this->assertTrue($diff < 60, 'creation date of the version we created in setupBeforeClass should be within the last few seconds');
    }

    public function testGetFrozenNode()
    {
        $frozen = $this->version->getFrozenNode();
        $this->assertTrue($frozen->hasProperty('foo'));
        $this->assertEquals('bar3', $frozen->getPropertyValue('foo'));

        $predecessors = $this->version->getPredecessors();
        $this->assertIsArray($predecessors);
        $firstVersion = reset($predecessors);
        $this->assertInstanceOf(VersionInterface::class, $firstVersion);
        $frozen2 = $firstVersion->getFrozenNode();
        $this->assertInstanceOf(NodeInterface::class, $firstVersion);
        /* @var $frozen2 NodeInterface */
        $this->assertTrue($frozen2->hasProperty('foo'));
        $this->assertEquals('bar2', $frozen2->getPropertyValue('foo'));
    }

    /**
     * @depends testGetFrozenNode
     */
    public function testFrozenNode()
    {
        $this->expectException(ConstraintViolationException::class);

        $frozen = $this->version->getFrozenNode();
        $frozen->setProperty('foo', 'should not work');
        self::$staticSharedFixture['session']->save();
    }

    public function testGetLinearPredecessorSuccessor()
    {
        $pred = $this->version->getLinearPredecessor();
        $this->assertInstanceOf(VersionInterface::class, $pred);
        $succ = $pred->getLinearSuccessor();
        $this->assertSame($this->version, $succ);
    }

    public function testGetLinearPredecessorNull()
    {
        $rootVersion = $this->vm->getVersionHistory('/tests_version_base/versioned')->getRootVersion();
        // base version is at end of chain
        $this->assertNull($rootVersion->getLinearPredecessor());
    }

    public function testGetLinearSuccessorNull()
    {
        // base version is at end of chain
        $this->assertNull($this->version->getLinearSuccessor());
    }

    public function testGetPredecessors()
    {
        $versions = $this->version->getPredecessors();
        $this->assertCount(1, $versions);
        $pred = $versions[0];
        $this->assertInstanceOf(VersionInterface::class, $pred);
        $versions = $pred->getSuccessors();
        $this->assertCount(1, $versions, 'expected a successor of our predecessor');
        $this->assertSame($this->version, $versions[0]);
    }

    public function testGetSuccessors()
    {
        $versions = $this->version->getSuccessors();
        $this->assertCount(0, $versions);
    }

    /**
     * Check $version->remove() is not possible. This must go through VersionHistory::remove.
     */
    public function testNodeRemoveOnVersion()
    {
        $this->expectException(RepositoryException::class);

        $version = $this->vm->checkpoint('/tests_version_base/versioned');
        $version->remove();
    }
}
