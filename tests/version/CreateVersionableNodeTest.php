<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
* Testing whether node property manipulations work correctly
*
* Covering jcr-2.8.3 spec $15.1
*/
class Version_CreateVersionableNodeTest extends jackalope_baseCase
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
    
    public function testAddVersionableMixin()
    {
        $this->node->addMixin("mix:versionable");
        $mixins = array();
        foreach ($this->node->getMixinNodeTypes() as $mix) {
            $mixins[] = $mix->getName();
        }
        
        
        $this->assertContains("mix:versionable", $mixins, "Node doesn't have mix:versionable mixin");
        $this->sharedFixture['session']->save();
        //get the node again from the server
        $this->node = $this->sharedFixture['session']->getNode('/tests_version_base/versionable');
        $this->assertContains("mix:versionable", $mixins, "Node doesn't have mix:versionable mixin");
        $this->assertTrue( $this->node->getProperty("jcr:isCheckedOut")->getBoolean(),"jcr:isCheckout is not true");
    }
    
    public function testCheckinVersion() {
        $ws = $this->sharedFixture['session']->getWorkspace();
        $vm = $ws->getVersionManager();
        $vm->checkout("/tests_version_base/versioned");
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar');
        $vm->checkin("/tests_version_base/versioned");
        $history = $vm->getVersionHistory("/tests_version_base/versioned");
        $this->AssertEquals(count($history->getAllVersions()), 2);
    }

    /**
     * @expectedException PHPCR\Version\VersionException
     */
    public function testWriteNotCheckedOutVersion() {
        $ws = $this->sharedFixture['session']->getWorkspace();
        $vm = $ws->getVersionManager();
        $vm->checkout("/tests_version_base/versioned");
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');

        $node->setProperty('foo', 'bar');
        $this->sharedFixture['session']->save();
        $newNode = $vm->checkin("/tests_version_base/versioned");

        $node->setProperty('foo', 'bar2');
        $this->sharedFixture['session']->save();

    }
    
}