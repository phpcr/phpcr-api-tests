<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/** test javax.jcr.Node read methods (read)
 *
 * todo: getCorrespondingNodePath, getDefinition, getMixinNodeTypes, getPrimaryNodeType, isNodeType
 *
 * NodeWriteMethods (level2): addMixin, addNode, canAddMixin, isCheckedOut, isLocked, orderBefore, removeMixin, removeShare, removeSharedSet, setPrimaryType, setProperty, update. Base Item write methods: isModified, refresh, save, remove
 * Lifecycle: followLifecycleTransition, getAllowedLifecycleTransistions
 */
class Read_ReadTest_NodeReadMethodsTest extends jackalope_baseCase
{
    protected $rootNode;
    protected $node;
    protected $deepnode;

    static public function  setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('read/read/base.xml');
    }

    public function setUp()
    {
        parent::setUp();
        $this->rootNode = $this->sharedFixture['session']->getRootNode();
        $this->node = $this->rootNode->getNode('tests_read_access_base');
        $this->deepnode = $this->node->getNode('multiValueProperty');
    }

    /*** item base methods for node ***/
    function testGetAncestor()
    {
        $ancestor = $this->deepnode->getAncestor(0);
        $this->assertNotNull($ancestor);
        $this->assertTrue($this->rootNode->isSame($ancestor));

        $ancestor = $this->deepnode->getAncestor(1);
        $this->assertNotNull($ancestor);
        $this->assertTrue($this->node->isSame($ancestor));

        //self
        $ancestor = $this->deepnode->getAncestor($this->deepnode->getDepth());
        $this->assertNotNull($ancestor);
        $this->assertTrue($this->deepnode->isSame($ancestor));
    }
    public function testGetDepth()
    {
        $this->assertEquals(1, $this->node->getDepth());
        $this->assertEquals(2, $this->deepnode->getDepth());
    }
    public function testGetName()
    {
        $name = $this->node->getName();
        $this->assertNotNull($name);
        $this->assertEquals('tests_read_access_base', $name);
    }
    public function testGetParent()
    {
        $parent = $this->deepnode->getParent();
        $this->assertNotNull($parent);
        $this->assertTrue($this->node->isSame($parent));
    }
    public function testGetPath()
    {
        $path = $this->deepnode->getPath();
        $this->assertEquals('/tests_read_access_base/multiValueProperty', $path);
    }
    public function testGetSession()
    {
        $sess = $this->node->getSession();
        $this->assertType('PHPCR\SessionInterface', $sess);
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


    /*** node specific methods ***/
    public function testGetNodeAbsolutePath()
    {
        $node = $this->rootNode->getNode('/tests_read_access_base');
        $this->assertType('PHPCR\NodeInterface', $node);
        $this->assertEquals('tests_read_access_base', $node->getName());
    }

    public function testGetNodeRelativePath()
    {
        $node = $this->rootNode->getNode('tests_read_access_base');
        $this->assertType('PHPCR\NodeInterface', $node);
        $this->assertEquals('tests_read_access_base', $node->getName());
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
        $node1 = $this->rootNode->getNode('tests_read_access_base');
        $iterator = $this->rootNode->getNodes();
        $this->assertType('Iterator', $iterator);
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
        $this->node = $this->rootNode->getNode('tests_read_access_base');
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
        $node = $this->rootNode->getNode('/tests_read_access_base');
        $iterator = $node->getNodes(array('idExample', 'test:*', 'jcr:*'));
        $nodes = array();
        foreach ($iterator as $n) {
            array_push($nodes, $n->getName());
        }
        $this->assertTrue(count($nodes) == 2);
        $this->assertContains('idExample', $nodes);
        $this->assertContains('test:namespacedNode', $nodes);
        $this->assertNotContains('jcr:content', $nodes); //jrc:content is not immediate child
        $this->assertNotContains('index.txt', $nodes);
    }

    public function testGetProperty()
    {
        $prop = $this->node->getProperty('jcr:created');
        $this->assertType('PHPCR\PropertyInterface', $prop);
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetPropertyPathNotFoundException()
    {
        $this->node->getProperty('foobar');
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
        $this->assertType('Iterator', $iterator);
        $props = array();
        foreach ($iterator as $prop) {
            array_push($props, $prop->getName());
        }
        $this->assertContains('jcr:created', $props);
    }

    public function testGetPropertiesPattern()
    {
        $iterator = $this->node->getProperties('jcr:cr*');
        $this->assertType('Iterator', $iterator);
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
        $this->assertType('Iterator', $iterator);
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
        $this->assertType('PHPCR\NodeInterface', $node);
        $this->assertEquals('/tests_read_access_base/index.txt/jcr:content', $node->getPath());
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

    public function testGetReferencesAll()
    {
        $ref = $this->rootNode->getNode('tests_read_access_base/idExample');
        $iterator = $ref->getReferences();
        $this->assertType('Iterator', $iterator);

        $this->markTestIncomplete('TODO: Have a referenced node');

/*
        JRnode->setProperty with a JRnode ref returns nothing, seems to be broken
        $ref = $this->rootNode->getNode('tests_read_access_base/idExample');
        $this->node->setProperty('testRef', $ref);
*/
    }

    public function testGetReferencesName()
    {
        $this->markTestIncomplete('TODO: Have a referenced node and referencer with name');
    }

    public function testGetWeakReferencesAll()
    {
        $iterator = $this->node->getWeakReferences();
        $this->assertType('Iterator', $iterator);
        $this->markTestIncomplete('TODO: Have a weakly referenced node');
    }

    public function testGetWeakReferencesName()
    {
        $this->markTestIncomplete('TODO: Have a weakly referenced node and referencer with name');
    }
    public function testGetSharedSetUnreferenced()
    {
        $iterator = $this->node->getSharedSet();
        $this->assertType('Iterator', $iterator);
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
        $this->assertTrue($this->deepnode->hasNode('../numberPropertyNode/jcr:content'));
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

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testHasPropertyRepositoryException()
    {
        $this->assertTrue($this->node->hasProperty('/foobar'));
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
        foreach($this->node as $name => $child) {
            $results = true;
            $this->assertInternalType('string', $name);
            $this->assertType('\PHPCR\NodeInterface', $child);
            $this->assertEquals($name, $child->getName());
        }
        $this->assertTrue($results, 'Iterator had no elements');
    }
}
