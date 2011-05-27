<?php
require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/** test javax.jcr.Node read methods (read) §5.6
 *
 * todo: getCorrespondingNodePath, getDefinition, getMixinNodeTypes, getPrimaryNodeType, isNodeType
 *
 * NodeWriteMethods (level2): addMixin, addNode, canAddMixin, isCheckedOut, isLocked, orderBefore, removeMixin, removeShare, removeSharedSet, setPrimaryType, setProperty, update. Base Item write methods: isModified, refresh, save, remove
 * Lifecycle: followLifecycleTransition, getAllowedLifecycleTransistions
 */
class Reading_5_NodeReadMethodsTest extends phpcr_suite_baseCase
{
    protected $rootNode;
    protected $node;
    protected $deepnode;

    static public function  setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('general/base');
    }

    public function setUp()
    {
        parent::setUp();
        $this->rootNode = $this->sharedFixture['session']->getRootNode();
        $this->node = $this->rootNode->getNode('tests_general_base');
        $this->deepnode = $this->node->getNode('multiValueProperty')->getNode('deepnode');
    }

    /*** item base methods for node ***/
    public function testGetAncestor()
    {
        $ancestor = $this->deepnode->getAncestor(0);
        $this->assertNotNull($ancestor);
        $this->assertTrue($this->rootNode->isSame($ancestor), 'depth 0 wrong');

        $ancestor = $this->deepnode->getAncestor(1);
        $this->assertNotNull($ancestor);
        $this->assertTrue($this->node->isSame($ancestor), 'depth 1 wrong');

        $ancestor = $this->deepnode->getAncestor(2);
        $this->assertNotNull($ancestor);
        $this->assertTrue($this->node->getNode('multiValueProperty')->isSame($ancestor), 'depth 2 wrong');

        //self
        $ancestor = $this->deepnode->getAncestor($this->deepnode->getDepth());
        $this->assertNotNull($ancestor);
        $this->assertTrue($this->deepnode->isSame($ancestor));
    }

    /**
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetAncestorTooDeep()
    {
        $this->deepnode->getAncestor($this->deepnode->getDepth()+1);
    }

    public function testGetDepth()
    {
        $this->assertEquals(1, $this->node->getDepth());
        $this->assertEquals(3, $this->deepnode->getDepth());
    }
    public function testGetName()
    {
        $name = $this->node->getName();
        $this->assertNotNull($name);
        $this->assertEquals('tests_general_base', $name);
    }
    public function testGetParent()
    {
        $parent = $this->deepnode->getParent();
        $this->assertNotNull($parent);
        $this->assertTrue($this->node->getNode('multiValueProperty')->isSame($parent));
    }
    public function testGetPath()
    {
        $path = $this->deepnode->getPath();
        $this->assertEquals('/tests_general_base/multiValueProperty/deepnode', $path);
    }
    public function testGetSession()
    {
        $sess = $this->node->getSession();
        $this->assertInstanceOf('PHPCR\SessionInterface', $sess);
        //how to further check if we got the right session?
    }
    public function testIsNew()
    {
        $this->assertFalse($this->deepnode->isNew());
    }
    public function testIsNode()
    {
        $this->assertTrue($this->deepnode->isNode());
    }
    //isSame implicitely tested in the path/parent tests

    public function testAccept()
    {
        $mock = $this->getMock('PHPCR\ItemVisitorInterface', array('visit'));
        $mock->expects($this->once())
            ->method('visit')
            ->with($this->equalTo($this->node));

        $this->node->accept($mock);
    }

    /*** node specific methods ***/
    public function testGetNodeAbsolutePath()
    {
        $node = $this->rootNode->getNode('/tests_general_base');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('tests_general_base', $node->getName());
    }

    public function testGetNodeRelativePath()
    {
        $node = $this->rootNode->getNode('tests_general_base');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('tests_general_base', $node->getName());
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetNodePathNotFoundException()
    {
        $this->rootNode->getNode('/foobar');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetNodeRepositoryException()
    {
        $this->rootNode->getNode('/ /'); //space is not valid in path
    }

    public function testGetNodes()
    {
        $node1 = $this->rootNode->getNode('tests_general_base');
        $iterator = $this->rootNode->getNodes();
        $this->assertInstanceOf('Iterator', $iterator);
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetNodesRepositoryException()
    {
        $this->markTestIncomplete('TODO: Figure how to produce this exception');
    }

    public function testGetNodesPattern()
    {
        $iterator = $this->node->getNodes("idExample");
        $this->nodes = array();
        foreach ($iterator as $n) {
            array_push($this->nodes, $n->getName());
        }
        $this->assertContains('idExample', $this->nodes);
        $this->assertNotContains('index.txt', $this->nodes);
    }

    public function testGetNodesPatternAdvanced()
    {
        $this->node = $this->rootNode->getNode('tests_general_base');
        $iterator = $this->node->getNodes("test:* | idExample");
        $this->nodes = array();
        foreach ($iterator as $n) {
            array_push($this->nodes, $n->getName());
        }
        $this->assertContains('idExample', $this->nodes);
        $this->assertContains('test:namespacedNode', $this->nodes);
        $this->assertNotContains('index.txt', $this->nodes);
    }
    public function testGetNodesNameGlobs()
    {
        $node = $this->rootNode->getNode('/tests_general_base');
        $iterator = $node->getNodes(array('idExample', 'test:*', 'jcr:*'));
        $nodes = array();
        foreach ($iterator as $n) {
            array_push($nodes, $n->getName());
        }
        $this->assertEquals(2, count($nodes));
        $this->assertContains('idExample', $nodes);
        $this->assertContains('test:namespacedNode', $nodes);
        $this->assertNotContains('jcr:content', $nodes); //jrc:content is not immediate child
        $this->assertNotContains('index.txt', $nodes);
    }

    public function testGetProperty()
    {
        $prop = $this->node->getProperty('jcr:created');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetPropertyPathNotFoundException()
    {
        $this->node->getProperty('foobar');
    }

    public function testGetPropertyOfOtherNode()
    {
        $prop = $this->node->getProperty('numberPropertyNode/jcr:content/ref');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals('/tests_general_base/numberPropertyNode/jcr:content/ref', $prop->getPath());
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetPropertyRepositoryException()
    {
        $this->node->getProperty('//');
    }

    public function testGetPropertiesAll()
    {
        $iterator = $this->node->getProperties();
        $this->assertInstanceOf('Iterator', $iterator);
        $props = array();
        foreach ($iterator as $prop) {
            array_push($props, $prop->getName());
        }
        $this->assertContains('jcr:created', $props);
    }

    public function testGetPropertiesPattern()
    {
        $iterator = $this->node->getProperties('jcr:cr*');
        $this->assertInstanceOf('Iterator', $iterator);
        $props = array();
        foreach ($iterator as $prop) {
            array_push($props, $prop->getName());
        }
        $this->assertContains('jcr:created', $props);
        $this->assertNotContains('jcr:primaryType', $props);
    }

    public function testGetPropertiesNameGlobs()
    {
        $iterator = $this->node->getProperties(array('jcr:cr*', 'jcr:prim*'));
        $this->assertInstanceOf('Iterator', $iterator);
        $props = array();
        foreach ($iterator as $prop) {
            array_push($props, $prop->getName());
        }
        $this->assertContains('jcr:created', $props); //TODO: this should have been fixed in current jackrabbit http://issues.apache.org/jira/browse/JCR-2060
        $this->assertContains('jcr:primaryType', $props);
    }

    /**
     * @expectedException \PHPCR\RepositoryError
     */
    public function testGetPropertiesRepositoryError()
    {
        $this->markTestIncomplete('TODO: Figure how to produce this error');
    }

    public function testGetPrimaryItem()
    {
        $node = $this->node->getNode('index.txt')->getPrimaryItem();
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('/tests_general_base/index.txt/jcr:content', $node->getPath());
    }

    /**
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetPrimaryItemItemNotFound()
    {
        $this->rootNode->getPrimaryItem();
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetPrimaryItemRepositoryException()
    {
        $this->markTestIncomplete('TODO: Figure how to produce this error');
    }

    public function testGetIdentifier()
    {
        $id = $this->node->getNode('idExample')->getIdentifier();
        $this->assertEquals('842e61c0-09ab-42a9-87c0-308ccc90e6f4', $id);
    }

    public function testGetIndex()
    {
        //TODO: Improve this test to test actual multiple nodes
        $index = $this->node->getIndex();
        $this->assertTrue(is_numeric($index));
        $this->assertEquals(1, $index);
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetIndexRepositoryException()
    {
        $this->markTestIncomplete('TODO: Figure how to produce this error');
    }

    /**
     * @group getReferences
     */
    public function testGetReferencesAll()
    {
        $target = $this->rootNode->getNode('tests_general_base/idExample');
        $source = $this->rootNode->getProperty('tests_general_base/numberPropertyNode/jcr:content/ref');

        $iterator = $target->getReferences();
        $this->assertInstanceOf('Iterator', $iterator);

        //there is exactly one node with reference to idExample.
        $this->assertEquals(1, count($iterator), "Wrong number of references to idExample");
        foreach ($iterator as $prop) {
            $this->assertInstanceOf('\PHPCR\PropertyInterface', $prop);
            $this->assertEquals($source, $prop);
        }
    }

    /**
     * Test that getReferences() on a non-referenced node will return no references
     * @group getReferences
     */
    public function testGetReferencesOnNonReferencedNode()
    {
        $target = $this->rootNode->getNode('tests_general_base/numberPropertyNode');

        $iterator = $target->getReferences();
        $this->assertInstanceOf('Iterator', $iterator);

        //there is no node with reference to numberPropertyNode.
        $this->assertEquals(0, count($iterator), "Wrong number of references to numberPropertyNode");
    }

    /**
     * @group getReferences
     */
    public function testGetReferencesName()
    {
        $target = $this->rootNode->getNode('tests_general_base/idExample');
        $source = $this->rootNode->getProperty('tests_general_base/numberPropertyNode/jcr:content/ref');

        $iterator = $target->getReferences('ref');
        $this->assertInstanceOf('Iterator', $iterator);

        //there is exactly one node with reference to idExample.
        $this->assertEquals(1, count($iterator), "Wrong number of references with name ref to idExample");
        foreach ($iterator as $prop) {
            $this->assertInstanceOf('\PHPCR\PropertyInterface', $prop);
            $this->assertEquals($source, $prop);

            $reference = $prop->getNode();
            $this->assertInstanceOf('PHPCR\NodeInterface', $reference);
            $this->assertEquals($reference, $target);
        }
    }

    public function testGetReferencesNonexistingName()
    {
        $this->markTestIncomplete('TODO: Implement Node::getReferences');
        $target = $this->rootNode->getNode('tests_general_base/idExample');
        $iterator = $target->getReferences('notexisting');
        $this->assertInstanceOf('Iterator', $iterator);
        $this->assertEquals(0, count($iterator), "Wrong number of references with name notexisting to idExample");
    }

    public function testGetWeakReferencesAll()
    {
        $this->markTestIncomplete('TODO: Have a weakly referenced node');
        $iterator = $this->node->getWeakReferences();
        $this->assertInstanceOf('Iterator', $iterator);
    }

    public function testGetWeakReferencesName()
    {
        $this->markTestIncomplete('TODO: Have a weakly referenced node and referencer with name');
    }
    public function testGetSharedSetUnreferenced()
    {
        $iterator = $this->node->getSharedSet();
        $this->assertInstanceOf('Iterator', $iterator);
        $this->assertTrue($iterator->valid());
        $node = $iterator->current();
        $this->assertEquals($node, $this->node);
    }
    public function testGetSharedSetReferenced()
    {
        $this->markTestIncomplete('TODO: Have a referenced node');
    }

    public function testHasNodeTrue()
    {
        $this->assertTrue($this->node->hasNode('index.txt'));
    }

    public function testHasNodePathTrue()
    {
        $this->assertTrue($this->deepnode->hasNode('../../numberPropertyNode/jcr:content'));
    }

    public function testHasNodeFalse()
    {
        $this->assertFalse($this->node->hasNode('foobar'));
    }

    public function testHasNodesTrue()
    {
        $this->assertTrue($this->node->hasNodes());
    }

    public function testHasNodesFalse()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $this->assertFalse($node->hasNodes());
    }

    public function testHasPropertyTrue()
    {
        $this->assertTrue($this->node->hasProperty('jcr:created'));
    }

    public function testHasPropertyFalse()
    {
        $this->assertFalse($this->node->hasProperty('foobar'));
    }

    public function testHasPropertyAbsolutePathTrue()
    {
        $this->assertTrue($this->node->hasProperty($this->node->getPath().'/jcr:created'));
    }

    public function testHasPropertyAbsolutePathFalse()
    {
        $this->assertFalse($this->node->hasProperty('/foobar'));
    }

    public function testHasPropertiesTrue()
    {
        $this->assertTrue($this->node->hasProperties('index.txt'));
    }

    public function testHasPropertiesFalse()
    {
        $this->markTestIncomplete('TODO: Figure how to create a node even without jcr:primaryType');
    }

    public function testIterator() {
        $this->assertTraversableImplemented($this->node);
        $results = false;
        foreach ($this->node as $name => $child) {
            $results = true;
            $this->assertInternalType('string', $name);
            $this->assertInstanceOf('\PHPCR\NodeInterface', $child);
            $this->assertEquals($name, $child->getName());
        }
        $this->assertTrue($results, 'Iterator had no elements');
    }
}
