<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\OrderableChildNodes;

use PHPCR\ItemNotFoundException;
use PHPCR\NodeInterface;
use PHPCR\Test\BaseCase;

/**
 * Covering jcr-2.8.3 spec $23.
 */
class OrderBeforeTest extends BaseCase
{
    public static function setupBeforeClass($fixtures = '23_OrderableChildNodes/orderable'): void
    {
        parent::setupBeforeClass($fixtures);
    }

    protected function setUp(): void
    {
        $this->renewSession();
        parent::setUp();
    }

    /**
     * Helper method to assert a certain order of the child nodes.
     *
     * @param array                $names array values are the names in expected order
     * @param NodeInterface        $node  the node whos children are to be checked
     */
    private function assertChildOrder($names, $node)
    {
        $children = [];

        foreach ($node as $name => $dummy) {
            $children[] = $name;
        }
        $this->assertEquals($names, $children);
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderBeforeUp()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $path = $this->node->getPath();

        $this->node->orderBefore('three', 'two');
        $this->assertChildOrder(['one', 'three', 'two', 'four'], $this->node);
        $this->session->save();
        $this->assertChildOrder(['one', 'three', 'two', 'four'], $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(['one', 'three', 'two', 'four'], $node);
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderBeforeFirst()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $path = $this->node->getPath();

        $this->node->orderBefore('three', 'one');
        $this->node->addNode('new');
        $this->node->orderBefore('new', 'three');
        $this->assertChildOrder(['new', 'three', 'one', 'two', 'four'], $this->node);
        $this->session->save();
        $this->assertChildOrder(['new', 'three', 'one', 'two', 'four'], $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(['new', 'three', 'one', 'two', 'four'], $node);
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderBeforeDown()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $path = $this->node->getPath();

        $this->node->orderBefore('two', 'four');
        $this->assertChildOrder(['one', 'three', 'two', 'four'], $this->node);

        $this->session->save();
        $this->assertChildOrder(['one', 'three', 'two', 'four'], $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(['one', 'three', 'two', 'four'], $node);
    }

    /**
     * \PHPCR\NodeInterface::orderBefore.
     */
    public function testNodeOrderBeforeEnd()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $path = $this->node->getPath();

        $this->node->orderBefore('two', null);
        $this->assertChildOrder(['one', 'three', 'four', 'two'], $this->node);

        $this->session->save();
        $this->assertChildOrder(['one', 'three', 'four', 'two'], $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(['one', 'three', 'four', 'two'], $node);
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderBeforeNoop()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $path = $this->node->getPath();

        $this->node->orderBefore('two', 'three');
        $this->assertChildOrder(['one', 'two', 'three', 'four'], $this->node);

        $session = $this->saveAndRenewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(['one', 'two', 'three', 'four'], $node);
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderBeforeSrcNotFound()
    {
        $this->expectException(ItemNotFoundException::class);

        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $this->node->orderBefore('notexisting', 'one');
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderBeforeDestNotFound()
    {
        $this->expectException(ItemNotFoundException::class);

        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $this->node->orderBefore('one', 'notexisting');
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderBeforeSwap()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $path = $this->node->getPath();

        $this->node->orderBefore('four', 'two');
        $this->assertChildOrder(['one', 'four', 'two', 'three', 'five'], $this->node);
        $this->node->orderBefore('two', 'one');
        $this->assertChildOrder(['two', 'one', 'four', 'three', 'five'], $this->node);

        $this->session->save();
        $this->assertChildOrder(['two', 'one', 'four', 'three', 'five'], $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(['two', 'one', 'four', 'three', 'five'], $node);
    }

    /**
     * Test reordering and adding a node and removing another one
     *
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderBeforeUpAndDelete()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $path = $this->node->getPath();

        $this->node->addNode('new1');
        $this->node->addNode('new2');
        $this->node->getNode('three')->remove();
        $this->node->orderBefore('four', 'two');
        $this->node->orderBefore('new1', 'two');
        $this->node->orderBefore('new2', 'two');
        $this->node->getNode('one')->remove();
        $this->assertChildOrder(['four', 'new1', 'new2', 'two'], $this->node);

        $this->session->save();
        $this->assertChildOrder(['four', 'new1', 'new2', 'two'], $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(['four', 'new1', 'new2', 'two'], $node);
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderBeforeUpAndRefresh()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);

        $this->node->orderBefore('three', 'two');
        $this->assertChildOrder(['one', 'three', 'two'], $this->node);

        $this->assertTrue($this->session->hasPendingChanges());
        $this->session->refresh(false);
        $this->assertFalse($this->node->isModified());
        $this->assertFalse($this->session->hasPendingChanges());

        $this->assertChildOrder(['one', 'two', 'three'], $this->node);
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderBeforeUpAndRefreshKeepChanges()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $path = $this->node->getPath();

        $othersession = self::$loader->getSession();
        $othernode = $othersession->getNode($path);

        $othernode->addNode('other1');
        $othernode->addNode('other2');
        $othernode->addNode('other-last');
        $othernode->orderBefore('other1', 'one');
        $othernode->orderBefore('other2', 'one');
        $this->assertChildOrder(['other1', 'other2', 'one', 'two', 'three', 'other-last'], $othernode);
        $othersession->save();
        $this->assertChildOrder(['other1', 'other2', 'one', 'two', 'three', 'other-last'], $othernode);

        $this->node->addNode('new');
        $this->node->orderBefore('three', 'two');
        $this->node->orderBefore('new', 'three');
        $this->assertChildOrder(['one', 'new', 'three', 'two'], $this->node);

        $this->session->refresh(true);

        $this->assertChildOrder(['other1', 'other2', 'one', 'new', 'three', 'other-last', 'two'], $this->node);
        $this->session->save();
        $this->assertChildOrder(['other1', 'other2', 'one', 'new', 'three', 'other-last', 'two'], $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(['other1', 'other2', 'one', 'new', 'three', 'other-last', 'two'], $node);
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderBeforeAndDeleteAndRefreshKeepChanges()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $path = $this->node->getPath();

        $this->node->addNode('new');
        $this->node->addNode('newfirst');
        $this->node->getNode('three')->remove();
        $this->node->orderBefore('newfirst', 'two');
        $this->node->orderBefore('four', 'two');
        $this->node->getNode('one')->remove();
        $this->assertChildOrder(['newfirst', 'four', 'two', 'new'], $this->node);

        $this->session->refresh(true);
        $this->assertChildOrder(['newfirst', 'four', 'two', 'new'], $this->node);
        $this->session->save();
        $this->assertChildOrder(['newfirst', 'four', 'two', 'new'], $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(['newfirst', 'four', 'two', 'new'], $node);
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderOnAdd()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $path = $this->node->getPath();

        $this->node->addNode('new');
        $this->assertChildOrder(['one', 'two', 'three', 'new'], $this->node);

        $this->session->save();
        $this->assertChildOrder(['one', 'two', 'three', 'new'], $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(['one', 'two', 'three', 'new'], $node);
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderOnMultipleAdds()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $path = $this->node->getPath();

        $this->node->addNode('new1');
        $this->node->addNode('new2');
        $this->assertChildOrder(['one', 'two', 'three', 'new1', 'new2'], $this->node);

        $this->session->save();
        $this->assertChildOrder(['one', 'two', 'three', 'new1', 'new2'], $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(['one', 'two', 'three', 'new1', 'new2'], $node);
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderOnMultipleAddsAndDelete()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $path = $this->node->getPath();

        $this->node->addNode('new1');
        $this->node->addNode('new2');
        $this->node->getNode('two')->remove();

        $this->assertChildOrder(['one', 'three', 'new1', 'new2'], $this->node);

        $this->session->save();
        $this->assertChildOrder(['one', 'three', 'new1', 'new2'], $this->node);

        $this->session = $this->renewSession();

        $node = $this->session->getNode($path);
        $this->assertChildOrder(['one', 'three', 'new1', 'new2'], $node);
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderNamespaces()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);
        $path = $this->node->getPath();

        $this->assertChildOrder(['jcr:one', 'one', 'sv:one'], $this->node);

        $this->node->orderBefore('sv:one', 'one');

        $this->assertChildOrder(['jcr:one', 'sv:one', 'one'], $this->node);

        $this->session->save();
        $this->assertChildOrder(['jcr:one', 'sv:one', 'one'], $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(['jcr:one', 'sv:one', 'one'], $node);
    }

    /**
     * @see NodeInterface::orderBefore.
     */
    public function testNodeOrderAfterOrderAndMove()
    {
        $this->assertInstanceOf(NodeInterface::class, $this->node);

        $src = '/tests_write_manipulation_move/testNodeOrderAfterOrderAndMove/src';
        $dst = '/tests_write_manipulation_move/testNodeOrderAfterOrderAndMove/dst';

        $srcParentNode = $this->session->getNode($src);
        $dstParentNode = $this->session->getNode($dst);

        $srcParentNode->orderBefore('three', 'two');
        $dstParentNode->orderBefore('three', 'two');

        $this->session->move($src.'/three', $dst.'/moved-three');
        $dstNode = $this->session->getNode($dst.'/moved-three');
        $this->assertInstanceOf(NodeInterface::class, $dstNode);

        $dstParentNode = $this->session->getNode($dst);
        $this->assertChildOrder(['one', 'three', 'two', 'four', 'moved-three'], $dstParentNode);

        $srcParentNode = $this->session->getNode($src);
        $this->assertChildOrder(['one', 'two', 'four'], $srcParentNode);

        $this->session->save();

        $session = $this->renewSession();

        $dstNode = $session->getNode($dst.'/moved-three');
        $this->assertInstanceOf(NodeInterface::class, $dstNode);

        $dstParentNode = $session->getNode($dst);
        $this->assertChildOrder(['one', 'three', 'two', 'four', 'moved-three'], $dstParentNode);

        $srcParentNode = $session->getNode($src);
        $this->assertChildOrder(['one', 'two', 'four'], $srcParentNode);
    }
}
