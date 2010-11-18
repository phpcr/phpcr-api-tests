<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * Covering jcr-2.8.3 spec $10.6
 */
class jackalope_tests_write_ManipulationTest_DeleteMethodsTest extends jackalope_baseCase {

    static public function setupBeforeClass() {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('write/manipulation/base.xml');
    }
    public function setUp() {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getNode('/tests_write_manipulation_base/numberPropertyNode/jcr:content');
        $this->property = $this->sharedFixture['session']->getProperty('/tests_write_manipulation_base/numberPropertyNode/jcr:content/longNumber');
    }

    /**
     * @covers SessionInterface::removeItem
     */
    public function testRemoveItemNode() {
        $parent = $this->node->getParent();
        $this->assertTrue($parent->hasNode('jcr:content'));
        $this->sharedFixture['session']->removeItem('/tests_write_manipulation_base/numberPropertyNode/jcr:content');
        $this->assertFalse($parent->hasNode('jcr:content'));
    }
    /**
     * @covers SessionInterface::removeItem
     */
    public function testRemoveItemProperty() {
        $node = $this->property->getParent();
        $this->assertTrue($node->hasProperty('longNumber'));
        $this->sharedFixture['session']->removeItem('/tests_write_manipulation_base/numberPropertyNode/jcr:content/longNumber');
        $this->assertFalse($node->hasProperty('longNumber'));
    }
    /**
     * @covers SessionInterface::removeItem
     * @expectedException \PHPCR\ConstraintViolationException
     */
    public function testRemoveItemConstraintViolation() {
        //not only remove item but also save session, as check might only be done on save
        $this->markTestIncomplete('TODO: figure out how to provoke that error');
    }
    /**
     * @covers SessionInterface::removeItem
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testRemoveItemNotExisting() {
        $this->sharedFixture['session']->removeItem('/not/existing');
    }
    /**
     * @covers ItemInterface::remove
     */
    public function testRemoveNode() {
        $parent = $this->node->getParent();
        $this->assertTrue($parent->hasNode('jcr:content'));
        $this->node->remove();
        $this->assertFalse($parent->hasNode('jcr:content'));
    }
    /**
     * @covers ItemInterface::remove
     */
    public function testRemoveProperty() {
        $node = $this->property->getParent();
        $this->assertTrue($node->hasProperty('longNumber'));
        $this->property->remove();
        $this->assertFalse($node->hasProperty('longNumber'));
    }

    public function testNodeRemoveProperty() {
        $this->assertTrue($this->node->hasProperty('longNumber'));
        $this->node->setProperty('longNumber', null);
        $this->assertFalse($this->node->hasProperty('longNumber'));
        $this->assertFalse($this->sharedFixture['session']->itemExists('/tests_write_manipulation_base/numberPropertyNode/jcr:content/longNumber'));
    }
    public function testNodeRemovePropertyNotExisting() {
        $this->node->setProperty('inexistent', null);
        //TODO: what should happen?
    }
    /**
     * @covers NodeInterface::setProperty
     * @expectedException \PHPCR\ConstraintViolationException
     */
    public function testNodeRemovePropertyConstraintViolation() {
        //not only remove item but also save session, as check might only be done on save
        $this->markTestIncomplete('TODO: figure out how to provoke that error');
    }
    /**
     * @covers NodeInterface::remove
     * @covers SessionInterface::getNode
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetRemovedNodeSession() {
        $path = $this->node->getPath();
        $this->node->remove();
        $this->sharedFixture['session']->getNode($path);
    }
    /**
     * @covers NodeInterface::remove
     * @covers NodeInterface::getNode
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetRemovedNodeNode() {
        $parent = $this->node->getParent();
        $name = $this->node->getName();
        $this->node->remove();
        $parent->getNode($name);
    }
    /**
     * @covers NodeInterface::remove
     * @covers SessionInterface::getNode
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetRemovedPropertySession() {
        $path = $this->property->getPath();
        $this->property->remove();
        $this->sharedFixture['session']->getProperty($path);
    }
    /**
     * @covers NodeInterface::remove
     * @covers NodeInterface::getNode
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetRemovedPropertyNode() {
        $parent = $this->property->getParent();
        $name = $this->property->getName();
        $this->property->remove();
        $parent->getProperty($name);
    }
    /**
     * try to remove a node that has already been removed in this session
     */
    public function testRemoveRemovedNode() {
        $path = $this->node->getPath();
        $this->node->remove();
        $this->sharedFixture['session']->removeItem($path);
    }
    /**
     * add node at place where there already was an other
     */
    public function testAddNodeOverRemoved() {
        $name = $this->node->getName();
        $path = $this->node->getPath();
        $parent = $this->node->getParent();
        $this->node->remove();
        $new = $parent->addNode($name);
        $item = $this->sharedFixture['session']->getNode($path);
        $this->assertEquals($new, $item);
        $this->markTestIncomplete('TODO: check if saving the session works properly');
    }
}


