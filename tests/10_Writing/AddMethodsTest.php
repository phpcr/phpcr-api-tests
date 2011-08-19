<?php
namespace PHPCR\Tests\Writing;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

use PHPCR\PropertyType as Type;

/**
 * Covering jcr-283 spec $10.4
 */
class AddMethodsTest extends \PHPCR\Test\BaseCase
{

    static public function setupBeforeClass($fixtures = '10_Writing/add')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        $this->renewSession();
        parent::setUp();
        //all tests in this suite rely on the trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node, "Something went wrong with fixture loading");
    }

    public function testAddNode()
    {
        $this->markTestSkipped('TODO: Find a case where the parent type specifies the type for this node'); //with nt:folder, this is also not working with the java jackrabbit, so it seems not to be an implementation issue
        // should take the primaryType
        $this->node->addNode('newNode');
        $this->assertNotNull($this->sharedFixture['session']->getNode($this->node->getPath() . '/newNode'), 'Node newNode was not created');
    }

    public function testAddNodeWithPath()
    {
        // should take the primaryType of <testAddNodeWithPath />
        $this->node->addNode('test:namespacedNode/newNode', 'nt:unstructured');
        $this->assertNotNull($this->sharedFixture['session']->getNode($this->node->getPath() . '/test:namespacedNode/newNode'), 'Node newNode was not created');
    }

    public function testAddNodeFileType()
    {
        $path = $this->node->getPath();
        $newNode = $this->node->addNode('newFileNode', 'nt:file');
        $contentNode = $newNode->addNode('jcr:content', 'nt:resource');
        $contentNode->setProperty('jcr:mimeType', 'text/plain', Type::STRING);
        $contentNode->setProperty('jcr:data', 'Hello', Type::BINARY);
        $contentNode->setProperty('jcr:lastModified', new \DateTime(), Type::DATE);

        $this->assertNotNull($newNode, 'Node newFileNode was not created');
        $this->assertTrue($newNode->isNew(), 'Node newFileNode is not marked dirty');
        $this->sharedFixture['session']->save();
        $this->assertFalse($newNode->isNew(), 'Node newFileNode was not saved');

        $this->renewSession();

        $newNode = $this->sharedFixture['session']->getNode($path . '/newFileNode');
        $this->assertNotNull($newNode, 'Node newFileNode was not created');
        $this->assertEquals('nt:file', $newNode->getPrimaryNodeType()->getName(), 'Node newFileNode was not created');
    }

    public function testAddNodeUnstructuredType()
    {
        $this->node->addNode('newUnstructuredNode', 'nt:unstructured');
        $this->assertNotNull($this->sharedFixture['session']->getNode($this->node->getPath() . '/newUnstructuredNode'), 'Node newUnstructuredNode was not created');
    }

    public function testAddPropertyOnUnstructured()
    {
        $path = $this->node->getPath();
        $node = $this->node->addNode('unstructuredNode', 'nt:unstructured');
        $node->setProperty('testprop', 'val');
        $node->setProperty('refprop', $this->node->getNode('ref'));

        $this->sharedFixture['session']->save();
        $this->assertFalse($node->isNew(), 'Node was not saved');

        $this->renewSession();
        $node = $this->sharedFixture['session']->getNode($path . '/unstructuredNode');

        $this->assertNotNull($node, 'Node was not created');
        $this->assertEquals('val', $node->getPropertyValue('testprop'), 'Property was not saved correctly');

        $node->setProperty('test2', 'val2');

        $this->sharedFixture['session']->save();
        $this->assertFalse($node->isNew(), 'Node was not saved');
        $this->assertFalse($node->getProperty('test2')->isNew(), 'Property was not saved');
        $this->renewSession();
        $node = $this->sharedFixture['session']->getNode($path . '/unstructuredNode');

        $this->assertEquals('val2', $node->getPropertyValue('test2'), 'Property was not added correctly');
    }

    public function testAddMultiValuePropertyOnUnstructured()
    {
        $path = $this->node->getPath();

        $node = $this->node->addNode('unstructuredNode2', 'nt:unstructured');
        $node->setProperty('test', array('val', 'val2'));

        $this->sharedFixture['session']->save();
        $this->assertFalse($node->isNew(), 'Node was not saved');

        $this->renewSession();
        $node = $this->sharedFixture['session']->getNode($path . '/unstructuredNode2');

        $this->assertNotNull($node, 'Node was not created');
        $this->assertEquals(array('val', 'val2'), $node->getPropertyValue('test'), 'Property was not saved correctly');

        $node->setProperty('test2', array('val3', 'val4'));

        $this->sharedFixture['session']->save();
        $this->assertFalse($node->isNew(), 'Node was not saved');
        $this->assertFalse($node->getProperty('test2')->isNew(), 'Property was not saved');
        $this->renewSession();
        $node = $this->sharedFixture['session']->getNode($path . '/unstructuredNode2');

        $this->assertEquals(array('val3', 'val4'), $node->getPropertyValue('test2'), 'Property was not added correctly');
    }

    /**
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
        $this->node->addNode('prop/failNode', 'nt:unstructured');
    }

    /**
     * try to add a property of the wrong type
     *
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testAddPropertyWrongType()
    {
        $file = $this->node->addNode('file', 'nt:file');
        $data = $file->addNode('jcr:content', 'nt:resource');
        $data->setProperty('jcr:data', 'abc', \PHPCR\PropertyType::STRING);
        $this->saveAndRenewSession();
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAddNodeWithIndex()
    {
        $this->node->addNode('name[3]', 'nt:unstructured');
    }

    public function testAddNodeChild()
    {
        $newNode = $this->node->addNode('parent', 'nt:unstructured');
        $newNode->addNode('child', 'nt:unstructured');

        $this->assertTrue($this->sharedFixture['session']->nodeExists('/tests_write_manipulation_add/testAddNodeChild/parent/child'), 'Child node not found [Session]');

        // dispatch to backend
        $session = $this->saveAndRenewSession();
        $this->assertTrue($session->nodeExists('/tests_write_manipulation_add/testAddNodeChild/parent/child'), 'Child node not found [Backend]');
    }

    /**
     * a more complex case with child nodes and properties
     */
    public function testAddNodeAndChildNode()
    {
        $parent = $this->node->addNode('parent', 'nt:folder');
        $child = $parent->addNode('child', 'nt:file');
        $content = $child->addNode('jcr:content', 'nt:resource');
        $content->setProperty('jcr:data', '1234', \PHPCR\PropertyType::BINARY);
        $path = $child->getPath();

        $this->saveAndRenewSession();

        $child = $this->sharedFixture['session']->getNode($path);
        $this->assertInstanceOf('PHPCR\NodeInterface', $child);
    }

}
