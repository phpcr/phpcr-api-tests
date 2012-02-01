<?php
namespace PHPCR\Tests\Versioning;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
* Testing whether node property manipulations work correctly
*
* Covering jcr-2.8.3 spec $15.1
*/
class CreateVersionableNodeTest extends \PHPCR\Test\BaseCase
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

    public function testAddVersionableMixin()
    {
        $this->node->addMixin('mix:versionable');
        $mixins = array();
        foreach ($this->node->getMixinNodeTypes() as $mix) {
            $mixins[] = $mix->getName();
        }

        $this->assertContains('mix:versionable', $mixins, 'Node does not have mix:versionable mixin');
        // For now, the session must be renewed otherwise the node is read from cache and will not have
        // the jcr:isCheckedOut property. This is not the expected behaviour.
        $this->saveAndRenewSession();
        //get the node again from the server
        $this->node = $this->sharedFixture['session']->getNode('/tests_version_base/versionable');
        $this->assertContains('mix:versionable', $mixins, 'Node does not have mix:versionable mixin');
        $this->assertTrue( $this->node->getProperty('jcr:isCheckedOut')->getBoolean(),'jcr:isCheckout is not true');
    }

    public function testCheckinVersion()
    {
        $this->vm->checkout('/tests_version_base/versioned');
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar');
        $this->vm->checkin('/tests_version_base/versioned');
        $history = $this->vm->getVersionHistory('/tests_version_base/versioned');
        $this->assertEquals(2, count($history->getAllVersions()));
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

        $node->setProperty('foo', 'bar2');
        $this->sharedFixture['session']->save();
    }

    public function testNewVersionableNode()
    {
        $this->renewSession(); // need a clean session after exception

        $node = $this->sharedFixture['session']->getNode('/tests_version_base');
        $node = $node->addNode('myversioned');
        $node->setProperty('foo', 'bar');
        $node->addMixin('mix:versionable');

        $this->saveAndRenewSession();
        $this->vm = $this->sharedFixture['session']->getWorkspace()->getVersionmanager();

        $node = $this->sharedFixture['session']->getNode('/tests_version_base/myversioned');
        $this->assertTrue($node->hasProperty('foo'));

        $this->vm->checkin($node->getPath());
        $this->vm->checkout($node->getPath());

        $node->setProperty('foo', 'XXX');

        $this->sharedFixture['session']->save();

        $node = $this->sharedFixture['session']->getNode('/tests_version_base/myversioned');
        $this->assertTrue($node->hasProperty('foo'));
        $this->assertEquals('XXX', $node->getPropertyValue('foo'));
    }
}
