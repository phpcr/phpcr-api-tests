<?php
namespace PHPCR\Tests\Versioning;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

/**
* Testing whether node property manipulations work correctly
*
* Covering jcr-2.8.3 spec $15.1
*/
class CheckinCheckoutNodeTest extends \PHPCR\Test\BaseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass('15_Versioning/base');
    }

    public function setUp()
    {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getNode('/tests_version_base/versionable');
        $this->vm = $this->sharedFixture['session']->getWorkspace()->getVersionManager();
    }

    public function testCheckinVersion() {
        $this->vm->checkout("/tests_version_base/versioned");
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar');
        $this->vm->checkin("/tests_version_base/versioned");
        $history = $this->vm->getVersionHistory("/tests_version_base/versioned");
        $this->assertEquals(2, count($history->getAllVersions()));
    }

    /**
     * @expectedException PHPCR\Version\VersionException
     */
    public function testWriteNotCheckedOutVersion() {
        $this->vm->checkout("/tests_version_base/versioned");
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');

        $node->setProperty('foo', 'bar');
        $this->sharedFixture['session']->save();
        $newNode = $this->vm->checkin("/tests_version_base/versioned");

        //try to save a checked in node
        $node->setProperty('foo', 'bar2');
        $this->sharedFixture['session']->save();

    }

    public function testCheckpoint() {
        $this->vm->checkout("/tests_version_base/versioned");
        $this->vm->checkpoint("/tests_version_base/versioned");

        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');

        $node->setProperty('foo', 'babar');
        $this->sharedFixture['session']->save();
        $newNode = $this->vm->checkin("/tests_version_base/versioned");

        $this->assertInstanceOf('\PHPCR\Version\VersionInterface', $newNode);
    }

}
