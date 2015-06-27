<?php
namespace PHPCR\Tests\Reading;


/**
 * test javax.jcr.Node read methods (read) §5.6
 */
class NodeReadMethodsTest extends \PHPCR\Test\BaseCase
{
    /** @var \PHPCR\NodeInterface */
    protected $rootNode;
    /** @var \PHPCR\NodeInterface */
    protected $node;
    /** @var \PHPCR\NodeInterface */
    protected $deepnode;

    public function setUp()
    {
        parent::setUp();
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
    /**
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetParentRootnode()
    {
        $this->rootNode->getParent();
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

    public function testGetNodeRelativePath()
    {
        $node = $this->rootNode->getNode('tests_general_base');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('tests_general_base', $node->getName());
    }

    public function testGetNodeRelativePathParent()
    {
        $node = $this->node->getNode('..');
        $this->assertSame($this->rootNode, $node);
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetNodePathNotFoundException()
    {
        $this->rootNode->getNode('foobar');
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetNodeAbsolutePath()
    {
        $this->rootNode->getNode('/tests_general_base');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetNodeRepositoryException()
    {
        // @see http://www.day.com/specs/jcr/2.0/3_Repository_Model.html#3.2.2%20Local%20Names
        $this->rootNode->getNode('/./');
    }

    public function testGetNodes()
    {
        $parent = $this->rootNode->getNode('tests_general_base');
        $iterator = $parent->getNodes();
        $this->assertInstanceOf('Iterator', $iterator);
        $this->assertInstanceOf('Countable', $iterator);

        $this->assertCount(8, $iterator);
        foreach ($iterator as $node) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        }
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
        $nodes = array();
        foreach ($iterator as $n) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $n);
            /** @var $n \PHPCR\NodeInterface */
            array_push($nodes, $n->getName());
        }
        $this->assertContains('idExample', $nodes);
        $this->assertNotContains('index.txt', $nodes);
    }

    public function testGetNodesTypeFilter()
    {
        $this->node = $this->rootNode->getNode('tests_general_base');
        $iterator = $this->node->getNodes(null, 'nt:file');
        $nodes = array();
        foreach ($iterator as $n) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $n);
            /** @var $n \PHPCR\NodeInterface */
            array_push($nodes, $n->getName());
        }
        $this->assertContains('index.txt', $nodes);
        $this->assertContains('idExample', $nodes);
        $this->assertNotContains('test:namespacedNode', $nodes);
        $this->assertNotContains('emptyExample', $nodes);
        $this->assertNotContains('multiValueProperty', $nodes);
        $this->assertContains('numberPropertyNode', $nodes);
        $this->assertContains('NumberPropertyNodeToCompare1', $nodes);
        $this->assertContains('NumberPropertyNodeToCompare2', $nodes);
    }

    public function testGetNodesTypeFilterList()
    {
        $this->node = $this->rootNode->getNode('tests_general_base');
        $iterator = $this->node->getNodes("id*", array('nt:file', 'nt:folder'));
        $nodes = array();
        foreach ($iterator as $n) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $n);
            /** @var $n \PHPCR\NodeInterface */
            array_push($nodes, $n->getName());
        }
        $this->assertNotContains('index.txt', $nodes);
        $this->assertContains('idExample', $nodes);
        $this->assertNotContains('test:namespacedNode', $nodes);
        $this->assertNotContains('emptyExample', $nodes);
        $this->assertNotContains('multiValueProperty', $nodes);
        $this->assertNotContains('numberPropertyNode', $nodes);
        $this->assertNotContains('NumberPropertyNodeToCompare1', $nodes);
        $this->assertNotContains('NumberPropertyNodeToCompare2', $nodes);
    }

    public function testGetNodesNameGlobs()
    {
        $node = $this->rootNode->getNode('tests_general_base');
        $iterator = $node->getNodes(array('idExample', 'test:*', 'jcr:*'));
        $nodes = array();
        foreach ($iterator as $n) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $n);
            /** @var $n \PHPCR\NodeInterface */
            array_push($nodes, $n->getName());
        }
        $this->assertCount(2, $nodes);
        $this->assertContains('idExample', $nodes);
        $this->assertContains('test:namespacedNode', $nodes);
        $this->assertNotContains('jcr:content', $nodes); //jrc:content is not immediate child
        $this->assertNotContains('index.txt', $nodes);
    }

    public function testGetNodeNames()
    {
        $node1 = $this->rootNode->getNode('tests_general_base');
        $iterator = $node1->getNodeNames();
        $this->assertInstanceOf('Iterator', $iterator);

        $names = array();
        foreach ($iterator as $name) {
            $names[] = $name;
        }

        $this->assertContains('idExample', $names);
    }

    public function testGetNodeNamesPattern()
    {
        $iterator = $this->node->getNodeNames("id*");
        $names = array();
        foreach ($iterator as $n) {
            array_push($names, $n);
        }
        $this->assertContains('idExample', $names);
        $this->assertNotContains('index.txt', $names);
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

    public function testGetPropertyValue()
    {
        $node = $this->node->getNode('numberPropertyNode/jcr:content');
        $value = $node->getPropertyValue('foo');
        $this->assertEquals('bar', $value);
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetPropertyValueNotFound()
    {
        $node = $this->node->getNode('numberPropertyNode/jcr:content');
        $node->getPropertyValue('notexisting');
    }

    public function testGetPropertyValueWithDefault()
    {
        $node = $this->node->getNode('numberPropertyNode/jcr:content');
        $value = $node->getPropertyValueWithDefault('foo', 'other');
        $this->assertEquals('bar', $value);
    }

    public function testGetPropertyValueWithDefaultNotExisting()
    {
        $node = $this->node->getNode('numberPropertyNode/jcr:content');
        $value = $node->getPropertyValueWithDefault('notexisting', 'other');
        $this->assertEquals('other', $value);
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
        $this->assertContains('jcr:created', $props);
        $this->assertContains('jcr:primaryType', $props);
    }

    public function testGetPropertiesValuesAll()
    {
        $node = $this->session->getNode('/tests_general_base/idExample/jcr:content/weakreference_source1');
        $props = $node->getPropertiesValues();
        $this->assertInternalType('array', $props);
        $this->assertArrayHasKey('ref1', $props);
        $this->assertInstanceOf('PHPCR\NodeInterface', $props['ref1']);
    }

    public function testGetPropertiesValuesAllNoDereference()
    {
        $node = $this->session->getNode('/tests_general_base/idExample/jcr:content/weakreference_source1');
        $props = $node->getPropertiesValues(null,false);
        $this->assertInternalType('array', $props);
        $this->assertArrayHasKey('ref1', $props);
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $props['ref1']);
    }

    public function testGetPropertiesValuesGlob()
    {
        $node = $this->session->getNode('/tests_general_base/idExample/jcr:content/weakreference_source1');
        $props = $node->getPropertiesValues("jcr:*");
        $this->assertInternalType('array', $props);
        /*
         * jcr:mixinTypes is a protected multi-value NAME property
         * it is optional if there are no mixin types declared on this node,
         * but would be mandatory if there where any.
         */
        if (count($props) == 1) {
            $this->assertArrayHasKey('jcr:primaryType', $props);
        } elseif (count($props) == 2) {
            $this->assertArrayHasKey('jcr:primaryType', $props);
            $this->assertArrayHasKey('jcr:mixinTypes', $props);
            $this->assertCount(0, $props['jcr:mixinTypes']);
        } else {
            $this->fail('wrong number of properties starting with jcr:');
        }
    }

    public function testGetReferencePropertyRepeated()
    {
        $node = $this->session->getNode('/tests_general_base/idExample/jcr:content/weakreference_repeated');
        $refs = $node->getPropertyValue('other_ref');
        $this->assertInternalType('array', (array) $refs);
        $this->assertCount(2, $refs);
        foreach ($refs as $node) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        }
    }

    /**
     * @group getPrimaryItem
     */
    public function testGetPrimaryItem()
    {
        $node = $this->node->getNode('index.txt')->getPrimaryItem();
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('/tests_general_base/index.txt/jcr:content', $node->getPath());
    }

    /**
     * @expectedException \PHPCR\ItemNotFoundException
     * @group getPrimaryItem
     */
    public function testGetPrimaryItemItemNotFound()
    {
        $this->rootNode->getPrimaryItem();
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     * @group getPrimaryItem
     */
    public function testGetPrimaryItemRepositoryException()
    {
        // To cause this error, an exception must be thrown by one of the following calls:
        // Session.getWorkspace, Workspace.getNodeTypeManager, NodeTypeManager.getPrimaryNodeType
        // NodeType.getPrimaryItemName, Session.getItem
        $this->markTestIncomplete('TODO: Figure how to produce this error');
    }

    public function testGetIdentifier()
    {
        $id = $this->node->getNode('idExample')->getIdentifier();
        $this->assertEquals('842e61c0-09ab-42a9-87c0-308ccc90e6f4', $id);
    }

    /**
     * The JCR specification is not saying what the properties of this id
     * should be. But it must be a string.
     */
    public function testGetIdentifierNonReferenceable()
    {
        $id = $this->node->getNode('index.txt')->getIdentifier();
        $this->assertInternalType('string', $id);
    }

    /**
     * getIndex has to work even when same-name siblings are not allowed.
     */
    public function testGetIndex()
    {
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
        $source[] = $this->rootNode->getProperty('tests_general_base/numberPropertyNode/jcr:content/ref');
        $source[] = $this->rootNode->getProperty('tests_general_base/numberPropertyNode/jcr:content/multiref');

        $iterator = $target->getReferences();
        $this->assertInstanceOf('Iterator', $iterator);

        //there are two nodes with reference to idExample.
        $this->assertCount(2, $iterator, "Wrong number of references to idExample");
        foreach ($iterator as $prop) {
            $this->assertInstanceOf('\PHPCR\PropertyInterface', $prop);
            $this->assertTrue(in_array($prop, $source));
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
        $this->assertCount(0, $iterator, "Wrong number of references to numberPropertyNode");
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
        $this->assertCount(1, $iterator, "Wrong number of references with name ref to idExample");
        foreach ($iterator as $prop) {
            $this->assertInstanceOf('\PHPCR\PropertyInterface', $prop);
            $this->assertEquals($source, $prop);

            $reference = $prop->getNode();
            $this->assertInstanceOf('PHPCR\NodeInterface', $reference);
            $this->assertSame($reference, $target);
        }
    }

    /**
     * @group getReferences
     */
    public function testGetReferencesNonexistingName()
    {
        $target = $this->rootNode->getNode('tests_general_base/idExample');
        $iterator = $target->getReferences('notexisting');
        $this->assertInstanceOf('Iterator', $iterator);
        $this->assertCount(0, $iterator, "Wrong number of references with name notexisting to idExample");
    }

    /**
     * @group getWeakReferences
     */
    public function testGetWeakReferencesAll()
    {
        $target = $this->rootNode->getNode('tests_general_base/idExample/jcr:content/weakreference_target');
        $source[] = $this->rootNode->getProperty('tests_general_base/idExample/jcr:content/weakreference_source1/ref1');
        $source[] = $this->rootNode->getProperty('tests_general_base/idExample/jcr:content/weakreference_source2/ref2');
        $source[] = $this->rootNode->getProperty('tests_general_base/idExample/jcr:content/weakreference_repeated/other_ref');

        $iterator = $target->getWeakReferences();
        $this->assertInstanceOf('Iterator', $iterator);

        // there are 4 different references, but 2 come from the same property so should only count once.
        $this->assertCount(3, $iterator, "Wrong number of weak references to weakreference_target");
        foreach ($iterator as $prop) {
            $this->assertInstanceOf('\PHPCR\PropertyInterface', $prop);
            $this->assertTrue(in_array($prop, $source, true));
        }
    }

    /**
     * @group getWeakReferences
     */
    public function testGetWeakReferencesName()
    {
        $target = $this->rootNode->getNode('tests_general_base/idExample/jcr:content/weakreference_target');
        $source = $this->rootNode->getProperty('tests_general_base/idExample/jcr:content/weakreference_source1/ref1');

        $iterator = $target->getWeakReferences('ref1');
        $this->assertInstanceOf('Iterator', $iterator);

        $this->assertCount(1, $iterator, "Wrong number of weak references to weakreference_target");
        foreach ($iterator as $prop) {
            $this->assertInstanceOf('\PHPCR\PropertyInterface', $prop);
            $this->assertEquals($prop, $source);
        }
    }

    /**
     * @group getWeakReferences
     */
    public function testGetWeakReferencesNonExistingName()
    {
        $target = $this->rootNode->getNode('tests_general_base/idExample/jcr:content/weakreference_target');

        $iterator = $target->getWeakReferences('unexisting_name');
        $this->assertInstanceOf('Iterator', $iterator);

        $this->assertCount(0, $iterator, "Wrong number of weak references to weakreference_target");
    }

    /**
     * @group getWeakReferences
     */
    public function testGetWeakReferencesOnNonReferencedNode()
    {
        $target = $this->rootNode->getNode('tests_general_base/numberPropertyNode');

        $iterator = $target->getReferences();
        $this->assertInstanceOf('Iterator', $iterator);

        //there is no node with reference to numberPropertyNode.
        $this->assertCount(0, $iterator, "Wrong number of references to numberPropertyNode");
    }

    public function testGetSharedSetUnreferenced()
    {
        // TODO: should this be moved to 14_ShareableNodes
        $iterator = $this->node->getSharedSet();
        $this->assertInstanceOf('Iterator', $iterator);
        $this->assertTrue($iterator->valid());
        $node = $iterator->current();
        $this->assertEquals($node, $this->node);
    }

    public function testGetSharedSetReferenced()
    {
        $this->markTestIncomplete('TODO: should this be moved to 14_ShareableNodes');
    }

    public function testHasNodeTrue()
    {
        $this->assertTrue($this->node->hasNode('index.txt'));
    }

    public function testHasNodePathTrue()
    {
        $this->assertTrue($this->deepnode->hasNode('../../numberPropertyNode/jcr:content'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasNodeAbsolutePathException()
    {
        $this->deepnode->hasNode('/tests_general_base');
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
     * @expectedException \InvalidArgumentException
     */
    public function testHasPropertyAbsolutePathException()
    {
        $this->node->hasProperty('/tests_general_base/nt:primaryType');
    }

    public function testHasPropertiesTrue()
    {
        $this->assertTrue($this->node->hasProperties());
    }

    /*
     *
     * this can not happen, every node has the jcr:primaryType
    public function testHasPropertiesFalse()
    {
        $this->markTestIncomplete('TODO: Figure how to create a node even without jcr:primaryType');
    }
    */

    public function testIterator()
    {
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
