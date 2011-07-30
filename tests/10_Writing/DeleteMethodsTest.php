<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
 * Covering jcr-2.8.3 spec $10.9
 */
class Writing_10_DeleteMethodsTest extends phpcr_suite_baseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass('10_Writing/delete');
    }

    public function setUp()
    {
        $this->renewSession(); // get rid of cache from previous tests
        parent::setUp();
    }

    /**
     * @covers \PHPCR\SessionInterface::removeItem
     */
    public function testRemoveItemNode()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $parent = $this->node->getParent();
        $this->assertTrue($parent->hasNode('testRemoveItemNode'));
        $this->sharedFixture['session']->removeItem('/tests_write_manipulation_delete/testRemoveItemNode');
        $this->assertFalse($parent->hasNode('testRemoveItemNode'), 'Node was not removed');
    }

    /**
     * @covers \PHPCR\SessionInterface::removeItem
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
     * @covers \PHPCR\SessionInterface::removeItem
     * @expectedException \PHPCR\ConstraintViolationException
     */
    public function testRemoveItemConstraintViolation()
    {
        //not only remove item but also save session, as check might only be done on save
        $this->markTestIncomplete('TODO: remove an jcr:data from an nt:file node and save');

        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
    }

    /**
     * @covers \PHPCR\SessionInterface::removeItem
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testRemoveItemNotExisting()
    {
        $this->sharedFixture['session']->removeItem('/not/existing');
    }

    /**
     * @covers \PHPCR\ItemInterface::remove
     */
    public function testRemoveNode()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $parent = $this->node->getParent();
        $this->assertTrue($parent->hasNode('testRemoveNode'));
        $this->node->remove();
        $this->assertFalse($parent->hasNode('child'));
    }

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

    public function testRemovePropertyFromBackend()
    {
        $this->rootNode->setProperty('toBeDeletedProperty', 'TEMP');
        $this->sharedFixture['session']->save();

        $this->renewSession();

        $node = $this->sharedFixture['session']->getNode('/');
        $this->assertEquals('TEMP', $node->getPropertyValue('toBeDeletedProperty'), 'Property was not created');

        $node->getProperty('toBeDeletedProperty')->remove();
        $this->sharedFixture['session']->save();

        $this->renewSession();

        $this->setExpectedException('\PHPCR\PathNotFoundException');
        $this->sharedFixture['session']->getNode('/')->getProperty('toBeDeletedProperty');

    }

    /**
     * @covers \PHPCR\PropertyInterface::remove
     */
    public function testRemoveProperty()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $property = $this->node->getProperty('longNumber');
        $this->assertTrue($this->node->hasProperty('longNumber'));
        $property->remove();
        $this->assertFalse($this->node->hasProperty('longNumber'));
    }

    /**
     * @covers \PHPCR\NodeInterface::setProperty
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
     * @covers \PHPCR\NodeInterface::setProperty
     */
    public function testNodeRemovePropertyNotExisting()
    {
//        $this->node->setProperty('inexistent', null);
        $this->markTestIncomplete('TODO: figure out what should happen when inexistant property is removed');

        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
    }

    /**
     * @covers \PHPCR\NodeInterface::setProperty
     * @expectedException \PHPCR\ConstraintViolationException
     */
    public function testNodeRemovePropertyConstraintViolation()
    {
        //not only remove item but also save session, as check might only be done on save
        $this->markTestIncomplete('TODO: would have to remove required property from a built-in node type');

        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
    }

    /**
     * @covers \PHPCR\NodeInterface::remove
     * @covers \PHPCR\SessionInterface::getNode
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
     * @covers \PHPCR\NodeInterface::remove
     * @covers \PHPCR\NodeInterface::getNode
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
     * @covers \PHPCR\NodeInterface::remove
     * @covers \PHPCR\SessionInterface::getNode
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
     * @covers \PHPCR\NodeInterface::remove
     * @covers \PHPCR\NodeInterface::getNode
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
