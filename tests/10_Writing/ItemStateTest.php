<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

use PHPCR\PropertyType;
use Jackalope\Item;

/**
 * Test the workflow of Item state
 */
class Writing_10_ItemStateTest extends phpcr_suite_baseCase
{
    public function setUp()
    {
        parent::setUp();

        if (! $this->sharedFixture['session']->getWorkspace() instanceof \Jackalope\Workspace) {
            $this->markTestSkipped('This test is only meant for Jackalope');
        }

        $root = $this->sharedFixture['session']->getRootNode();

        if ($root->hasNode('testNode')) {
            $node = $root->getNode('testNode');
            $node->remove();
        }

        if ($root->hasProperty('testProp')) {
            $prop = $root->getProperty('testProp');
            $prop->remove();
        }

        $this->saveAndRenewSession();
    }

    public function testNodeWorkflow()
    {
        $session = $this->sharedFixture['session'];
        $root = $session->getRootNode();

        // New node --> state = NEW
        $node = $root->addNode('testNode');
        $this->assertInstanceOf('\Jackalope\Item', $node);
        $this->assertEquals(Item::STATE_NEW, $node->getState());

        // Modifying a new node keeps it new --> state = NEW
        $node->setProperty('newProp', 4321);
        $this->assertEquals(Item::STATE_NEW, $node->getState());

        // Node saved --> state = DIRTY
        $session->save();
        $this->assertEquals(Item::STATE_DIRTY, $node->getState());

        // Node accessed for reading --> reloaded --> state = CLEAN
        $name = $node->getName();
        $this->assertEquals(Item::STATE_CLEAN, $node->getState());

        // Add a property --> state = MODIFIED
        $node->setProperty('myProp', 1234);
        $this->assertEquals(Item::STATE_MODIFIED, $node->getState());

        // Node saved --> state = DIRTY
        $session->save();
        $this->assertEquals(Item::STATE_DIRTY, $node->getState());

        // Node deleted --> state = DELETED
        $node->remove();
        $this->assertEquals(Item::STATE_DELETED, $node->getState());

        // Node deleted --> read access should throw an error
        $flag = false;
        try {
            $name = $node->getName();
        } catch (\PHPCR\InvalidItemStateException $ex) {
            $flag = true;
        }
        $this->assertTrue($flag);
    }

    public function testPropertyWorkflow()
    {
        $session = $this->sharedFixture['session'];
        $root = $session->getRootNode();

        // New propery --> state = NEW
        $prop = $root->setProperty('testProp', 'some value');
        $this->assertEquals(Item::STATE_NEW, $prop->getState());

        // Modifying a new property keeps it new --> state = NEW
        $prop->setValue('another value');
        $this->assertEquals(Item::STATE_NEW, $prop->getState());

        // Prop saved --> state = DIRTY
        $session->save();
        $this->assertEquals(Item::STATE_DIRTY, $prop->getState());

        // Prop read --> reload --> state = CLEAN
        $value = $prop->getValue();
        $this->assertEquals(Item::STATE_CLEAN, $prop->getState());

        // Prop modified --> state = MODIFIED
        $prop->setValue('something else');
        $this->assertEquals(Item::STATE_MODIFIED, $prop->getState());

        // Prop saved --> state = DIRTY
        $session->save();
        $this->assertEquals(Item::STATE_DIRTY, $prop->getState());

        // Prop read --> reload --> state = CLEAN
        $value = $prop->getValue();
        $this->assertEquals(Item::STATE_CLEAN, $prop->getState());

        // Prop deleted --> state = DELETED
        $prop->remove();
        $this->assertEquals(Item::STATE_DELETED, $prop->getState());

        // Prop deleted --> read access should throw an exception
        $flag = false;
        try {
            $prop->getValue();
        } catch (\PHPCR\InvalidItemStateException $ex) {
            $flag = true;
        }
        $this->assertTrue($flag);
    }
}
