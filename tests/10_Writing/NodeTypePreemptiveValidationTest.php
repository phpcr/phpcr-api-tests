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
        parent::setUp();
        $ntm = self::$staticSharedFixture['session']->getWorkspace()->getNodeTypeManager();
        $this->file = $ntm->getNodeType('nt:file');
        $this->folder = $ntm->getNodeType('nt:folder');
    }

    public function testCanAddChildNode()
    {
        $this->assertFalse($this->file->canAddChildNode('jcr:content'));
        $this->assertFalse($this->file->canAddChildNode('something'));
        $this->assertFalse($this->file->canAddChildNode('jcr:created')); //this is a property
        // do we have a built-in type with a default child node type?

        $this->assertTrue($this->file->canAddChildNode('jcr:content', 'nt:base'));
        $this->assertTrue($this->file->canAddChildNode('jcr:content', 'nt:hierarchyNode'));

        $this->assertTrue($this->folder->canAddChildNode('something', 'nt:file'));
        $this->assertTrue($this->folder->canAddChildNode('something', 'nt:hierarchyNode'));
        $this->assertFalse($this->folder->canAddChildNode('something', 'nt:base'));
        $this->assertFalse($this->folder->canAddChildNode('something', 'jcr:created')); // invalid type
    }

    public function testCanRemoveNode()
    {
        $this->assertFalse($this->file->canRemoveNode('jcr:content'));
        $this->assertTrue($this->file->canRemoveNode('notdefined')); // only returns false for required children, not for forbidden ones
        $this->assertTrue($this->file->canRemoveNode('jcr:created')); // this is a property, not a child
    }

    public function testCanSetProperty()
    {
        $this->assertTrue($this->file->canSetProperty('mix:created', new \DateTime()));
        $this->assertTrue($this->file->canSetProperty('mix:created', '2011-10-13'));
        $this->assertTrue($this->file->canSetProperty('mix:created', 32388)); // timestamp
        $this->assertTrue($this->resource->canSetProperty('jcr:mimeType', 'text/plain'));
    }

    public function testCanSetPropertyWrongType()
    {
        $this->assertFalse($this->file->canSetProperty('mix:created', 'notadate'));
        $this->assertFalse($this->file->canSetProperty('mix:created', true));
    }

    public function testCanRemoveProperty()
    {
        $this->assertTrue($this->file->canRemoveProperty('notdefined'));
        $this->assertTrue($this->file->canRemoveProperty('jcr:content')); // this is a child, not a property...
        $this->assertTrue($this->mimeType->canRemoveProperty('jcr:mimeType'));
        $this->assertFalse($this->file->canRemoveProperty('jcr:created'));
    }

}
