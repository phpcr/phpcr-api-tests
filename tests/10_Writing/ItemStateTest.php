<?php
namespace PHPCR\Tests\Writing;


use Jackalope\Item;

/**
 * Test the workflow of Item state
 */
class ItemStateTest extends \PHPCR\Test\BaseCase
{
    public function setUp()
    {
        parent::setUp();

        if (! $this->session instanceof \Jackalope\Session) {
            $this->markTestSkipped('This test is only meant for Jackalope'); //TODO: this is a unit test that belongs into jackalope
        }

        $root = $this->session->getRootNode();

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
        $root = $this->session->getRootNode();

        // New node --> state = NEW
        $node = $root->addNode('testNode');
        $this->assertInstanceOf('\Jackalope\Node', $node);
        $this->assertEquals(Item::STATE_NEW, $node->getState());

        // Modifying a new node keeps it new --> state = NEW
        $node->setProperty('newProp', 4321);
        $this->assertEquals(Item::STATE_NEW, $node->getState());

        // Node saved --> state = DIRTY
        $this->session->save();
        $this->assertEquals(Item::STATE_DIRTY, $node->getState());

        // Node accessed for reading --> reloaded --> state = CLEAN
        $name = $node->getName();
        $this->assertEquals(Item::STATE_CLEAN, $node->getState());

        // Add a property --> state = MODIFIED
        $node->setProperty('myProp', 1234);
        $this->assertEquals(Item::STATE_MODIFIED, $node->getState());

        // Node saved --> state = DIRTY
        $this->session->save();
        $this->assertEquals(Item::STATE_DIRTY, $node->getState());

        // Node deleted --> state = DELETED
        $node->remove();
        $this->assertEquals(Item::STATE_DELETED, $node->getState());

        $this->assertFalse($root->hasNode('testNode'));

        // Node deleted --> read access should throw an error
        $this->setExpectedException('\PHPCR\InvalidItemStateException');
        $name = $node->getName();
    }

    public function testPropertyWorkflow()
    {
        $root = $this->session->getRootNode();

        // New propery --> state = NEW
        $prop = $root->setProperty('testProp', 'some value');
        $this->assertEquals(Item::STATE_NEW, $prop->getState());

        // Modifying a new property keeps it new --> state = NEW
        $prop->setValue('another value');
        $this->assertEquals(Item::STATE_NEW, $prop->getState());

        // Prop saved --> state = DIRTY
        $this->session->save();
        $this->assertEquals(Item::STATE_DIRTY, $prop->getState());

        // Prop read --> reload --> state = CLEAN
        $value = $prop->getValue();
        $this->assertEquals(Item::STATE_CLEAN, $prop->getState());

        // Prop modified --> state = MODIFIED
        $prop->setValue('something else');
        $this->assertEquals(Item::STATE_MODIFIED, $prop->getState());

        // Prop saved --> state = DIRTY
        $this->session->save();
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
