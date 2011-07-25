<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
* Testing whether node property manipulations work correctly
*
* Covering jcr-2.8.3 spec $15.1
*/
class Versioning_15_RestoreNodeTest extends phpcr_suite_baseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('15_Versioning/base');
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
        $this->assertEquals('bar3', $node->getProperty('foo')->getValue());

        // Restore the 1.0 value aka 'bar'
        $this->vm->restore(true, "1.0", "/tests_version_base/versioned"); // TODO: is 1.0 implementation specific? should use the VersionInterface object probably

        // Read the NEW value and test if the cache has been cleared.
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        $this->assertEquals('bar', $node->getProperty('foo')->getValue());

         // Read the NEW value out of the var after the session is renewed and the cache clear
        $this->renewSession();
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        $this->assertEquals('bar', $node->getProperty('foo')->getValue());

    }

}
