<?php
namespace PHPCR\Tests\NodeTypeDiscovery;


/**
 * test NodeInterface::isNodeType (read) §8.6
 */
class NodeNodeTypeReadMethodsTest extends \PHPCR\Test\BaseCase
{
    protected $rootNode;
    protected $node;
    protected $deepnode;

    public function setUp()
    {
        parent::setUp();
        $this->node = $this->rootNode->getNode('tests_general_base');
        $this->nodewithmixin = $this->node->getNode('multiValueProperty');
        $this->deepnode = $this->nodewithmixin->getNode('deepnode');
    }

    public function testIsDirectType()
    {
        $this->assertTrue($this->deepnode->isNodeType('nt:folder'));
        $this->assertFalse($this->deepnode->isNodeType('nt:file'));
        $this->assertFalse($this->deepnode->isNodeType('not:existing'));
    }

    public function testIsNotMixinNoMixins()
    {
        $this->assertFalse($this->deepnode->isNodeType('mix:referenceable'));
    }

    public function testIsParentType()
    {
        $this->assertTrue($this->deepnode->isNodeType('nt:hierarchyNode'));
    }

    public function testIsGrandparentType()
    {
        $this->assertTrue($this->deepnode->isNodeType('mix:created'));
    }

    public function testIsMixin()
    {
        $this->assertTrue($this->nodewithmixin->isNodeType('mix:referenceable'));
    }

    public function testIsNotMixin()
    {
        $this->assertFalse($this->nodewithmixin->isNodeType('mix:language'));
    }

    // note: mixin that is parent is tested in Versioning\NodeTypeReadTest as there is no
    // other mixin that inherits from another type.
}
