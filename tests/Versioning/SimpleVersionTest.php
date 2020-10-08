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
 * Testing whether simple versioning works.
 *
 * Covering jcr-283 spec $15.1
 */
class SimpleVersionTest extends BaseCase
{
    /**
     * @var VersionManagerInterface
     */
    private $vm;

    /**
     * @var VersionInterface
     */
    private $simpleVersioned;

    public static function setupBeforeClass($fixtures = '15_Versioning/base'): void
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->vm = $this->session->getWorkspace()->getVersionManager();

        $this->simpleVersioned = $this->vm->getBaseVersion('/tests_version_base/simpleVersioned');

        $this->assertInstanceOf(VersionInterface::class, $this->simpleVersioned);
    }

    public function testGetContainingHistory()
    {
        $this->assertSame($this->vm->getVersionHistory('/tests_version_base/simpleVersioned'), $this->simpleVersioned->getContainingHistory());
    }

    public function testGetCreated()
    {
        $date = $this->simpleVersioned->getCreated();
        $diff = time() - $date->getTimestamp();
        $this->assertTrue($diff < 60, 'creation date of the version we created in setupBeforeClass should be within the last few seconds');
    }

    public function testGetFrozenNode()
    {
        $frozen = $this->simpleVersioned->getFrozenNode();
        $this->assertTrue($frozen->hasProperty('foo'));
        $this->assertEquals('bar3', $frozen->getPropertyValue('foo'));

        $predecessors = $this->simpleVersioned->getPredecessors();
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

        $frozen = $this->simpleVersioned->getFrozenNode();
        $frozen->setProperty('foo', 'should not work');
        self::$staticSharedFixture['session']->save();
    }

    public function testGetLinearPredecessorSuccessor()
    {
        $pred = $this->simpleVersioned->getLinearPredecessor();
        $this->assertInstanceOf(VersionInterface::class, $pred);
        $succ = $pred->getLinearSuccessor();
        $this->assertSame($this->simpleVersioned, $succ);
    }

    public function testGetLinearPredecessorNull()
    {
        $rootVersion = $this->vm->getVersionHistory('/tests_version_base/simpleVersioned')->getRootVersion();
        // base version is at end of chain
        $this->assertNull($rootVersion->getLinearPredecessor());
    }

    public function testGetLinearSuccessorNull()
    {
        // base version is at end of chain
        $this->assertNull($this->simpleVersioned->getLinearSuccessor());
    }

    public function testGetPredecessors()
    {
        $versions = $this->simpleVersioned->getPredecessors();
        $this->assertCount(1, $versions);
        $pred = $versions[0];
        $this->assertInstanceOf(VersionInterface::class, $pred);
        $versions = $pred->getSuccessors();
        $this->assertCount(1, $versions, 'expected a successor of our predecessor');
        $this->assertSame($this->simpleVersioned, $versions[0]);
    }

    public function testGetSuccessors()
    {
        $versions = $this->simpleVersioned->getSuccessors();
        $this->assertCount(0, $versions);
    }

    /**
     * Check $version->remove() is not possible. This must go through VersionHistory::remove.
     */
    public function testNodeRemoveOnVersion()
    {
        $this->expectException(RepositoryException::class);

        $version = $this->vm->checkpoint('/tests_version_base/simpleVersioned');
        $version->remove();
    }
}
