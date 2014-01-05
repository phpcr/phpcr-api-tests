<?php
namespace PHPCR\Tests\Writing;

use PHPCR\NodeType\NodeTypeInterface;
use PHPCR\NodeType\NodeTypeManagerInterface;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Test pre-emptive validation (§10.10.3.2)
 */
class NodeTypePreemptiveValidationTest extends \PHPCR\Test\BaseCase
{
    // we can use general/base, as we do not actually write, just check if we could

    /**
     * @var NodeTypeInterface
     */
    private $file;
    /**
     * @var NodeTypeInterface
     */
    private $folder;
    /**
     * @var NodeTypeInterface
     */
    private $resource;
    /**
     * @var NodeTypeManagerInterface
     */
    private $ntm;

    public function setUp()
    {
        parent::setUp();
        $this->ntm = $this->session->getWorkspace()->getNodeTypeManager();
        $this->file = $this->ntm->getNodeType('nt:file');
        $this->folder = $this->ntm->getNodeType('nt:folder');
        $this->resource = $this->ntm->getNodeType('nt:resource');
    }

    public function testCanAddChildNode()
    {
        $this->assertFalse($this->file->canAddChildNode('jcr:content'));
        $this->assertFalse($this->file->canAddChildNode('something'));
        $this->assertFalse($this->file->canAddChildNode('jcr:created')); //this is a property
        // do we have a built-in type with a default child node type?

        $this->assertTrue($this->file->canAddChildNode('jcr:content', 'nt:folder')); // any type is allowed as content
        $this->assertFalse($this->file->canAddChildNode('jcr:content', 'nt:base')); // abstract type

        $this->assertTrue($this->folder->canAddChildNode('something', 'nt:file'));
        $this->assertTrue($this->folder->canAddChildNode('something', 'nt:folder'));
        $this->assertFalse($this->folder->canAddChildNode('something', 'nt:base'));
        $this->assertFalse($this->folder->canAddChildNode('something', 'mix:created'));
        $this->assertFalse($this->folder->canAddChildNode('something', 'jcr:created'));
        $this->assertFalse($this->folder->canAddChildNode('something', 'notexistingnodetype'));
    }

    public function testCanRemoveNode()
    {
        $this->assertFalse($this->file->canRemoveNode('jcr:content'));
        $this->assertTrue($this->file->canRemoveNode('notdefined')); // only returns false for required children, not for forbidden ones
        $this->assertTrue($this->file->canRemoveNode('jcr:created')); // this is a property, not a child
    }

    public function testCanSetProperty()
    {
        $this->assertTrue($this->file->canSetProperty('jcr:created', new \DateTime()));
        $this->assertTrue($this->file->canSetProperty('jcr:created', '2011-10-13'));
        $this->assertTrue($this->file->canSetProperty('jcr:created', 32388)); // timestamp
        $this->assertTrue($this->resource->canSetProperty('jcr:mimeType', 'text/plain'));
    }

    public function testCanSetPropertyWrongType()
    {
        $this->assertFalse($this->file->canSetProperty('jcr:created', 'notadate'));
        $this->assertFalse($this->file->canSetProperty('jcr:created', true));
        $this->assertFalse($this->file->canSetProperty('jcr:created', $this)); // not a valid type for value
        $this->assertFalse($this->file->canSetProperty('mix:created', new \DateTime())); // this is a type
        $this->assertFalse($this->file->canSetProperty('nt:file', 'nosense')); // this is a type
    }

    /**
     * requires the implementation to support unstructured nodes
     */
    public function testCanAddChildNodeWildcard()
    {
        $undef = $this->ntm->getNodeType('nt:unstructured');
        $this->assertTrue($undef->canAddChildNode('something', 'nt:file'));
        $this->assertFalse($undef->canAddChildNode('something', 'notexistingnodetype'));
    }

    /**
     * requires the implementation to support unstructured nodes
     */
    public function testCanSetPropertyWildcard()
    {
        $undef = $this->ntm->getNodeType('nt:unstructured');
        $this->assertTrue($undef->canSetProperty('something', true));
    }

    public function testCanRemoveProperty()
    {
        $this->assertTrue($this->file->canRemoveProperty('notdefined'));
        $this->assertTrue($this->file->canRemoveProperty('jcr:content')); // this is a child, not a property...
        $this->assertTrue($this->file->canRemoveProperty('jcr:mimeType'));
        $this->assertFalse($this->file->canRemoveProperty('jcr:created'));
    }

}
