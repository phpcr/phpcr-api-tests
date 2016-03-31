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

/**
 * test sequences of adding / moving / removing stuff inside a transaction.
 */
class CombinedManipulationsTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = '10_Writing/combinedmanipulations')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        $this->renewSession(); // kill cache between tests
        parent::setUp();
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
    }

    /**
     * remove a node and then add a new one at the same path.
     *
     * the old should disappear and a new one appear in place
     */
    public function testRemoveAndAdd()
    {
        $node = $this->node->getNode('child');
        $path = $node->getPath();
        $parentpath = $this->node->getPath();
        $this->assertInstanceOf('PHPCR\NodeType\NodeTypeInterface', $node->getPrimaryNodeType());
        $this->assertSame('nt:unstructured', $node->getPrimaryNodeType()->getName());

        $node->remove();
        $this->assertFalse($this->session->nodeExists($path));
        $this->assertFalse($this->node->hasNode('child'));
        $this->node->addNode('child', 'nt:folder');

        $this->assertTrue($this->session->nodeExists($path), "No node at $path");
        $this->assertTrue($this->node->hasNode('child'), "No child 'child' at $path");
        $this->session->save();
        $this->assertTrue($this->session->nodeExists($path));
        $this->assertTrue($this->node->hasNode('child'));

        $session = $this->saveAndRenewSession();

        $this->assertTrue($session->nodeExists($path));
        $this->assertTrue($session->getNode($parentpath)->hasNode('child'));
        $node = $session->getNode($path);
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertSame('nt:folder', $node->getPrimaryNodeType()->getName());
        $this->assertFalse($node->hasNodes());
    }

    /**
     * remove a node, save and then add a new one at the same path.
     *
     * almost the same as above, but we had bugs in jackalope with internal
     * state tracking in this situation
     */
    public function testRemoveSaveAndAdd()
    {
        $node = $this->node->getNode('child');
        $path = $node->getPath();
        $this->assertInstanceOf('PHPCR\NodeType\NodeTypeInterface', $node->getPrimaryNodeType());
        $this->assertSame('nt:unstructured', $node->getPrimaryNodeType()->getName());

        $node->remove();
        $this->node->setProperty('test', 'toast');

        $this->session->save();
        $newnode = $this->node->addNode('child', 'nt:folder');
        $this->assertNotSame($node, $newnode); // adding the node has to create a new object

        $this->node->getPropertyValue('test');
        $this->assertSame($newnode, $this->node->getNode($newnode->getName()));
        $this->assertSame($newnode, $this->session->getNode($path));

        $this->session->save();

        $this->assertTrue($this->session->nodeExists($path));
        $this->assertTrue($this->node->hasNode('child'));
    }

    /**
     * add a node and remove it immediately without persisting.
     *
     * should not do anything at the backend
     */
    public function testAddAndRemove()
    {
        $parentpath = $this->node->getPath();
        $path = "$parentpath/child";

        $node = $this->node->addNode('child', 'nt:folder');

        $this->assertTrue($this->session->nodeExists($path));
        $this->assertTrue($this->node->hasNode('child'));

        $node->remove();

        $this->assertFalse($this->session->nodeExists($path));
        $this->assertFalse($this->node->hasNode('child'));

        $this->session->save();

        $this->assertFalse($this->session->nodeExists($path));
        $this->assertFalse($this->node->hasNode('child'));

        $session = $this->renewSession();

        $this->assertFalse($session->nodeExists($path));
        $this->assertFalse($session->getNode($parentpath)->hasNode('child'));
    }

    /**
     * add a property and remove it immediately without persisting.
     *
     * should not do anything at the backend
     */
    public function testAddAndRemoveProperty()
    {
        $property = $this->node->setProperty('prop', 'test');
        $path = $property->getPath();

        $this->assertTrue($this->session->propertyExists($path));
        $this->assertTrue($this->node->hasProperty('prop'));

        $property->remove();

        $this->assertFalse($this->session->propertyExists($path));
        $this->assertFalse($this->node->hasProperty('prop'));

        $this->session->save();

        $this->assertFalse($this->session->propertyExists($path));
        $this->assertFalse($this->node->hasProperty('prop'));

        $session = $this->renewSession();

        $this->assertFalse($session->propertyExists($path));
    }

    /**
     * Remove a property and in the same session remove its containing node.
     */
    public function testRemovePropertyAndNode()
    {
        $property = $this->node->setProperty('prop', 'test');
        $nodepath = $this->node->getPath();
        $proppath = $property->getPath();

        $property->remove();
        $this->node->remove();

        $this->assertFalse($this->session->nodeExists($nodepath));
        $this->assertFalse($this->session->propertyExists($proppath));

        $this->session->save();

        $this->assertFalse($this->session->nodeExists($nodepath));
        $this->assertFalse($this->session->propertyExists($proppath));

        $session = $this->renewSession();

        $this->assertFalse($session->nodeExists($nodepath));
        $this->assertFalse($session->propertyExists($proppath));
    }

    /**
     * remove a node and then add a new one at the same path and then remove again.
     *
     * in the end, the node must disapear
     */
    public function testRemoveAndAddAndRemove()
    {
        $node = $this->node->getNode('child');
        $path = $node->getPath();
        $parentpath = $this->node->getPath();

        $node->remove();

        $this->assertFalse($this->session->nodeExists($path));
        $this->assertFalse($this->node->hasNode('child'));

        $node = $this->node->addNode('child', 'nt:folder');

        $this->assertTrue($this->session->nodeExists($path));
        $this->assertTrue($this->node->hasNode('child'));

        $node->remove();

        $this->assertFalse($this->session->nodeExists($path));
        $this->assertFalse($this->node->hasNode('child'));

        $session = $this->saveAndRenewSession();

        $this->assertFalse($session->nodeExists($path));
        $this->assertFalse($session->getNode($parentpath)->hasNode('child'));
    }

    /**
     * remove a node and then add a new one at the same path and then remove again.
     *
     * in the end, the node must disapear
     */
    public function testAddAndRemoveAndAdd()
    {
        $parentpath = $this->node->getPath();
        $path = "$parentpath/child";

        $node = $this->node->addNode('child', 'nt:folder');

        $this->assertTrue($this->session->nodeExists($path));
        $this->assertTrue($this->node->hasNode('child'));

        $node->remove();

        $this->assertFalse($this->session->nodeExists($path));
        $this->assertFalse($this->node->hasNode('child'));

        $newnode = $this->node->addNode('child', 'nt:unstructured');

        $this->assertNotSame($node, $newnode);
        $this->assertTrue($this->session->nodeExists($path));
        $this->assertTrue($this->node->hasNode('child'));

        $this->session->save();

        $this->assertTrue($this->session->nodeExists($path));
        $this->assertTrue($this->node->hasNode('child'));

        $session = $this->renewSession();

        $this->assertTrue($session->nodeExists($path));
        $this->assertTrue($session->getNode($parentpath)->hasNode('child'));
        $node = $session->getNode($parentpath)->getNode('child');
        $this->assertTrue($node->isNodeType('nt:unstructured'));
    }

    public function testRemoveAndAddToplevelNode()
    {
        $nodename = 'toBeDeleted';
        if (!$this->rootNode->hasNode($nodename)) {
            $this->rootNode->addNode($nodename, 'nt:unstructured');
        }
        $session = $this->saveAndRenewSession();
        $node = $session->getNode("/$nodename");

        // remove + add
        $node->remove();
        $node = $this->rootNode->addNode($nodename, 'nt:unstructured');
        $this->assertTrue($node->isNew());
        $session->save();

        $this->assertTrue($session->nodeExists("/$nodename"));

        $this->renewSession();

        $this->assertTrue($this->session->nodeExists("/$nodename"));
    }

    public function testRemoveAndAddAndRemoveToplevelNode()
    {
        $nodename = 'toBeDeleted';
        if (!$this->rootNode->hasNode($nodename)) {
            $this->rootNode->addNode($nodename, 'nt:unstructured');
        }
        $session = $this->saveAndRenewSession();
        $node = $session->getNode("/$nodename");

        $node->remove();
        $node = $this->rootNode->addNode($nodename, 'nt:unstructured');
        $this->assertTrue($node->isNew());
        $node->remove();

        $session->save();

        $this->renewSession();

        $this->setExpectedException('\PHPCR\PathNotFoundException');
        $this->session->getNode("/$nodename");
    }

    /**
     * remove a node and then move another node at the same path.
     */
    public function testRemoveAndMove()
    {
        $node = $this->session->getNode($this->node->getPath().'/parent/child');
        $path = $node->getPath();
        $this->assertInstanceOf('PHPCR\NodeType\NodeTypeInterface', $node->getPrimaryNodeType());
        $this->assertSame('nt:unstructured', $node->getPrimaryNodeType()->getName());

        $node->remove();
        $this->assertFalse($this->session->nodeExists($path));
        $this->session->move($this->node->getPath().'/other', $path);
        $this->assertTrue($this->session->nodeExists($path));
        $parent = $this->node->getNode('parent');
        $this->assertTrue($parent->hasNode('child'));

        $session = $this->saveAndRenewSession();

        $node = $session->getNode($path);
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertSame('nt:folder', $node->getPrimaryNodeType()->getName());
        $this->assertFalse($node->hasNodes());
    }

    /**
     * Add a node to an existing node. Move the node under a new node. Add another node underneath.
     *
     * And add a node, save, then move the parent.
     */
    public function testAddAndChildAddAndMove()
    {
        $path = $this->node->getPath();

        $node = $this->node->getNode('node');
        $child = $node->addNode('child');

        $existing = $this->node->getNode('existing');
        $existingchild = $existing->addNode('child');

        // move an existing node into a tree of new nodes
        $this->session->move("$path/existing", "$path/node/child/existing");
        $this->assertEquals("$path/node/child/existing/child", $existingchild->getPath());
        $this->session->getNode("$path/node/child/existing")->addNode('otherchild');

        $this->session->save();

        $this->assertTrue($this->session->nodeExists("$path/node/child/existing"));
        $this->assertTrue($this->session->nodeExists("$path/node/child/existing/child"));
        $this->assertTrue($this->session->nodeExists("$path/node/child/existing/otherchild"));

        $this->session->move("$path/node", "$path/target");
        $this->session->save();

        $this->assertEquals("$path/target/child", $child->getPath());

        $session = $this->renewSession();

        $this->assertTrue($session->nodeExists("$path/target/child"));
        $this->assertTrue($session->nodeExists("$path/target/child/existing"));
        $this->assertTrue($session->nodeExists("$path/target/child/existing/child"));
        $this->assertTrue($session->nodeExists("$path/target/child/existing/otherchild"));
    }

    /**
     * From /src/parent/child we remove child, then move parent to /target and then remove /src.
     *
     * We should be left with /target
     */
    public function testRemoveMoveRemove()
    {
        $path = $this->node->getPath();

        $child = $this->session->getNode("$path/src/parent/child");
        $child->remove();
        $this->session->move("$path/src/parent", "$path/target");
        $src = $this->node->getNode('src');
        $src->remove();

        $this->assertTrue($this->session->nodeExists("$path/target"));
        $this->assertFalse($this->session->nodeExists("$path/target/child"));
        $this->assertFalse($this->session->nodeExists("$path/src"));

        $this->session->save();

        $this->assertTrue($this->session->nodeExists("$path/target"));
        $this->assertFalse($this->session->nodeExists("$path/target/child"));
        $this->assertFalse($this->session->nodeExists("$path/src"));

        $session = $this->renewSession();

        $this->assertTrue($session->nodeExists("$path/target"));
        $this->assertFalse($session->nodeExists("$path/target/child"));
        $this->assertFalse($session->nodeExists("$path/src"));
    }

    /**
     * Move a node, then remove one of its properties, then move it again.
     */
    public function testMoveRemovepropertyMove()
    {
        $path = $this->node->getPath();

        $this->session->move("$path/src/parent/child", "$path/src/temp");
        $this->assertFalse($this->session->propertyExists("$path/src/parent/child/test"));
        $node = $this->session->getNode("$path/src/temp");
        $node->getProperty('test')->remove();
        $this->assertFalse($this->session->propertyExists("$path/src/temp/test"));
        $this->session->move("$path/src/temp", "$path/target");

        $this->assertTrue($this->session->nodeExists("$path/target"));
        $this->assertFalse($this->session->propertyExists("$path/target/test"));

        $this->session->save();

        $this->assertTrue($this->session->nodeExists("$path/target"));
        $this->assertFalse($this->session->propertyExists("$path/src/parent/child/test"));
        $this->assertFalse($this->session->propertyExists("$path/src/temp/test"));
        $this->assertFalse($this->session->propertyExists("$path/target/test"));

        $session = $this->renewSession();

        $this->assertTrue($session->nodeExists("$path/target"));
        $this->assertFalse($session->propertyExists("$path/src/parent/child/test"));
        $this->assertFalse($session->propertyExists("$path/src/temp/test"));
        $this->assertFalse($session->propertyExists("$path/target/test"));
    }

    /**
     * Move a node and then try to access one of its children (needs the new path).
     */
    public function testLoadchildMovedNode()
    {
        $path = $this->node->getPath();

        $this->session->move("$path/src/parent", "$path/target");
        $this->assertTrue($this->session->nodeExists("$path/target/child"));
        $node = $this->session->getNode("$path/target/child");
        $this->assertInstanceOf('\PHPCR\NodeInterface', $node);

        $this->session->save();

        $this->assertTrue($this->session->nodeExists("$path/target/child"));

        $session = $this->renewSession();

        $this->assertTrue($session->nodeExists("$path/target/child"));
        $node = $session->getNode("$path/target/child");
        $this->assertInstanceOf('\PHPCR\NodeInterface', $node);
    }

    public function testSessionHasPendingChanges()
    {
        $this->assertFalse($this->session->hasPendingChanges());
        $this->node->setProperty('prop', 'New');
        $this->assertTrue($this->session->hasPendingChanges());
    }

    public function testSimpleSessionRefresh()
    {
        $node = $this->node;

        $node->setProperty('prop', 'New');
        $this->assertEquals('New', $node->getPropertyValue('prop'));

        $othersession = self::$loader->getSession();
        $othernode = $othersession->getNode($node->getPath());
        $othernode->setProperty('prop', 'Other');
        $othernode->setProperty('newprop', 'Test');
        $othersession->save();

        $this->session->refresh(false);
        $this->assertFalse($this->session->hasPendingChanges());
        $this->assertEquals('Other', $node->getPropertyValue('prop'));
        $this->assertTrue($node->hasProperty('newprop'));
        $this->assertEquals('Test', $node->getPropertyValue('newprop'));
    }

    public function testSimpleSessionRefreshKeepChanges()
    {
        $node = $this->node;
        $path = $node->getPath();

        $node->setProperty('prop', 'New');
        $this->assertEquals('New', $node->getPropertyValue('prop'));
        $this->assertTrue($node->isModified());

        $othersession = self::$loader->getSession();
        $othernode = $othersession->getNode($node->getPath());
        $othernode->setProperty('prop', 'Other');
        $othernode->setProperty('newprop', 'Test');
        $othernode->setProperty('mod', 'Changed');
        $othersession->save();

        $this->session->refresh(true);
        $this->assertTrue($this->session->hasPendingChanges());
        $this->assertTrue($node->isModified());
        $this->assertEquals('New', $node->getPropertyValue('prop'));
        $this->assertTrue($node->hasProperty('newprop'));
        $this->assertEquals('Test', $node->getPropertyValue('newprop'));
        $this->assertEquals('Changed', $node->getPropertyValue('mod'));

        $this->session->save();
        $this->assertFalse($node->isModified());
        $this->assertEquals('New', $node->getPropertyValue('prop'));
        $this->assertTrue($node->hasProperty('newprop'));
        $this->assertEquals('Test', $node->getPropertyValue('newprop'));
        $this->assertEquals('Changed', $node->getPropertyValue('mod'));

        $session = $this->renewSession();

        $node = $session->getNode($path);

        $this->assertEquals('New', $node->getPropertyValue('prop'));
        $this->assertTrue($node->hasProperty('newprop'));
        $this->assertEquals('Test', $node->getPropertyValue('newprop'));
        $this->assertEquals('Changed', $node->getPropertyValue('mod'));
    }

    public function testRemoveSessionRefresh()
    {
        $node = $this->node;

        $node->setProperty('prop', null);
        $this->assertFalse($node->hasProperty('prop'));
        $child = $node->getNode('child');
        $child->remove();
        $this->assertFalse($node->hasNode('child'));
        $this->assertFalse($this->session->nodeExists($node->getPath().'/child'));

        $this->session->refresh(false);
        $this->assertFalse($this->session->hasPendingChanges());
        $this->assertEquals('Old', $node->getPropertyValue('prop'));
        $this->assertTrue($node->hasNode('child'));
        $this->assertTrue($this->session->nodeExists($node->getPath().'/child'));
        $this->assertSame($child, $this->session->getNode($node->getPath().'/child'));
    }

    public function testRemoveSessionRefreshKeepChanges()
    {
        $node = $this->node;
        $path = $node->getPath();

        $node->setProperty('prop', null);
        $this->assertFalse($node->hasProperty('prop'));
        $this->assertFalse($this->session->propertyExists($node->getPath().'/prop'));
        $child = $node->getNode('child');
        $child->remove();
        $this->assertFalse($node->hasNode('child'));
        $this->assertFalse($this->session->nodeExists($node->getPath().'/child'));

        $this->session->refresh(true);
        $this->assertTrue($this->session->hasPendingChanges());
        $this->assertFalse($node->hasProperty('prop'));
        $this->assertFalse($this->session->propertyExists($node->getPath().'/prop'));
        $this->assertFalse($node->hasNode('child'));
        $this->assertFalse($this->session->nodeExists($node->getPath().'/child'));

        $this->session->save();
        $this->assertFalse($node->hasProperty('prop'));
        $this->assertFalse($this->session->propertyExists($node->getPath().'/prop'));
        $this->assertFalse($node->hasNode('child'));
        $this->assertFalse($this->session->nodeExists($node->getPath().'/child'));

        $session = $this->renewSession();
        $node = $session->getNode($path);
        $this->assertFalse($node->hasProperty('prop'));
        $this->assertFalse($session->propertyExists($node->getPath().'/prop'));
        $this->assertFalse($node->hasNode('child'));
        $this->assertFalse($session->nodeExists($node->getPath().'/child'));
    }

    /**
     * remove a child node and a property in a different session. should
     * disappear on refresh, even if we want to keep changes.
     */
    public function testRemoveOtherSessionRefreshKeepChanges()
    {
        $node = $this->node;
        $path = $node->getPath();
        $child = $node->getNode('childnode');
        $childprop = $this->session->getProperty($node->getPath().'/child/childprop');

        $node->setProperty('newprop', 'Value');

        $othersession = self::$loader->getSession();
        $othernode = $othersession->getNode($node->getPath());
        $othernode->setProperty('prop', null);
        $othernode->getNode('child')->remove();
        $othernode->getNode('childnode')->remove();
        $othersession->save();

        $this->session->refresh(true);
        try {
            $childprop->getValue();
            $this->fail('Should not be possible to get the value of a deleted property');
        } catch (\PHPCR\InvalidItemStateException $e) {
            //expected
        }

        $this->assertTrue($this->session->hasPendingChanges());
        try {
            $child->getPath();
            $this->fail('getting the path of deleted child should throw exception');
        } catch (\PHPCR\InvalidItemStateException $e) {
            // expected
        }
        $this->assertFalse($node->hasProperty('prop'));
        $this->assertFalse($node->hasNode('child'));
        $this->assertTrue($node->hasProperty('newprop'));
        $this->assertEquals('Value', $node->getPropertyValue('newprop'));

        $this->session->save();
        $this->assertFalse($node->hasProperty('prop'));
        $this->assertFalse($node->hasNode('child'));
        $this->assertTrue($node->hasProperty('newprop'));
        $this->assertEquals('Value', $node->getPropertyValue('newprop'));

        $session = $this->renewSession();
        $node = $session->getNode($path);
        $this->assertFalse($node->hasProperty('prop'));
        $this->assertFalse($node->hasNode('child'));
        $this->assertTrue($node->hasProperty('newprop'));
        $this->assertEquals('Value', $node->getPropertyValue('newprop'));
    }

    /**
     * It should throw an exception when accessing a property of a node created
     * and persisted earlier in the session, but which has since been deleted
     * by a different session. (refresh should not throw an exception).
     */
    public function testRemoveNewNodeInOtherSessionDiscardChanges()
    {
        $this->setExpectedException(
            'PHPCR\PathNotFoundException', 
            'Property bar'
        );

        $node = $this->node;
        $fooNode = $node->addNode('foo');
        $fooNode->setProperty('bar', 'boo');
        $this->session->save();

        $othersession = self::$loader->getSession();
        $othersession->getNode($node->getPath())->remove();
        $othersession->save();

        $this->session->refresh(false);

        $fooNode->getProperty('bar');
    }

    /**
     * Same as above but keep changes when refreshing -- what should happen here?
     *
     * Currently jackalope-doctrine-dbal throws:
     *
     *   PHPCR\RepositoryException:
     *   Setting item /tests_write_combined_manipulation/testRemoveNewNodeInOtherSessionDiscardChanges/foo dirty in state 4 is not expected
     */
    public function testRemoveNewNodeInOtherSessionKeepChanges()
    {
        $node = $this->node;
        $fooNode = $node->addNode('foo');
        $fooNode->setProperty('bar', 'boo');
        $this->session->save();

        $othersession = self::$loader->getSession();
        $othersession->getNode($node->getPath())->remove();
        $othersession->save();

        $this->session->refresh(true);

        $fooNode->getProperty('bar');
    }

    public function testMoveSessionRefresh()
    {
        $node = $this->node;
        $child = $node->getNode('src/child');

        $this->session->move($node->getPath().'/src/child', $node->getPath().'/target/childnew');

        $this->assertFalse($this->session->nodeExists($node->getPath().'/src/child'));
        $this->assertTrue($this->session->nodeExists($node->getPath().'/target/childnew'));

        $this->assertTrue($this->session->hasPendingChanges());
        $this->session->refresh(false);
        $this->assertFalse($this->session->hasPendingChanges());

        $this->assertTrue($this->session->nodeExists($node->getPath().'/src/child'));
        $this->assertFalse($this->session->nodeExists($node->getPath().'/target/childnew'));
        $this->assertEquals($node->getPath().'/src/child', $child->getPath());
        $src = $node->getNode('src');
        $this->assertTrue($src->hasNode('child'));
        $this->assertSame($child, $src->getNode('child'));
        $target = $node->getNode('target');
        $this->assertFalse($target->hasNode('childnew'));
    }

    public function testMoveSessionRefreshKeepChanges()
    {
        $node = $this->node;
        $path = $node->getPath();
        $child = $node->getNode('src/child');

        $this->session->move($node->getPath().'/src/child', $node->getPath().'/target/childnew');

        $this->assertFalse($this->session->nodeExists($node->getPath().'/src/child'));
        $this->assertTrue($this->session->nodeExists($node->getPath().'/target/childnew'));

        $this->session->refresh(true);
        $this->assertTrue($this->session->hasPendingChanges());

        $this->assertFalse($this->session->nodeExists($node->getPath().'/src/child'));
        $this->assertTrue($this->session->nodeExists($node->getPath().'/target/childnew'));
        $this->assertEquals($node->getPath().'/target/childnew', $child->getPath());
        $src = $node->getNode('src');
        $this->assertFalse($src->hasNode('child'));
        $target = $node->getNode('target');
        $this->assertTrue($target->hasNode('childnew'));
        $this->assertSame($child, $target->getNode('childnew'));

        $this->session->save();
        $this->assertFalse($this->session->nodeExists($node->getPath().'/src/child'));
        $this->assertTrue($this->session->nodeExists($node->getPath().'/target/childnew'));
        $this->assertEquals($node->getPath().'/target/childnew', $child->getPath());
        $src = $node->getNode('src');
        $this->assertFalse($src->hasNode('child'));
        $target = $node->getNode('target');
        $this->assertTrue($target->hasNode('childnew'));
        $this->assertSame($child, $target->getNode('childnew'));

        $session = $this->renewSession();
        $node = $session->getNode($path);
        $this->assertFalse($session->nodeExists($node->getPath().'/src/child'));
        $this->assertTrue($session->nodeExists($node->getPath().'/target/childnew'));
        $this->assertEquals($node->getPath().'/target/childnew', $child->getPath());
        $src = $node->getNode('src');
        $this->assertFalse($src->hasNode('child'));
        $target = $node->getNode('target');
        $this->assertTrue($target->hasNode('childnew'));
    }

    public function testAddSessionRefresh()
    {
        $node = $this->node;
        $node->addNode('child');
        $node->setProperty('prop', 'Test');

        $this->session->refresh(false);
        $this->assertFalse($this->session->hasPendingChanges());

        $this->assertFalse($node->hasNode('child'));
        $this->assertFalse($node->hasProperty('prop'));

        $this->assertFalse($this->session->nodeExists($node->getPath().'/child'));
        $this->assertFalse($this->session->propertyExists($node->getPath().'/prop'));

        $this->setExpectedException('\PHPCR\PathNotFoundException');
        $node->getNode('child');
    }

    public function testAddSessionRefreshKeepChanges()
    {
        $node = $this->node;
        $path = $node->getPath();
        $child = $node->addNode('child');
        $property = $node->setProperty('prop', 'Test');

        $this->session->refresh(true);
        $this->assertTrue($this->session->hasPendingChanges());

        $this->assertTrue($node->hasNode('child'));
        $this->assertSame($child, $node->getNode('child'));
        $this->assertTrue($node->hasProperty('prop'));
        $this->assertSame($property, $node->getProperty('prop'));

        $this->assertTrue($this->session->nodeExists($node->getPath().'/child'));
        $this->assertTrue($this->session->propertyExists($node->getPath().'/prop'));

        $this->session->save();
        $this->assertTrue($node->hasNode('child'));
        $this->assertSame($child, $node->getNode('child'));
        $this->assertTrue($node->hasProperty('prop'));
        $this->assertSame($property, $node->getProperty('prop'));

        $this->assertTrue($this->session->nodeExists($node->getPath().'/child'));
        $this->assertTrue($this->session->propertyExists($node->getPath().'/prop'));

        $session = $this->renewSession();
        $node = $session->getNode($path);
        $child = $node->getNode('child');
        $this->assertTrue($node->hasNode('child'));
        $this->assertSame($child, $node->getNode('child'));
        $this->assertTrue($node->hasProperty('prop'));
        $this->assertSame('Test', $node->getPropertyValue('prop'));

        $this->assertTrue($session->nodeExists($node->getPath().'/child'));
        $this->assertTrue($session->propertyExists($node->getPath().'/prop'));
    }

    public function testNodeRevert()
    {
        $node = $this->node;

        $node->addNode('child');
        $node->setProperty('other', 'Test');
        $existingProperty = $node->setProperty('prop', 'New');

        $node->revert();

        $this->assertSame($existingProperty, $node->getProperty('prop'));
        $this->assertSame('Old', $existingProperty->getValue());
        $this->assertFalse($node->hasNode('child'));
        $this->assertFalse($node->hasProperty('other'));
        $this->assertFalse($this->session->nodeExists($node->getPath().'/child'));
        $this->assertFalse($this->session->propertyExists($node->getPath().'/other'));

        $this->setExpectedException('\PHPCR\PathNotFoundException');
        $node->getPropertyValue('other');
    }
}
