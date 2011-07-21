<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
* Testing whether node property manipulations work correctly
*
* Covering jcr-2.8.3 spec $15.1
*/
class Versioning_15_CheckinCheckoutNodeTest extends phpcr_suite_baseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('15_Versioning/base');
    }

    public function setUp()
    {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getNode('/tests_version_base/versionable');
        try {
            $this->vm = $this->sharedFixture['session']->getWorkspace()->getVersionManager();
        } catch (\PHPCR\UnSupportedRepositoryOperationException $e) {
            $this->markTestSkipped("Versioning not supported: " . $e->getMessage());
        }
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
