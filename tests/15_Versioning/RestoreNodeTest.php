<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
* Testing whether node property manipulations work correctly
*
* Covering jcr-2.8.3 spec $15.1
*/
class Versioning_15_RestoreNodeTest extends jackalope_baseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('version/base');
    }

    public function setUp()
    {
        parent::setUp();
        $this->vm = $this->sharedFixture['session']->getWorkspace()->getVersionManager();
    }

    public function testRestoreversion() {
        $this->vm->checkout("/tests_version_base/versioned");
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');

        $node->setProperty('foo', 'bar');
        $this->sharedFixture['session']->save();
        $this->vm->checkin("/tests_version_base/versioned");

        $this->vm->checkout("/tests_version_base/versioned");

        $node->setProperty('foo', 'bar2');
        $this->sharedFixture['session']->save();
        $this->vm->checkin("/tests_version_base/versioned");

        $this->vm->checkout("/tests_version_base/versioned");
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');

        $node->setProperty('foo', 'bar3');
        $this->sharedFixture['session']->save();
        $this->vm->checkin("/tests_version_base/versioned");

        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        // Read the OLD value out of the var and fill the cache
        $this->assertEquals($node->getProperty('foo')->getValue(), 'bar3');

        // Restore the 1.0 value aka 'bar'
        $this->vm->restore(true, "1.0", "/tests_version_base/versioned");

        // Read the NEW value and test if the cache has been cleared.
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        $this->assertEquals($node->getProperty('foo')->getValue(), 'bar');

         // Read the NEW value out of the var after the session is renewed and the cache clear
        $this->renewSession();
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        $this->assertEquals($node->getProperty('foo')->getValue(), 'bar');

    }

}