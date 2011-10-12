<?php
namespace PHPCR\Tests\Writing;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

/**
 * Test pre-emptive validation (§10.10.3.2)
 */
class NodeTypePreemptiveValidationTest extends \PHPCR\Test\BaseCase
{
    // we can use general/base, as we do not actually write, just check if we could

    private $file;
    private $folder;

    public function setUp()
    {
        $ntm = self::$staticSharedFixture['session']->getWorkspace()->getNodeTypeManager();
        $this->file = $ntm->getNodeType('nt:file');
        $this->folder = $ntm->getNodeType('nt:folder');
    }

    public function testCanAddChildNode()
    {
        $this->markTestSkipped('check this out and implement in an implementation first');
        $this->assertTrue($this->file->canAddChildNode('jcr:content'));
        $this->assertFalse($this->file->canAddChildNode('something'));
        $this->assertFalse($this->file->canAddChildNode('jcr:created')); //this is a property

        $this->assertTrue($this->file->canAddChildNode('jcr:content', 'nt:base'));
        $this->assertTrue($this->file->canAddChildNode('jcr:content', 'nt:hierarchyNode'));

        $this->assertTrue($this->folder->canAddChildNode('something', 'nt:file'));
        $this->assertTrue($this->folder->canAddChildNode('something', 'nt:hierarchyNode'));
        $this->assertFalse($this->folder->canAddChildNode('something', 'nt:base'));
        $this->assertFalse($this->folder->canAddChildNode('something', 'jcr:created'));
    }

    /* TODO

        testCanRemoveNode
        testCanRemoveProperty
        testCanSetProperty
    */
}
