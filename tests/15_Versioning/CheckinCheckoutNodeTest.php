<?php
namespace PHPCR\Tests\Versioning;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
* Testing whether node property manipulations work correctly
*
* Covering jcr-2.8.3 spec $15.1
*/
class CheckinCheckoutNodeTest extends \PHPCR\Test\BaseCase
{
    static public function setupBeforeClass($fixtures = '15_Versioning/base')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getNode('/tests_version_base/versionable');
        $this->vm = $this->sharedFixture['session']->getWorkspace()->getVersionManager();
    }

    public function testCheckinVersion()
    {
        $history = $this->vm->getVersionHistory('/tests_version_base/versioned');
        $this->assertEquals(1, count($history->getAllVersions()));

        $this->vm->checkout('/tests_version_base/versioned');
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar');
        $this->sharedFixture['session']->save();

        $this->vm->checkin('/tests_version_base/versioned');
        $this->assertEquals(2, count($history->getAllVersions()));

        $this->renewSession();
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        $this->assertTrue($node->hasProperty('foo'));
        $this->assertEquals('bar', $node->getPropertyValue('foo'));
    }

    /**
     * @expectedException PHPCR\Version\VersionException
     */
    public function testWriteNotCheckedOutVersion()
    {
        $this->vm->checkout('/tests_version_base/versioned');
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');

        $node->setProperty('foo', 'bar');
        $this->sharedFixture['session']->save();
        $this->vm->checkin('/tests_version_base/versioned');

        //try to save a checked in node
        $node->setProperty('foo', 'bar2');
        $this->sharedFixture['session']->save();

    }

    public function testCheckpoint()
    {
        $this->vm->checkout('/tests_version_base/versioned');
        $this->vm->checkpoint('/tests_version_base/versioned');

        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');

        $node->setProperty('foo', 'babar');
        $this->sharedFixture['session']->save();
        $newNode = $this->vm->checkin('/tests_version_base/versioned');

        $this->assertInstanceOf('\PHPCR\Version\VersionInterface', $newNode);
    }

    public function testCheckinTwice()
    {
        $version = $this->vm->checkin('/tests_version_base/versioned'); // make sure node is checked in

        $history = $this->vm->getVersionHistory('/tests_version_base/versioned');
        $count = count($history->getAllVersions());

        $version2 = $this->vm->checkin('/tests_version_base/versioned'); // this should not create a new version

        $this->assertEquals($count, count($history->getAllVersions()));
        $session = $this->saveAndRenewSession();
        $history = $session->getWorkspace()->getVersionManager()->getVersionHistory('/tests_version_base/versioned');
        $this->assertEquals($count, count($history->getAllVersions()));

        $this->assertSame($version, $version2, 'must be the same version instance');
    }

}
