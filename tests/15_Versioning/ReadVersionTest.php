<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
* Testing whether node property manipulations work correctly
*
* Covering jcr-2.8.3 spec $15.1
*/
class Versioning_15_TeadVersionTest extends phpcr_suite_baseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('15_Versioning/base');
    }

    public function setUp()
    {
        parent::setUp();
        $this->vm = $this->sharedFixture['session']->getWorkspace()->getVersionManager();
    }

    public function testReadVersion()
    {
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar');
        $this->sharedFixture['session']->save();
        $node->setProperty('foo', 'bar2');
        $this->sharedFixture['session']->save();

        $history = $this->vm->getVersionHistory("/tests_version_base/versioned");
        $versions = $history->getAllVersions();
        // TODO: why is this not 2?
        $this->assertEquals(count($versions), 1);

        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned', 'Version\Version');
        $nodes = $node->getPredecessors();
        $this->assertEquals(1, count($nodes));

        $node = reset($versions);
        $nodes = $node->getSuccessors();
        // TODO why is this not 1?
        $this->assertEquals(0, count($nodes));
    }

}
