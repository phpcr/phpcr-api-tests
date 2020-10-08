<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Writing;

use Jackalope\Item;
use Jackalope\Node;
use Jackalope\Session;
use PHPCR\InvalidItemStateException;
use PHPCR\Test\BaseCase;

/**
 * Test the workflow of Item state.
 */
class ItemStateTest extends BaseCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (!$this->session instanceof Session) {
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
        $this->assertInstanceOf(Node::class, $node);
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
        $this->expectException(InvalidItemStateException::class);
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
        } catch (InvalidItemStateException $ex) {
            $flag = true;
        }
        $this->assertTrue($flag);
    }
}
