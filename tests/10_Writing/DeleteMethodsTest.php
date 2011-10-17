<?php
namespace PHPCR\Tests\Writing;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

/**
 * Covering jcr-2.8.3 spec $10.9
 */
class DeleteMethodsTest extends \PHPCR\Test\BaseCase
{
    static public function setupBeforeClass($fixtures = '10_Writing/delete')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        $this->renewSession(); // get rid of cache from previous tests
        parent::setUp();
    }

    /**
     * \PHPCR\SessionInterface::removeItem
     */
    public function testRemoveItemNode()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $session = $this->sharedFixture['session'];

        $parent = $this->node->getParent();
        $this->assertTrue($parent->hasNode('testRemoveItemNode'));

        $session->removeItem($this->node->getPath());

        $this->assertFalse($parent->hasNode('testRemoveItemNode'), 'Node was not removed');
        $this->assertFalse($this->sharedFixture['session']->nodeExists($parent->getPath().'/testRemoveItemNode'));

        $this->saveAndRenewSession();

        $this->assertFalse($this->sharedFixture['session']->nodeExists($parent->getPath().'/testRemoveItemNode'));
    }

    /**
     * \PHPCR\SessionInterface::removeItem
     */
    public function testRemoveItemProperty()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $property = $this->node->getProperty('longNumber');
        $this->assertTrue($this->node->hasProperty('longNumber'));
        $this->sharedFixture['session']->removeItem('/tests_write_manipulation_delete/testRemoveItemProperty/longNumber');
        $this->assertFalse($this->node->hasProperty('longNumber'));
    }

    /**
     * \PHPCR\SessionInterface::removeItem
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testRemoveNodeConstraintViolation()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $this->sharedFixture['session']->removeItem('/tests_write_manipulation_delete/testRemoveNodeConstraintViolation/jcr:content');
        $this->sharedFixture['session']->save();
    }

    /**
     * \PHPCR\SessionInterface::removeItem
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testRemoveItemNotExisting()
    {
        $this->sharedFixture['session']->removeItem('/not/existing');
    }

    /**
     * Check if state of cached parent node is updated correctly
     *
     * \PHPCR\ItemInterface::remove
     */
    public function testRemoveNode()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $parent = $this->node->getParent();
        $this->assertTrue($parent->hasNode('testRemoveNode'));

        $this->node->remove();

        $this->assertFalse($parent->hasNode('testRemoveNode'));
        $this->assertFalse($this->sharedFixture['session']->nodeExists($path));

        $this->saveAndRenewSession();

        $this->assertFalse($this->sharedFixture['session']->nodeExists($path));
    }

    /**
     * Check if state of parent that was not cached when delete was executed is correct
     *
     * \PHPCR\ItemInterface::remove
     */
    public function testRemoveNodeParentState()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath().'/parent/child';
        $session = $this->sharedFixture['session'];

        $child = $session->getNode($path);
        $child->remove();

        $parent = $session->getNode($this->node->getPath().'/parent');
        $this->assertFalse($parent->hasNode('child'));
        $this->assertFalse($session->nodeExists($path));

        $session = $this->saveAndRenewSession();

        $this->assertFalse($session->nodeExists($path));
        $parent = $session->getNode($this->node->getPath().'/parent');
        $this->assertFalse($parent->hasNode('child'));
    }

    /**
     * add a node, save it, remove it, save again, try to access the removed node
     */
    public function testRemoveNodeFromBackend()
    {
        $node = $this->rootNode->addNode('toBeDeleted', 'nt:unstructured');
        $this->sharedFixture['session']->save();

        $this->renewSession();

        $node = $this->sharedFixture['session']->getNode('/toBeDeleted');

        $node->remove();
        $this->sharedFixture['session']->save();

        $this->renewSession();

        $this->setExpectedException('\PHPCR\PathNotFoundException');
        $this->sharedFixture['session']->getNode('/toBeDeleted');

    }

    /**
     * add a property, save it, remove it, save again, try to access the removed property
     */
    public function testRemovePropertyFromBackend()
    {
        $this->rootNode->setProperty('toBeDeletedProperty', 'TEMP');
        $this->saveAndRenewSession();

        $node = $this->sharedFixture['session']->getNode('/');
        $this->assertTrue($node->hasProperty('toBeDeletedProperty'), 'Property was not created');
        $this->assertEquals('TEMP', $node->getPropertyValue('toBeDeletedProperty'), 'wrong value');

        $node->getProperty('toBeDeletedProperty')->remove();
        $this->saveAndRenewSession();

        $this->setExpectedException('\PHPCR\PathNotFoundException');
        $this->sharedFixture['session']->getNode('/')->getProperty('toBeDeletedProperty');

    }

    /**
     * \PHPCR\PropertyInterface::remove
     * \PHPCR\PropertyInterface::setValue
     */
    public function testRemoveProperty()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $property = $this->node->getProperty('longNumber');
        $other = $this->node->getProperty('otherprop');
        $this->assertTrue($this->node->hasProperty('longNumber'));
        $property->remove();
        $this->assertFalse($this->node->hasProperty('longNumber'));
        $other->setValue(null);
        $this->assertFalse($this->node->hasProperty('otherprop'));

        $session = $this->saveAndRenewSession();
        $node = $session->getNode($path);
        $this->assertFalse($this->node->hasProperty('longNumber'));
        $this->assertFalse($this->node->hasProperty('otherprop'));
    }

    /**
     * \PHPCR\NodeInterface::setProperty
     */
    public function testNodeRemoveProperty()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $this->assertTrue($this->node->hasProperty('longNumber'));
        $this->node->setProperty('longNumber', null);
        $this->assertFalse($this->node->hasProperty('longNumber'));
        $this->assertFalse($this->sharedFixture['session']->itemExists('/tests_write_manipulation_delete/testNodeRemobeProperty/longNumber'));
    }

    /**
     * \PHPCR\NodeInterface::setProperty
     */
    public function testNodeRemovePropertyNotExisting()
    {
//        $this->node->setProperty('inexistent', null);
        $this->markTestIncomplete('TODO: figure out what should happen when inexistant property is removed');

        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
    }

    /**
     * \PHPCR\NodeInterface::setProperty
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testNodeRemovePropertyConstraintViolation()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $this->node->setProperty('jcr:created', null);
        $this->sharedFixture['session']->save();
    }

    /**
     * \PHPCR\NodeInterface::remove
     * \PHPCR\SessionInterface::getNode
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetRemovedNodeSession()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $path = $this->node->getPath();
        $this->node->remove();
        $this->sharedFixture['session']->getNode($path);
    }

    /**
     * \PHPCR\NodeInterface::remove
     * \PHPCR\NodeInterface::getNode
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetRemovedNodeNode()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $parent = $this->node->getParent();
        $name = $this->node->getName();
        $this->node->remove();
        $parent->getNode($name);
    }

    /**
     * \PHPCR\NodeInterface::remove
     * \PHPCR\SessionInterface::getNode
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetRemovedPropertySession()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $property = $this->node->getProperty('prop');
        $path = $property->getPath();
        $property->remove();
        $this->sharedFixture['session']->getProperty($path);
    }

    /**
     * \PHPCR\NodeInterface::remove
     * \PHPCR\NodeInterface::getNode
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetRemovedPropertyNode()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $property = $this->node->getProperty('prop');
        $name = $property->getName();
        $property->remove();
        $this->node->getProperty($name);
    }

    /**
     * try to remove a node that has already been removed in this session
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testRemoveRemovedNode()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $path = $this->node->getPath();
        $this->node->remove();
        $this->sharedFixture['session']->removeItem($path);
    }

    /**
     * try to remove a node that has already been removed in this session
     * @expectedException \PHPCR\InvalidItemStateException
     */
    public function testRefreshRemovedProperty()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $property = $this->node->getProperty('longNumber');
        $this->node->setProperty('longNumber', null);
        $property->refresh(false);
    }


    /**
     * add node at place where there already was an other
     */
    public function testAddNodeOverRemoved()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $name = $this->node->getName();
        $path = $this->node->getPath();
        $parent = $this->node->getParent();
        $this->node->remove();
        $new = $parent->addNode($name, 'nt:unstructured');
        $item = $this->sharedFixture['session']->getNode($path);
        $this->assertEquals($new, $item);

        $this->saveAndRenewSession();

        $this->assertNotNull($this->node);
    }

    /**
     * deleting a node must cascade to its children
     */
    public function testDeleteCascade()
    {
        $session = $this->sharedFixture['session'];
        $path = $this->node->getPath();

        $ptest = $this->node->setProperty('test', 'value');
        $prop = $this->node->getProperty('prop');
        $child = $this->node->getNode('child');
        $child->setProperty('test', 'value');
        $childprop = $child->getProperty('prop');
        $childchild = $child->getNode('child');
        $childchildprop = $childchild->getProperty('prop');

        $this->node->remove();

        $items = array($this->node, $ptest, $prop, $child, $childprop, $childchild, $childchildprop);
        foreach ($items as $item) {
            try {
                $this->fail('Should not be able to get path of deleted item '.$item->getPath()); // this should explode
            } catch(\PHPCR\InvalidItemStateException $e) {
                // the exception is expected
            }
        }

        $session->save();

        $this->assertFalse($session->nodeExists("$path/prop"));
        $this->assertFalse($session->nodeExists("$path/test"));
        $this->assertFalse($session->nodeExists("$path/child"));
        $this->assertFalse($session->nodeExists("$path/child/child"));
        $this->assertFalse($session->propertyExists("$path/prop"));
        $this->assertFalse($session->propertyExists("$path/child/prop"));
        $this->assertFalse($session->propertyExists("$path/child/child/prop"));

        foreach ($items as $item) {
            try {
                $this->fail('Should not be able to get path of deleted item '.$item->getPath()); // this should explode
            } catch(\PHPCR\InvalidItemStateException $e) {
                // the exception is expected
            }
        }
    }


    /**
     * It is not allowed to delete a referenced node
     *
     * @expectedException PHPCR\ReferentialIntegrityException
     */
    public function testDeleteReferencedNodeException()
    {
        $destnode = $this->node->getNode('idExample');
        $destnode->remove();
        $this->sharedFixture['session']->save();
    }

    /**
     * however, if the reference is first deleted, it must be possible to
     * delete the node
     */
    public function testDeletePreviouslyReferencedNode()
    {
        // 2) Get the referencing property and delete it
        $sourceprop = $this->node->getProperty('reference');
        $sourceprop->remove();

        // 3) Save and renew session
        $this->saveAndRenewSession();

        // 4) Load the previously referenced node and remove it
        $destnode = $this->node->getNode('idExample');

        $destnode->remove();
        $this->saveAndRenewSession();

        $this->assertFalse($this->node->hasNode($this->getName()));
    }

    /**
     * it must be possible to delete a weakly referenced node
     */
    public function testDeleteWeakReferencedNode()
    {
        $destnode = $this->node->getNode('idExample');
        $destnode->remove();
        $this->saveAndRenewSession();

        $this->assertFalse($this->node->hasNode('idExample'));
    }

}
