<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

use PHPCR\PropertyType as Type;

/**
 * Covering jcr-283 spec $10.4
 */
class Write_Manipulation_AddMethodsTest extends jackalope_baseCase
{

    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('write/manipulation/add');
    }

    public function setUp()
    {
        $this->renewSession();
        parent::setUp();
    }

    /**
     * @covers Jackalope\Node::addNode
     * @covers Jackalope\Session::getNode
     */
    public function testAddNode()
    {
        $this->markTestSkipped('Find a case where the parent type specifies the type for this node'); //with nt:folder, this is also not working with the java jackrabbit, so it seems not to be an implementation issue
        // should take the primaryType of emptyExample
        $this->assertTrue(is_object($this->node));
        $this->node->addNode('newNode');
        $this->assertNotNull($this->sharedFixture['session']->getNode($this->node->getPath() . '/newNode'), 'Node newNode was not created');
    }
    /**
     * @covers Jackalope\Node::addNode
     * @covers Jackalope\Session::getNode
     */
    public function testAddNodeWithPath()
    {
        // should take the primaryType of <testAddNodeWithPath />
        $this->assertTrue(is_object($this->node));
        $this->node->addNode('test:namespacedNode/newNode', 'nt:unstructured');
        $this->assertNotNull($this->sharedFixture['session']->getNode($this->node->getPath() . '/test:namespacedNode/newNode'), 'Node newNode was not created');
    }

    public function testAddNodeFileType()
    {
        $this->assertTrue(is_object($this->node));
        $this->node->addNode('newFileNode', 'nt:file');
        $newNode = $this->sharedFixture['session']->getNode($this->node->getPath() . '/newFileNode');
        $contentNode = $newNode->addNode('jcr:content', 'nt:resource');
        $contentNode->setProperty('jcr:mimeType', 'text/plain', Type::STRING);
        $contentNode->setProperty('jcr:data', 'Hello', Type::BINARY);
        $contentNode->setProperty('jcr:lastModified', new DateTime(), Type::DATE);

        $this->assertNotNull($newNode, 'Node newFileNode was not created');
        $this->assertTrue($newNode->isNew(), 'Node newFileNode is not marked dirty');
        $this->sharedFixture['session']->save();
        $this->assertFalse($newNode->isNew(), 'Node newFileNode was not saved');

        $this->renewSession();

        $newNode = $this->sharedFixture['session']->getNode($this->node->getPath() . '/newFileNode');
        $this->assertNotNull($newNode, 'Node newFileNode was not created');
        $this->assertEquals('nt:file', $newNode->getPrimaryNodeType()->getName(), 'Node newFileNode was not created');
    }

    public function testAddNodeUnstructuredType()
    {
        $this->assertTrue(is_object($this->node));
        $this->node->addNode('newUnstructuredNode', 'nt:unstructured');
        $this->assertNotNull($this->sharedFixture['session']->getNode($this->node->getPath() . '/newUnstructuredNode'), 'Node newUnstructuredNode was not created');
    }

    public function testAddPropertyOnUnstructured()
    {
        $this->assertTrue(is_object($this->node));
        $node = $this->node->addNode('unstructuredNode', 'nt:unstructured');
        $node->setProperty('test', 'val');

        $this->sharedFixture['session']->save();
        $this->assertFalse($node->isNew(), 'Node was not saved');

        $this->renewSession();
        $node = $this->sharedFixture['session']->getNode($this->node->getPath() . '/unstructuredNode');

        $this->assertNotNull($node, 'Node was not created');
        $this->assertEquals('val', $node->getPropertyValue('test'), 'Property was not saved correctly');

        $node->setProperty('test2', 'val2');

        $this->sharedFixture['session']->save();
        $this->assertFalse($node->isNew(), 'Node was not saved');
        $this->assertFalse($node->getProperty('test2')->isNew(), 'Property was not saved');
        $this->renewSession();
        $node = $this->sharedFixture['session']->getNode($this->node->getPath() . '/unstructuredNode');

        $this->assertEquals('val2', $node->getPropertyValue('test2'), 'Property was not added correctly');
    }

    public function testAddMultiValuePropertyOnUnstructured()
    {
        $this->assertTrue(is_object($this->node));
        $node = $this->node->addNode('unstructuredNode2', 'nt:unstructured');
        $node->setProperty('test', array('val', 'val2'));

        $this->sharedFixture['session']->save();
        $this->assertFalse($node->isNew(), 'Node was not saved');

        $this->renewSession();
        $node = $this->sharedFixture['session']->getNode($this->node->getPath() . '/unstructuredNode2');

        $this->assertNotNull($node, 'Node was not created');
        $this->assertEquals(array('val', 'val2'), $node->getPropertyValue('test'), 'Property was not saved correctly');

        $node->setProperty('test2', array('val3', 'val4'));

        $this->sharedFixture['session']->save();
        $this->assertFalse($node->isNew(), 'Node was not saved');
        $this->assertFalse($node->getProperty('test2')->isNew(), 'Property was not saved');
        $this->renewSession();
        $node = $this->sharedFixture['session']->getNode($this->node->getPath() . '/unstructuredNode2');

        $this->assertEquals(array('val3', 'val4'), $node->getPropertyValue('test2'), 'Property was not added correctly');
    }


    /**
     * @covers Jackalope\Node::addNode
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testAddNodeMissingType()
    {
        $this->assertTrue(is_object($this->node));
        $this->node->addNode('newNode');
    }
    /**
     * @expectedException \PHPCR\NodeType\NoSuchNodeTypeException
     */
    public function testAddNodeWithInexistingType()
    {
        $this->assertTrue(is_object($this->node));
        $this->node->addNode('newFileNode', 'inexistenttype');
        $this->assertNotNull($this->sharedFixture['session']->getNode($this->node->getPath() . '/newFileNode'), 'Node newFileNode was not created');
    }

    /**
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testAddNodeExisting()
    {
        $this->assertTrue(is_object($this->node));
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
        $this->assertTrue(is_object($this->node));
        $parent = $this->node->addNode('nonExistent/newNode', 'nt:unstructured');
    }

    /**
     * try to add a node below a property
     *
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testAddNodeToProperty()
    {
        $this->assertTrue(is_object($this->node));
        $this->node->addNode('prop/failNode', 'nt:unstructured');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAddNodeWithIndex()
    {
        $this->assertTrue(is_object($this->node));
        $this->node->addNode('name[3]', 'nt:unstructured');
    }

    public function testAddNodeChild()
    {
        $this->assertTrue(is_object($this->node));
        $newNode = $this->node->addNode('parent', 'nt:unstructured');
        $newNode->addNode('child', 'nt:unstructured');

        $this->assertTrue($this->sharedFixture['session']->nodeExists('/tests_write_manipulation_add/testAddNodeChild/parent/child'), 'Child node not found [Session]');

        // dispatch to backend
        $session = $this->saveAndRenewSession();
        $this->assertTrue($session->nodeExists('/tests_write_manipulation_add/testAddNodeChild/parent/child'), 'Child node not found [Backend]');
    }

    public function testAddMixinOnNewNode()
    {
        $this->assertTrue(is_object($this->node));
        $newNode = $this->node->addNode('parent', 'nt:unstructured');
        $newNode->addMixin('mix:created');
        $session = $this->saveAndRenewSession();
        $savedNode = $session->getNode($newNode->getPath());
        $resultTypes = array();
        foreach ($savedNode->getMixinNodeTypes() as $type) {
            $resultTypes[] = $type->getName();
        }
        $this->assertEquals(array('mix:created'), $resultTypes, 'Node mixins should contain mix:created');
    }

    public function testAddMixinOnExistingNode()
    {
        $this->assertTrue(is_object($this->node));
        $this->node->addMixin('mix:created');
        $session = $this->saveAndRenewSession();
        $savedNode = $session->getNode($this->node->getPath());
        $resultTypes = array();
        foreach ($savedNode->getMixinNodeTypes() as $type) {
            $resultTypes[] = $type->getName();
        }
        $this->assertEquals(array('mix:created'), $resultTypes, 'Node mixins should contain mix:created');
    }
}
