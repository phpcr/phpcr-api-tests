<?php
namespace PHPCR\Tests\Versioning;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Testing whether the version history methods work correctly
 *
 * Covering jcr-2.8.3 spec $15.1
 */
class VersionHistoryTest extends \PHPCR\Test\BaseCase
{
    static public function setupBeforeClass($fixtures = '15_Versioning/base')
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
        $this->vm = $this->sharedFixture['session']->getWorkspace()->getVersionManager();
    }

    //TODO: missing methods

    public function testGetVersionableIdentifier()
    {
        $nodePath = '/tests_version_base/versioned';
        $history = $this->vm->getVersionHistory($nodePath);
        $uuid = $history->getVersionableIdentifier();
        $node = self::$staticSharedFixture['session']->getNode($nodePath);
        $this->assertEquals($node->getIdentifier(), $uuid, 'the versionable identifier must be the uuid of the versioned node');
    }

    public function testGetVersionHistory()
    {
        $nodePath = '/tests_version_base/versioned';
        $history = $this->vm->getVersionHistory($nodePath);
        $this->assertSame($history, $this->vm->getVersionHistory($nodePath));
        $versions = $history->getAllVersions();
        $this->assertTraversableImplemented($versions);

        $this->assertEquals(5, count($versions));

        foreach ($versions as $version) {
            $this->assertInstanceOf('PHPCR\Version\VersionInterface', $version);
        }

        $firstVersion = reset($versions);
        $lastVersion = end($versions);
        $currentVersion = $this->vm->getBaseVersion($nodePath);

        $this->assertSame($currentVersion, $lastVersion);
        $this->assertEquals(0, count($firstVersion->getPredecessors()));
        $this->assertEquals(1, count($firstVersion->getSuccessors()));
        $this->assertEquals(1, count($lastVersion->getPredecessors()));
        $this->assertEquals(0, count($lastVersion->getSuccessors()));
    }

    /**
     * @expectedException \PHPCR\UnsupportedRepositoryOperationException
     */
    public function testGetVersionHistoryNonversionable()
    {
        $this->vm->getVersionHistory("/tests_version_base/unversionable");
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetVersionHistoryNonexisting()
    {
        $this->vm->getVersionHistory("/not-existing-node");
    }
}
