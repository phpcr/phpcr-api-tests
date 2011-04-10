<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * Covering jcr-2.8.3 spec $10.6
 */
class Write_Manipulation_DeleteMethodsTest extends jackalope_baseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('write/manipulation/delete');
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
        $this->markTestIncomplete('TODO: figure out how to provoke that error');
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
        $parent = $this->node->getParent();
        $this->assertTrue($parent->hasNode('testRemoveNode'));
        $this->node->remove();
        $this->assertFalse($parent->hasNode('child'));
    }

    public function testRemoveNodeFromBackend()
    {
        $node = $this->rootNode->addNode('toBeDeleted', 'nt:unstructured');
        $this->sharedFixture['session']->getObjectManager()->save();

        $this->renewSession();

        $node = $this->sharedFixture['session']->getNode('/toBeDeleted');

        $node->remove();
        $this->sharedFixture['session']->getObjectManager()->save();

        $this->renewSession();

        $this->setExpectedException('\PHPCR\PathNotFoundException');
        $this->sharedFixture['session']->getNode('/toBeDeleted');

    }

    public function testRemovePropertyFromBackend()
    {
        $this->rootNode->setProperty('toBeDeletedProperty', 'TEMP');
        $this->sharedFixture['session']->getObjectManager()->save();

        $this->renewSession();

        $node = $this->sharedFixture['session']->getNode('/');
        $this->assertEquals('TEMP', $node->getPropertyValue('toBeDeletedProperty'), 'Property was not created');

        $node->getProperty('toBeDeletedProperty')->remove();
        $this->sharedFixture['session']->getObjectManager()->save();

        $this->renewSession();

        $this->setExpectedException('\PHPCR\PathNotFoundException');
        $this->sharedFixture['session']->getNode('/')->getProperty('toBeDeletedProperty');

    }

    /**
     * @covers \PHPCR\PropertyInterface::remove
     */
    public function testRemoveProperty()
    {
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
    }
    /**
     * @covers \PHPCR\NodeInterface::setProperty
     * @expectedException \PHPCR\ConstraintViolationException
     */
    public function testNodeRemovePropertyConstraintViolation()
    {
        //not only remove item but also save session, as check might only be done on save
        $this->markTestIncomplete('TODO: figure out how to provoke that error');
    }
    /**
     * @covers \PHPCR\NodeInterface::remove
     * @covers \PHPCR\SessionInterface::getNode
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetRemovedNodeSession()
    {
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
        $path = $this->node->getPath();
        $this->node->remove();
        $this->sharedFixture['session']->removeItem($path);
    }
    /**
     * add node at place where there already was an other
     */
    public function testAddNodeOverRemoved()
    {
        $name = $this->node->getName();
        $path = $this->node->getPath();
        $parent = $this->node->getParent();
        $this->node->remove();
        $new = $parent->addNode($name, 'nt:unstructured');
        $item = $this->sharedFixture['session']->getNode($path);
        $this->assertEquals($new, $item);
        $this->markTestIncomplete('TODO: check if saving the session works properly');
    }
}


