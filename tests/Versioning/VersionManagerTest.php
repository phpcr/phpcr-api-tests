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

use PHPCR\Version\VersionManagerInterface;

/**
 * Testing version manager functions.
 *
 * Covering jcr-2.8.3 spec $15.1
 */
class VersionManagerTest extends \PHPCR\Test\BaseCase
{
    /**
     * @var VersionManagerInterface
     */
    private $vm;

    public static function setupBeforeClass($fixtures = '15_Versioning/base')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        parent::setUp();
        $this->renewSession();
        $this->node = $this->session->getNode('/tests_version_base/versionable');
        $this->vm = $this->session->getWorkspace()->getVersionManager();
    }

    public function testCheckinCheckoutVersion()
    {
        $history = $this->vm->getVersionHistory('/tests_version_base/versioned');
        $this->assertInstanceOf('PHPCR\Version\VersionHistoryInterface', $history);
        $this->assertCount(1, $history->getAllVersions());

        $this->vm->checkout('/tests_version_base/versioned');
        $node = $this->session->getNode('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar');
        $this->session->save();

        $this->vm->checkin('/tests_version_base/versioned');
        $this->assertCount(2, $history->getAllVersions());

        $this->renewSession();
        $node = $this->session->getNode('/tests_version_base/versioned');
        $this->assertTrue($node->hasProperty('foo'));
        $this->assertEquals('bar', $node->getPropertyValue('foo'));
    }

    public function testWriteNotCheckedOutVersion()
    {
        $this->vm->checkout('/tests_version_base/versioned');
        $node = $this->session->getNode('/tests_version_base/versioned');

        $node->setProperty('foo', 'bar');
        $this->session->save();
        $this->vm->checkin('/tests_version_base/versioned');

        // all this should have worked, now we try something that should fail
        $this->setExpectedException('PHPCR\Version\VersionException');
        $node->setProperty('foo', 'bar2');
        $this->session->save();
    }

    /**
     * @expectedException \PHPCR\InvalidItemStateException
     */
    public function testCheckinModifiedNode()
    {
        $this->vm->checkout('/tests_version_base/versioned');
        $node = $this->session->getNode('/tests_version_base/versioned');
        $node->setProperty('foo', 'modified');
        $this->vm->checkin('/tests_version_base/versioned');
    }

    public function testCheckpoint()
    {
        $this->vm->checkout('/tests_version_base/versioned');
        $this->vm->checkpoint('/tests_version_base/versioned');

        $node = $this->session->getNode('/tests_version_base/versioned');

        $node->setProperty('foo', 'babar');
        $this->session->save();
        $newNode = $this->vm->checkin('/tests_version_base/versioned');

        $this->assertInstanceOf('\PHPCR\Version\VersionInterface', $newNode);
    }

    public function testCheckinTwice()
    {
        $version = $this->vm->checkin('/tests_version_base/versioned'); // make sure node is checked in

        $history = $this->vm->getVersionHistory('/tests_version_base/versioned');
        $count = count($history->getAllVersions());

        $version2 = $this->vm->checkin('/tests_version_base/versioned'); // this should not create a new version

        $this->assertCount($count, $history->getAllVersions());
        $session = $this->saveAndRenewSession();
        $history = $session->getWorkspace()->getVersionManager()->getVersionHistory('/tests_version_base/versioned');
        $this->assertCount($count, $history->getAllVersions());

        $this->assertSame($version, $version2, 'must be the same version instance');
    }

    public function testGetBaseVersion()
    {
        $version = $this->vm->getBaseVersion('/tests_version_base/versioned');
        $this->assertInstanceOf('PHPCR\\Version\\VersionInterface', $version);
    }

    /**
     * @expectedException \PHPCR\UnsupportedRepositoryOperationException
     */
    public function testGetBaseVersionNonversionable()
    {
        $version = $this->vm->getBaseVersion('/tests_version_base/unversionable');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetBaseVersionNotfound()
    {
        $version = $this->vm->getBaseVersion('/tests_version_base/not_existing');
    }

    public function testGetVersionHistory()
    {
        $nodePath = '/tests_version_base/versioned';
        $history = $this->vm->getVersionHistory($nodePath);
        $this->assertInstanceOf('PHPCR\\Version\\VersionHistoryInterface', $history);
        $this->assertSame($history, $this->vm->getVersionHistory($nodePath));
    }

    /**
     * @expectedException \PHPCR\UnsupportedRepositoryOperationException
     */
    public function testGetVersionHistoryNonversionable()
    {
        $this->vm->getVersionHistory('/tests_version_base/unversionable');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetVersionHistoryNotfound()
    {
        $this->vm->getVersionHistory('/tests_version_base/not_existing');
    }

    /**
     * @depends testCheckinCheckoutVersion
     */
    public function testIsCheckedOut()
    {
        $nodePath = '/tests_version_base/versioned';
        $this->vm->checkin($nodePath);
        $this->assertFalse($this->vm->isCheckedOut($nodePath));
        $this->vm->checkout($nodePath);
        $this->assertTrue($this->vm->isCheckedOut($nodePath));
    }

    /**
     * @expectedException \PHPCR\UnsupportedRepositoryOperationException
     */
    public function testIsCheckedOutNonversionable()
    {
        $this->vm->isCheckedOut('/tests_version_base/unversionable');
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testIsCheckedOutNotExisting()
    {
        $this->vm->isCheckedOut('/tests_version_base/not_existing');
    }

    public function testRestoreByPathAndName()
    {
        $nodePath = '/tests_version_base/versioned';
        $this->vm->checkout($nodePath);
        $node = $this->session->getNode($nodePath);

        $node->setProperty('foo', 'bar');
        $this->session->save();
        $version = $this->vm->checkin($nodePath);

        $this->vm->checkout($nodePath);

        $node->setProperty('foo', 'bar2');
        $this->session->save();
        $this->vm->checkin($nodePath);

        $this->vm->checkout($nodePath);
        $node = $this->session->getNode($nodePath);

        $node->setProperty('foo', 'bar3');
        $this->session->save();
        $this->vm->checkin($nodePath);

        $node = $this->session->getNode($nodePath);
        // Read the OLD value out of the var and fill the cache
        $this->assertEquals('bar3', $node->getProperty('foo')->getValue());

        $history = $this->vm->getVersionHistory($nodePath);

        // Restore the first version aka 'bar'
        $this->vm->restore(true, $version->getName(), $nodePath);

        // Read the NEW value and test if it was reset
        $this->assertEquals('bar', $node->getProperty('foo')->getValue());

        // Read the NEW value out of the var after the session is renewed and the cache clear
        $this->renewSession();
        $this->vm = $this->session->getWorkspace()->getVersionManager();

        $node = $this->session->getNode($nodePath);
        $this->assertEquals('bar', $node->getProperty('foo')->getValue());
    }
    public function testRestoreByVersionObject()
    {
        $nodePath = '/tests_version_base/versioned';
        $this->vm->checkout($nodePath);
        $node = $this->session->getNode($nodePath);

        $node->setProperty('foo', 'bar');
        $this->session->save();
        $version = $this->vm->checkin($nodePath);

        $this->vm->checkout($nodePath);

        $node->setProperty('foo', 'bar2');
        $this->session->save();
        $this->vm->checkin($nodePath);

        $this->vm->checkout($nodePath);
        $node = $this->session->getNode($nodePath);

        $node->setProperty('foo', 'bar3');
        $this->session->save();
        $this->vm->checkin($nodePath);

        $node = $this->session->getNode($nodePath);
        // Read the OLD value out of the var and fill the cache
        $this->assertEquals('bar3', $node->getProperty('foo')->getValue());

        // Restore the first version aka 'bar'
        $this->vm->restore(true, $version);

        // Read the NEW value and test if it was reset
        $this->assertEquals('bar', $node->getProperty('foo')->getValue());

        // Read the NEW value out of the var after the session is renewed and the cache clear
        $this->renewSession();
        $node = $this->session->getNode($nodePath);
        $this->assertEquals('bar', $node->getProperty('foo')->getValue());
    }

    /**
     * @expectedException \PHPCR\InvalidItemStateException
     */
    public function testRestorePendingChanges()
    {
        $nodePath = '/tests_version_base/versioned';
        $this->vm->checkout($nodePath);
        $node = $this->session->getNode($nodePath);

        $node->setProperty('foo', 'bar');
        $this->session->save();
        $version = $this->vm->checkin($nodePath);
        $this->vm->checkout($nodePath);
        $this->vm->checkin($nodePath);

        $node->setProperty('foo', 'bar2');

        $this->vm->restore(true, $version);
    }

    public function testRestoreWithDeletedProperties()
    {
        $nodePath = '/tests_version_base/versioned';
        $version = $this->vm->checkpoint($nodePath);
        $this->vm->checkout($nodePath);

        $node = $this->session->getNode($nodePath);
        $node->getProperty('foo')->remove();

        $this->session->save();

        $this->assertFalse($node->hasProperty('foo'));

        $this->vm->restore(true, $version);
        $node = $this->session->getNode($nodePath);

        $this->assertTrue($node->hasProperty('foo'));
    }

    public function testRestoreWithNewProperties()
    {
        $nodePath = '/tests_version_base/versioned';
        $version = $this->vm->checkpoint($nodePath);
        $this->vm->checkout($nodePath);

        $node = $this->session->getNode($nodePath);
        $node->setProperty('bar', 'value');

        $this->session->save();

        $this->assertTrue($node->hasProperty('bar'));

        $this->vm->restore(true, $version);
        $node = $this->session->getNode($nodePath);

        $this->assertFalse($node->hasProperty('bar'));
    }

    public function testRestoreIsCheckedIn()
    {
        $nodePath = '/tests_version_base/versioned';
        $version = $this->vm->checkpoint($nodePath);

        $this->vm->checkout($nodePath);
        $this->assertTrue($this->vm->isCheckedOut($nodePath));

        $this->vm->restore(true, $version);
        $this->assertFalse($this->vm->isCheckedOut($nodePath));
    }

    public function testRestoreBaseProperties()
    {
        // TODO also check for primary node type once it can be changed

        $nodePath = '/tests_version_base/versioned';
        $version = $this->vm->checkpoint($nodePath);
        $this->vm->checkout($nodePath);

        $node = $this->session->getNode($nodePath);
        $node->addMixin('mix:created');

        $this->session->save();

        $node = $this->session->getNode($nodePath);
        $this->assertContains('mix:created', $node->getPropertyValue('jcr:mixinTypes'));
        $this->assertTrue($node->hasProperty('jcr:created'));

        $this->vm->restore(true, $version);

        $node = $this->session->getNode($nodePath);
        $this->assertEquals(array('mix:versionable'), $node->getPropertyValue('jcr:mixinTypes'));
        $this->assertFalse($node->hasProperty('jcr:created'));
    }

    public function testRestoreWithChildren()
    {
        $nodePath = '/tests_version_base/versioned';
        $this->vm->checkout($nodePath);
        $node = $this->session->getNode($nodePath);
        $childNode1 = $node->addNode('childNode1');
        $childNode1->setProperty('foo', 'child1');
        $childNode2 = $node->addNode('childNode2');
        $childNode2->setProperty('foo', 'child2');
        $childNode3 = $childNode1->addNode('childNode3');

        $this->session->save();
        $version = $this->vm->checkin($nodePath);

        $this->assertCount(2, $node->getNodes());
        $this->assertEquals('child1', $node->getNode('childNode1')->getPropertyValue('foo'));
        $this->assertEquals('child2', $node->getNode('childNode2')->getPropertyValue('foo'));

        $this->assertCount(1, $node->getNode('childNode1')->getNodes());

        $this->vm->checkout($nodePath);

        $childNode1->remove();
        $childNode2->setProperty('foo', 'child1');

        $this->session->save();

        $this->assertCount(1, $node->getNodes());
        $this->assertEquals('child1', $node->getNode('childNode2')->getPropertyValue('foo'));

        $this->vm->restore(true, $version);
        $node = $this->session->getNode($nodePath);

        $this->assertCount(2, $node->getNodes());
        $this->assertEquals('child1', $node->getNode('childNode1')->getPropertyValue('foo'));
        $this->assertEquals('child2', $node->getNode('childNode2')->getPropertyValue('foo'));
        $this->assertCount(1, $node->getNode('childNode1')->getNodes());
    }

    public function testRestoreWithNewChildren()
    {
        $nodePath = '/tests_version_base/versioned';
        $version = $this->vm->checkin($nodePath);
        $this->vm->checkout($nodePath);
        $node = $this->session->getNode($nodePath);
        $node->addNode('newChildNode');
        $this->session->save();

        $node = $this->session->getNode($nodePath);
        $this->assertTrue($node->hasNode('newChildNode'));

        $this->vm->restore(true, $version);
        $node = $this->session->getNode($nodePath);

        $this->assertFalse($node->hasNode('newChildNode'));
    }

    // TODO: test restore with removeExisting false and having an id clash

    // TODO: testRestoreByVersionArray, testRestoreVersionToPath, testRestoreVersionToExistingPath (expect exception)

    /**
     * @expectedException \PHPCR\UnsupportedRepositoryOperationException
     */
    public function testRestoreNonversionablePath()
    {
        $this->vm->restore(true, 'something', '/tests_version_base/unversionable');
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testRestoreNonexistingPath()
    {
        $this->vm->restore(true, 'something', '/tests_version_base/not_existing');
    }
    /**
     * @expectedException \PHPCR\Version\VersionException
     */
    public function testRestoreNonexistingName()
    {
        $this->vm->restore(true, 'not-existing', '/tests_version_base/versioned');
    }
    public function testRestoreNonsenseArguments()
    {
        try {
            $this->vm->restore(true, 'something');
            $this->fail('restoring with version name and no path should throw an exception');
        } catch (\Exception $e) {
            // we expect something to be thrown
        }
        try {
            $this->vm->restore(true, $this);
            $this->fail('restoring with non-version object');
        } catch (\Exception $e) {
            // we expect something to be thrown
        }
    }
    /**
     * @expectedException \PHPCR\Version\VersionException
     */
    public function testRestoreRootVersion()
    {
        $rootVersion = $this->vm->getVersionHistory('/tests_version_base/versioned')->getRootVersion();
        $this->vm->restore(true, $rootVersion);
    }

    // TODO: cancelMerge, merge, doneMerge, createConfiguration, createActivity, setActivity, getActivity, removeActivity, restoreByLabel
}
