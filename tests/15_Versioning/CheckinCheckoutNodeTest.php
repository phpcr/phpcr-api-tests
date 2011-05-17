<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
* Testing whether node property manipulations work correctly
*
* Covering jcr-2.8.3 spec $15.1
*/
class Versioning_15_CheckinCheckoutNodeTest extends jackalope_baseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('version/base');
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
        $this->AssertEquals(count($history->getAllVersions()), 2);
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

        $node->setProperty('foo', 'bar2');
        $this->sharedFixture['session']->save();

    }
    
}