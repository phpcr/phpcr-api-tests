<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\SameNameSiblings;

use PHPCR\NodeInterface;
use PHPCR\Test\BaseCase;

/**
 * Test for deleting same name siblings (SNS).
 *
 * At this point, we are only testing for the ability to delete existing SNS;
 * creating or manipulating them is not supported.
 */
class DeleteMethodsTest extends BaseCase
{
    public static function setupBeforeClass($fixtures = '22_SameNameSiblings/delete'): void
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp(): void
    {
        $this->renewSession(); // get rid of cache from previous tests

        parent::setUp();
    }

    /**
     * Call session->removeItem() with multiple items before session->save().
     */
    public function testRemoveItemMultiple()
    {
        $parentPath = '/tests_write_manipulation_delete_sns/testRemoveSnsBySession';
        $childNames = ['child', 'child[2]', 'child[3]'];

        $parent = $this->getParentNode($this->session, $parentPath);

        foreach ($childNames as $childName) {
            $this->assertTrue($parent->hasNode($childName));
            $this->session->removeItem($parentPath.'/'.$childName);
            $this->assertFalse($parent->hasNode($childName), 'Node was not removed');
        }

        $this->saveAndRenewSession();

        foreach ($childNames as $childName) {
            $this->assertFalse($this->session->nodeExists($parentPath.'/'.$childName));
        }
    }

    /**
     * Call node->remove() with multiple items before session->save().
     *
     * @see ItemInterface::remove
     */
    public function testRemoveNode()
    {
        $parentPath = '/tests_write_manipulation_delete_sns/testRemoveSnsByNode';
        $childNames = ['child', 'child[2]', 'child[3]'];

        $parent = $this->getParentNode($this->session, $parentPath);

        foreach ($childNames as $childName) {
            $this->assertTrue($parent->hasNode($childName));
            $child = $parent->getNode($childName);
            $this->assertInstanceOf('PHPCR\NodeInterface', $child);
            $child->remove();
            $this->assertFalse($parent->hasNode($childName), 'Node was not removed');
        }

        $this->saveAndRenewSession();

        foreach ($childNames as $childName) {
            $this->assertFalse($this->session->nodeExists($parentPath.'/'.$childName));
        }
    }

    /**
     * Delete 6 nodes from a set of 12, and check that the correct ones remain
     * (using a property to track individual nodes as they get renamed).
     */
    public function testDeleteManyNodes()
    {
        $parentPath = '/tests_write_manipulation_delete_sns/testRemoveManyNodes';
        $childrenAtStart = [
            'child'     => '1',
            'child[2]'  => '2',
            'child[3]'  => '3',
            'child[4]'  => '4',
            'child[5]'  => '5',
            'child[6]'  => '6',
            'child[7]'  => '7',
            'child[8]'  => '8',
            'child[9]'  => '9',
            'child[10]' => '10',
            'child[11]' => '11',
            'child[12]' => '12',
        ];

        $childrenToDelete = [
            'child',
            'child[2]',
            'child[3]',
            'child[6]',
            'child[10]',
            'child[11]',
        ];

        $childrenAtEnd = [
            'child'     => '4',
            'child[2]'  => '5',
            'child[3]'  => '7',
            'child[4]'  => '8',
            'child[5]'  => '9',
            'child[6]'  => '12',
        ];

        $parent = $this->getParentNode($this->session, $parentPath);

        foreach ($childrenAtStart as $childName => $childNumber) {
            $this->assertTrue($parent->hasNode($childName), "Child $childNumber not found.");
        }

        foreach ($childrenToDelete as $childName) {
            $this->session->removeItem($parentPath.'/'.$childName);
            $this->assertFalse($parent->hasNode($childName), 'Node was not removed');
        }

        $this->saveAndRenewSession();

        $parent = $this->session->getNode($parentPath);
        $this->assertCount(count($childrenAtEnd), $parent->getNodes());

        $childValue = -1;
        foreach ($parent->getNodes() as $node) {
            $childValue = (-1 === $childValue) ? current($childrenAtEnd) : next($childrenAtEnd);
            $childKey = key($childrenAtEnd);
            $this->assertEquals($parentPath.'/'.$childKey, $node->getPath());
            $this->assertEquals($childValue, $node->getProperty('childNumber')->getValue());
        }
    }

    /**
     * @param $session
     * @param $parentPath
     *
     * @return mixed
     */
    private function getParentNode($session, $parentPath)
    {
        $parent = $session->getNode($parentPath);
        $this->assertInstanceOf(NodeInterface::class, $parent);

        return $parent;
    }
}
