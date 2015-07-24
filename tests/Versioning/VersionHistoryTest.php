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

use PHPCR\Util\PathHelper;
use PHPCR\Version\VersionHistoryInterface;
use PHPCR\Version\VersionManagerInterface;

/**
 * Testing whether the version history methods work correctly.
 *
 * Covering jcr-2.8.3 spec $15.1
 */
class VersionHistoryTest extends \PHPCR\Test\BaseCase
{
    /**
     * @var VersionManagerInterface
     */
    private $vm;

    /**
     * @var VersionHistoryInterface
     */
    private $history;

    public static function setupBeforeClass($fixtures = '15_Versioning/base')
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
        self::$staticSharedFixture['session'] = self::$loader->getSession(); //reset the session, should not be needed if save would correctly invalidate and refresh $node
    }

    public function setUp()
    {
        parent::setUp();
        $this->vm = $this->session->getWorkspace()->getVersionManager();
        $this->history = $this->vm->getVersionHistory('/tests_version_base/versioned');
        $this->assertInstanceOf('PHPCR\\Version\\VersionHistoryInterface', $this->history);
    }

    public function testGetAllLinearFrozenNodes()
    {
        $frozenNodes = $this->history->getAllLinearFrozenNodes();
        $this->assertTraversableImplemented($frozenNodes);

        $this->assertCount(5, $frozenNodes);

        $lastNode = null;
        foreach ($frozenNodes as $name => $node) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $node);
            $this->assertInternalType('string', $name);
            $lastNode = $node;
        }

        $currentNode = $this->vm->getBaseVersion('/tests_version_base/versioned')->getFrozenNode();

        $this->assertSame($currentNode, $lastNode);
    }
    public function testGetAllFrozenNodes()
    {
        // TODO: have non linear version history
        $frozenNodes = $this->history->getAllFrozenNodes();
        $this->assertTraversableImplemented($frozenNodes);

        $this->assertCount(5, $frozenNodes);

        $lastNode = null;
        foreach ($frozenNodes as $name => $node) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $node);
            $this->assertInternalType('string', $name);
            $lastNode = $node;
        }

        $currentNode = $this->vm->getBaseVersion('/tests_version_base/versioned')->getFrozenNode();

        $this->assertSame($currentNode, $lastNode);
    }
    /**
     * @group x
     */
    public function testGetAllLinearVersions()
    {
        $versions = $this->history->getAllLinearVersions();
        $this->assertTraversableImplemented($versions);

        $this->assertCount(5, $versions);

        $firstVersion = $versions->current();
        $lastVersion = null;
        foreach ($versions as $name => $version) {
            $this->assertInstanceOf('PHPCR\Version\VersionInterface', $version);
            $this->assertEquals($version->getName(), $name);
            $lastVersion = $version;
        }

        $currentVersion = $this->vm->getBaseVersion('/tests_version_base/versioned');

        $this->assertSame($currentVersion, $lastVersion);
        $this->assertCount(0, $firstVersion->getPredecessors());
        $this->assertCount(1, $firstVersion->getSuccessors());
        $this->assertCount(1, $lastVersion->getPredecessors());
        $this->assertCount(0, $lastVersion->getSuccessors());
    }

    public function testGetAllVersions()
    {
        // TODO: have non linear version history
        $versions = $this->history->getAllVersions();
        $this->assertTraversableImplemented($versions);

        $this->assertCount(5, $versions);

        $firstVersion = $versions->current();
        $lastVersion = null;
        foreach ($versions as $name => $version) {
            $this->assertInstanceOf('PHPCR\Version\VersionInterface', $version);
            $this->assertEquals($version->getName(), $name);
            $lastVersion = $version;
        }

        $currentVersion = $this->vm->getBaseVersion('/tests_version_base/versioned');

        $this->assertSame($currentVersion, $lastVersion);
        $this->assertCount(0, $firstVersion->getPredecessors());
        $this->assertCount(1, $firstVersion->getSuccessors());
        $this->assertCount(1, $lastVersion->getPredecessors());
        $this->assertCount(0, $lastVersion->getSuccessors());
    }

    /**
     * Check version history (allVersions), add more versions, then check the history updates correctly.
     */
    public function testMixingCreateAndGetAllVersions()
    {
        $vm = $this->session->getWorkspace()->getVersionManager();
        $baseNode = $this->session->getNode('/tests_version_base');

        $node = $baseNode->addNode('versioned_all', 'nt:unstructured');
        $node->addMixin('mix:versionable');
        $node->setProperty('foo', 'bar');
        $this->session->save();

        $history = $vm->getVersionHistory('/tests_version_base/versioned_all');
        $this->assertCount(1, $history->getAllVersions());
        foreach ($history->getAllVersions() as $name => $version) {
            $this->assertInstanceOf('PHPCR\Version\VersionInterface', $version);
            $this->assertEquals($version->getName(), $name);
        }

        $vm->checkpoint('/tests_version_base/versioned_all');
        $node->setProperty('foo', 'bar2');
        $this->session->save();
        $this->assertCount(2, $history->getAllVersions());

        $vm->checkin('/tests_version_base/versioned_all');
        $this->assertCount(3, $history->getAllVersions());

        $finalVersions = $history->getAllVersions();
        $firstVersion = $finalVersions->current();
        $lastVersion = null;
        foreach ($finalVersions as $name => $version) {
            $this->assertInstanceOf('PHPCR\Version\VersionInterface', $version);
            $this->assertEquals($version->getName(), $name);
            $lastVersion = $version;
        }

        $currentVersion = $this->vm->getBaseVersion('/tests_version_base/versioned_all');

        $this->assertSame($currentVersion, $lastVersion);
        $this->assertCount(0, $firstVersion->getPredecessors());
        $this->assertCount(1, $firstVersion->getSuccessors());
        $this->assertCount(1, $lastVersion->getPredecessors());
        $this->assertCount(0, $lastVersion->getSuccessors());
    }

    /**
     * Check version history (allLinearVersions), add more versions, then check the history updates correctly.
     */
    public function testMixingCreateAndGetAllLinearVersions()
    {
        $vm = $this->session->getWorkspace()->getVersionManager();
        $baseNode = $this->session->getNode('/tests_version_base');

        $node = $baseNode->addNode('versioned_all_linear', 'nt:unstructured');
        $node->addMixin('mix:versionable');
        $node->setProperty('foo', 'bar');
        $this->session->save();

        $history = $vm->getVersionHistory('/tests_version_base/versioned_all_linear');
        $this->assertCount(1, $history->getAllLinearVersions());

        $vm->checkpoint('/tests_version_base/versioned_all_linear');
        $node->setProperty('foo', 'bar2');
        $this->session->save();
        $this->assertCount(2, $history->getAllLinearVersions());

        $vm->checkin('/tests_version_base/versioned_all_linear');
        $this->assertCount(3, $history->getAllLinearVersions());

        $finalVersions = $history->getAllLinearVersions();
        $firstVersion = $finalVersions->current();
        $lastVersion = null;
        foreach ($finalVersions as $name => $version) {
            $this->assertInstanceOf('PHPCR\Version\VersionInterface', $version);
            $this->assertEquals($version->getName(), $name);
            $lastVersion = $version;
        }

        $currentVersion = $this->vm->getBaseVersion('/tests_version_base/versioned_all_linear');

        $this->assertSame($currentVersion, $lastVersion);
        $this->assertCount(0, $firstVersion->getPredecessors());
        $this->assertCount(1, $firstVersion->getSuccessors());
        $this->assertCount(1, $lastVersion->getPredecessors());
        $this->assertCount(0, $lastVersion->getSuccessors());
    }

    public function testGetRootVersion()
    {
        $rootVersion = $this->history->getRootVersion();
        $this->assertInstanceOf('PHPCR\\Version\\VersionInterface', $rootVersion);
        $this->assertEquals($this->history->getPath(), PathHelper::getParentPath($rootVersion->getPath()));
    }

    public function testGetVersionableIdentifier()
    {
        $uuid = $this->history->getVersionableIdentifier();
        $node = self::$staticSharedFixture['session']->getNode('/tests_version_base/versioned');
        $this->assertEquals($node->getIdentifier(), $uuid, 'the versionable identifier must be the uuid of the versioned node');
    }

    /**
     * Create two versions then delete the first version.
     *
     * Note that you can not use $version->remove() although version is a node.
     */
    public function testDeleteVersion()
    {
        $nodePath = '/tests_version_base/versioned';
        $this->session->getNode($nodePath); // just to make sure this does not confuse anything

        $version = $this->vm->checkpoint($nodePath);
        $this->vm->checkpoint($nodePath); // create another version, the last version can not be removed
        $history = $this->vm->getVersionHistory($nodePath);

        // The version exists before removal
        $versionPath = $version->getPath();
        $versionName = $version->getName();

        $history->getAllVersions(); // load all versions so they land in cache
        $frozen = $history->getVersion($versionName)->getFrozenNode(); // also have the frozen node in cache
        $frozenPath = $frozen->getPath();

        $this->assertTrue($this->session->itemExists($versionPath));
        $this->assertTrue($this->versionExists($history, $versionName));

        // Remove the version
        $history->removeVersion($versionName);

        // The version is gone after removal
        $this->assertFalse($this->session->itemExists($versionPath));
        $this->assertFalse($this->session->itemExists($frozenPath));

        $this->assertFalse($this->versionExists($history, $versionName));
    }

    /**
     * Check the last version cannot be removed.
     *
     * @expectedException \PHPCR\ReferentialIntegrityException
     */
    public function testDeleteLatestVersion()
    {
        $version = $this->vm->checkpoint('/tests_version_base/versioned');
        $history = $this->vm->getVersionHistory('/tests_version_base/versioned');
        $history->removeVersion($version->getName());
    }

    /**
     * Try removing an unexisting version.
     *
     * @expectedException \PHPCR\Version\VersionException
     */
    public function testDeleteUnexistingVersion()
    {
        $this->vm->checkpoint('/tests_version_base/versioned');
        $history = $this->vm->getVersionHistory('/tests_version_base/versioned');
        $history->removeVersion('unexisting');
    }

    /**
     * Check if a version node with the given name exists in the version history.
     *
     * @param VersionHistoryInterface $history     The version history node
     * @param string                  $versionName The name of the version to search for
     *
     * @return bool
     */
    protected function versionExists($history, $versionName)
    {
        foreach ($history->getAllVersions() as $version) {
            if ($version->getName() === $versionName) {
                return true;
            }
        }

        return false;
    }

    // TODO: missing addVersionlabel, getVersionByLabel, getVersionLabels, hasVersionLabel, removeVersionLabel
}
