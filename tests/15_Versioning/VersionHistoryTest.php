<?php
namespace PHPCR\Tests\Versioning;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

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
        try {
            $vm = self::$staticSharedFixture['session']->getWorkspace()->getVersionManager();
        } catch (\PHPCR\UnSupportedRepositoryOperationException $e) {
            return;
        }
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
        try {
            $this->vm = $this->sharedFixture['session']->getWorkspace()->getVersionManager();
        } catch (\PHPCR\UnSupportedRepositoryOperationException $e) {
            $this->markTestSkipped("Versioning not supported: " . $e->getMessage());
        }
    }

    //TODO: missing methods

    public function testGetVersionHistory() {
        $history = $this->vm->getVersionHistory("/tests_version_base/versioned");
        $versions = $history->getAllVersions();
        $this->assertTraversableImplemented($versions);

        $this->assertEquals(5, count($versions));

        foreach ($versions as $version) {
            $this->assertInstanceOf('PHPCR\Version\VersionInterface', $version);
        }

        $firstVersion = reset($versions);
        $lastVersion = end($versions);
        $currentVersion = $this->vm->getBaseVersion("/tests_version_base/versioned");

        $this->assertSame($currentVersion, $lastVersion);
        $this->assertEquals(0, count($firstVersion->getPredecessors()));
        $this->assertEquals(1, count($firstVersion->getSuccessors()));
        $this->assertEquals(1, count($lastVersion->getPredecessors()));
        $this->assertEquals(0, count($lastVersion->getSuccessors()));
    }

}
