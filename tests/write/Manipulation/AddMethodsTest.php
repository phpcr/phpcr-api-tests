<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

use PHPCR\PropertyType as Type;

/**
 * Covering jcr-283 spec $10.4
 */
class Write_Manipulation_AddMethodsTest extends jackalope_baseCase
{
    protected $node;

    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('write/manipulation/base.xml');
    }

    public function setUp()
    {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getNode('/tests_write_manipulation_base/emptyExample');
    }

    /**
     * @covers jackalope_Node::addNode
     * @covers jackalope_Session::getNode
     */
    public function testAddNode()
    {
        $this->markTestSkipped('Find a case where the parent type specifies the type for this node'); //with nt:folder, this is also not working with the java jackrabbit, so it seems not to be an implementation issue
        // should take the primaryType of emptyExample
        $this->node->addNode('newNode');
        $this->assertNotNull($this->sharedFixture['session']->getNode($this->node->getPath() . '/newNode'), 'Node newNode was not created');
    }
    /**
     * @covers jackalope_Node::addNode
     * @covers jackalope_Session::getNode
     */
    public function testAddNodeWithPath()
    {
        // should take the primaryType of emptyExample
        $this->node->addNode('../test:namespacedNode/newNode', 'nt:unstructured');
        $this->assertNotNull($this->sharedFixture['session']->getNode($this->node->getPath() . '/../test:namespacedNode/newNode'), 'Node newNode was not created');
    }

    /**
     * @group 1
     */
    public function testAddNodeFileType()
    {
        $this->node->addNode('newFileNode', 'nt:file');
        $newNode = $this->sharedFixture['session']->getNode($this->node->getPath() . '/newFileNode');
        $contentNode = $newNode->addNode('jcr:content', 'nt:resource');
        $contentNode->setProperty('jcr:mimeType', 'text/plain', Type::STRING);
        $contentNode->setProperty('jcr:data', 'Hello', Type::BINARY);
        $contentNode->setProperty('jcr:lastModified', new DateTime(), Type::DATE);

        $this->assertNotNull($newNode, 'Node newFileNode was not created');
        $this->assertTrue($newNode->isNew(), 'Node newFileNode is not marked dirty');
        $this->sharedFixture['session']->getObjectManager()->save();
        $this->assertFalse($newNode->isNew(), 'Node newFileNode was not saved');

        $this->renewSession();

        $newNode = $this->sharedFixture['session']->getNode($this->node->getPath() . '/newFileNode');
        $this->assertNotNull($newNode, 'Node newFileNode was not created');
        $this->assertEquals('nt:file', $newNode->getPrimaryNodeType()->getName(), 'Node newFileNode was not created');
    }

    public function testAddNodeUnstructuredType()
    {
        $this->node->addNode('newUnstructuredNode', 'nt:unstructured');
        $this->assertNotNull($this->sharedFixture['session']->getNode($this->node->getPath() . '/newFileNode'), 'Node newFileNode was not created');
    }

    /**
     * @covers jackalope_Node::addNode
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testAddNodeMissingType()
    {
        $this->node->addNode('newNode');
    }
    /**
     * @expectedException \PHPCR\NodeType\NoSuchNodeTypeException
     */
    public function testAddNodeWithInexistingType()
    {
        $this->node->addNode('newFileNode', 'inexistenttype');
        $this->assertNotNull($this->sharedFixture['session']->getNode($this->node->getPath() . '/newFileNode'), 'Node newFileNode was not created');
    }

    /**
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testAddNodeExisting()
    {
        $name = $this->node->getName();
        $parent = $this->node->getParent();
        $parent->addNode($name, 'nt:unstructured');
    }

    /**
     * try to add a node below a not existing node.
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testAddNodePathNotFound()
    {
        $parent = $this->node->addNode('nonExistent/newNode', 'nt:unstructured');
    }

    /**
     * try to add a node below a property
     *
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testAddNodeToProperty()
    {
        $this->node->addNode('../numberPropertyNode/jcr:created/name', 'nt:unstructured');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAddNodeWithIndex()
    {
        $this->node->addNode('name[3]', 'nt:unstructured');
    }
}
