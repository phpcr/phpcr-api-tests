<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2013 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Writing;

use PHPCR\ItemNotFoundException;

/**
 * Covering jcr-2.8.3 spec $10.9.
 */
class DeleteMethodsTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = '10_Writing/delete')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        $this->renewSession(); // get rid of cache from previous tests
        parent::setUp();
    }

    /**
     * \PHPCR\SessionInterface::removeItem.
     */
    public function testRemoveItemNode()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $parent = $this->node->getParent();
        $this->assertTrue($parent->hasNode('testRemoveItemNode'));

        $this->session->removeItem($this->node->getPath());

        $this->assertFalse($parent->hasNode('testRemoveItemNode'), 'Node was not removed');
        $this->assertFalse($this->session->nodeExists($parent->getPath() . '/testRemoveItemNode'));

        $this->saveAndRenewSession();

        $this->assertFalse($this->session->nodeExists($parent->getPath() . '/testRemoveItemNode'));
    }

    /**
     * \PHPCR\SessionInterface::removeItem.
     */
    public function testRemoveItemProperty()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $property = $this->node->getProperty('longNumber');
        $this->assertTrue($this->node->hasProperty('longNumber'));
        $this->session->removeItem('/tests_write_manipulation_delete/testRemoveItemProperty/longNumber');
        $this->assertFalse($this->node->hasProperty('longNumber'));
    }

    /**
     * \PHPCR\SessionInterface::removeItem.
     *
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testRemoveNodeConstraintViolation()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $this->session->removeItem('/tests_write_manipulation_delete/testRemoveNodeConstraintViolation/jcr:content');
        $this->session->save();
    }

    /**
     * \PHPCR\SessionInterface::removeItem.
     *
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testRemoveItemNotExisting()
    {
        $this->session->removeItem('/not/existing');
    }

    /**
     * Check if state of cached parent node is updated correctly.
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
        $this->assertFalse($this->session->nodeExists($path));

        $this->saveAndRenewSession();

        $this->assertFalse($this->session->nodeExists($path));
    }

    public function testRemoveNodeWhitespace()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $node = $this->node->getNode('child whitespace');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);

        $path = $node->getPath();

        $node->remove();
        $this->saveAndRenewSession();

        $this->assertFalse($this->session->nodeExists($path));
    }

    /**
     * Check if state of parent that was not cached when delete was executed is correct.
     *
     * \PHPCR\ItemInterface::remove
     */
    public function testRemoveNodeParentState()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath() . '/parent/child';

        $child = $this->session->getNode($path);
        $child->remove();

        $parent = $this->session->getNode($this->node->getPath() . '/parent');
        $this->assertFalse($parent->hasNode('child'));
        $this->assertFalse($this->session->nodeExists($path));

        $session = $this->saveAndRenewSession();

        $this->assertFalse($session->nodeExists($path));
        $parent = $session->getNode($this->node->getPath() . '/parent');
        $this->assertFalse($parent->hasNode('child'));
    }

    /**
     * add a node, save it, remove it, save again, try to access the removed node.
     */
    public function testRemoveNodeFromBackend()
    {
        $nodename = 'toBeDeleted';
        if (!$this->rootNode->hasNode($nodename)) {
            $this->rootNode->addNode($nodename, 'nt:unstructured');
            $this->session->save();
            $this->renewSession();
        }

        $node = $this->session->getNode('/toBeDeleted');

        $node->remove();
        $this->session->save();

        $this->renewSession();

        $this->setExpectedException('\PHPCR\PathNotFoundException');
        $this->session->getNode('/toBeDeleted');
    }

    /**
     * add a property, save it, remove it, save again, try to access the removed property.
     */
    public function testRemovePropertyFromBackend()
    {
        $this->rootNode->setProperty('toBeDeletedProperty', 'TEMP');
        $this->saveAndRenewSession();

        $node = $this->session->getNode('/');
        $this->assertTrue($node->hasProperty('toBeDeletedProperty'), 'Property was not created');
        $this->assertEquals('TEMP', $node->getPropertyValue('toBeDeletedProperty'), 'wrong value');

        $node->getProperty('toBeDeletedProperty')->remove();
        $this->saveAndRenewSession();

        $this->setExpectedException('\PHPCR\PathNotFoundException');
        $this->session->getNode('/')->getProperty('toBeDeletedProperty');
    }

    /**
     * \PHPCR\PropertyInterface::remove
     * \PHPCR\PropertyInterface::setValue.
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
     * \PHPCR\NodeInterface::setProperty.
     */
    public function testNodeRemoveProperty()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $this->assertTrue($this->node->hasProperty('longNumber'));
        $this->node->setProperty('longNumber', null);
        $this->assertFalse($this->node->hasProperty('longNumber'));
        $this->assertFalse($this->session->itemExists('/tests_write_manipulation_delete/testNodeRemobeProperty/longNumber'));
    }

    /**
     * \PHPCR\NodeInterface::setProperty.
     */
    public function testNodeRemovePropertyNotExisting()
    {
        //        $this->node->setProperty('inexistent', null);
        $this->markTestIncomplete('TODO: figure out what should happen when inexistant property is removed');

        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
    }

    /**
     * \PHPCR\NodeInterface::setProperty.
     *
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testNodeRemovePropertyConstraintViolation()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $this->node->setProperty('jcr:created', null); //removes the property
        $this->session->save();
    }

    /**
     * \PHPCR\NodeInterface::setProperty.
     *
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testRemovePropertyConstraintViolation()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $this->node->setProperty('jcr:primaryType', null); //removes the property
        $this->session->save();
    }

    /**
     * \PHPCR\NodeInterface::remove
     * \PHPCR\SessionInterface::getNode.
     *
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetRemovedNodeSession()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $path = $this->node->getPath();
        $this->node->remove();
        $this->session->getNode($path);
    }

    /**
     * \PHPCR\NodeInterface::remove
     * \PHPCR\NodeInterface::getNode.
     *
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
     * \PHPCR\SessionInterface::getNode.
     *
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetRemovedPropertySession()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $property = $this->node->getProperty('prop');
        $path = $property->getPath();
        $property->remove();
        $this->session->getProperty($path);
    }

    /**
     * \PHPCR\NodeInterface::remove
     * \PHPCR\NodeInterface::getNode.
     *
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
     * try to remove a node that has already been removed in this session.
     *
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testRemoveRemovedNode()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $path = $this->node->getPath();
        $this->node->remove();
        $this->session->removeItem($path);
    }

    /**
     * Try to call revert on a property that has been removed in this session.
     *
     * @expectedException \PHPCR\InvalidItemStateException
     */
    public function testRevertRemovedProperty()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $property = $this->node->getProperty('longNumber');
        $this->node->setProperty('longNumber', null);
        $property->revert();
    }

    /**
     * deleting a node must cascade to its children.
     */
    public function testDeleteCascade()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

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
                $this->fail('Should not be able to get path of deleted item ' . $item->getPath()); // this should explode
            } catch (\PHPCR\InvalidItemStateException $e) {
                // the exception is expected
            }
        }

        $this->session->save();

        $this->assertFalse($this->session->nodeExists("$path/prop"));
        $this->assertFalse($this->session->nodeExists("$path/test"));
        $this->assertFalse($this->session->nodeExists("$path/child"));
        $this->assertFalse($this->session->nodeExists("$path/child/child"));
        $this->assertFalse($this->session->propertyExists("$path/prop"));
        $this->assertFalse($this->session->propertyExists("$path/child/prop"));
        $this->assertFalse($this->session->propertyExists("$path/child/child/prop"));

        foreach ($items as $item) {
            try {
                $this->fail('Should not be able to get path of deleted item ' . $item->getPath()); // this should explode
            } catch (\PHPCR\InvalidItemStateException $e) {
                // the exception is expected
            }
        }
    }

    /**
     * It is not allowed to delete a referenced node.
     *
     * @expectedException \PHPCR\ReferentialIntegrityException
     */
    public function testDeleteReferencedNodeException()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $destnode = $this->node->getNode('idExample');
        $destnode->remove();
        $this->session->save();
    }

    /**
     * however, if the reference is first deleted, it must be possible to
     * delete the node.
     */
    public function testDeletePreviouslyReferencedNode()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        // 2) Get the referencing property and delete it
        $sourceprop = $this->node->getProperty('reference');
        $sourceprop->remove();

        // 4) Load the previously referenced node and remove it
        $destnode = $this->node->getNode('idExample');

        $destnode->remove();
        $this->saveAndRenewSession();

        $this->assertFalse($this->node->hasNode($this->getName()));
    }

    /**
     * it must be possible to delete a weakly referenced node.
     */
    public function testDeleteWeakReferencedNode()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $destnode = $this->node->getNode('idExample');
        $destnode->remove();
        $this->saveAndRenewSession();

        $this->assertFalse($this->node->hasNode('idExample'));
    }

    /**
     * test if deleting a node and creating a node at the same path with a new UUID
     * won't cause trouble with internally cached UUID's.
     */
    public function testDeleteNodeAndReusePathWithReference()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        // 1. remove the idExample node with UUID cbc172b2-c317-44ac-a73b-1df61c35fb1a
        $referencedNode = $this->node->getNode('idExample');
        $path = $referencedNode->getPath();
        $uuid = $referencedNode->getIdentifier();

        $child = $referencedNode->getNode('idChild');
        $childUuid = $child->getIdentifier();

        $referencedNode->remove();
        try {
            $this->session->getNodeByIdentifier($uuid);
            $this->fail('Removed node was still found');
        } catch (ItemNotFoundException $e) {
            // expected
        }
        try {
            $this->session->getNodeByIdentifier($childUuid);
            $this->fail('Removed child node was still found');
        } catch (ItemNotFoundException $e) {
            // expected
        }

        $this->assertFalse($this->session->nodeExists($path));

        // 2. Save the session (without reloading)
        $this->session->save();
        $this->assertFalse($this->session->nodeExists($path));

        // 3. Recreate the node with a specific UUID
        $referencedNode = $this->node->addNode('idExample');
        $referencedNode->addMixin('mix:referenceable');
        $referencedNode->setProperty('jcr:uuid', '54378257-ca4d-4b9f-9383-f30dfb280977');

        $child = $referencedNode->addNode('idChild');
        $child->addMixin('mix:referenceable');
        $child->setProperty('jcr:uuid', 'eee78257-ca4d-4b9f-9383-f30dfb280977');

        // Node should be persisted before using it as a reference
        $this->session->save();

        // 4. Give the testNode a reference to the new idExample node
        $this->node->setProperty('reference', '54378257-ca4d-4b9f-9383-f30dfb280977', \PHPCR\PropertyType::REFERENCE);
        $this->node->setProperty('referenceChild', 'eee78257-ca4d-4b9f-9383-f30dfb280977', \PHPCR\PropertyType::REFERENCE);

        // 5. Throws an PHPCR\ReferentialIntegrityException when above UUID is not a valid reference
        $this->saveAndRenewSession();

        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $this->assertEquals('54378257-ca4d-4b9f-9383-f30dfb280977', $this->node->getProperty('reference')->getString(), 'Reference property should contain "54378257-ca4d-4b9f-9383-f30dfb280977" as string value');
        $this->assertEquals('eee78257-ca4d-4b9f-9383-f30dfb280977', $this->node->getProperty('referenceChild')->getString(), 'Reference property should contain "eee78257-ca4d-4b9f-9383-f30dfb280977" as string value');
        $this->assertEquals('54378257-ca4d-4b9f-9383-f30dfb280977', $this->node->getNode('idExample')->getIdentifier(), 'idExample node should have "54378257-ca4d-4b9f-9383-f30dfb280977" as UUID');
    }

    public function testWorkspaceDelete()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $workspace = $this->session->getWorkspace();
        $path = $this->node->getPath();

        $property = $this->node->getProperty('prop');
        $workspace->removeItem($path);

        // Session
        $this->assertFalse($this->session->nodeExists($path));
        $this->assertFalse($this->session->nodeExists($path . '/child'));
        $this->assertFalse($this->session->propertyExists($path . '/child/prop'));
        try {
            $this->node->getPath();
            $this->fail('Node was not notified that it is deleted');
        } catch (\PHPCR\InvalidItemStateException $e) {
            // success
        }
        try {
            $property->getValue();
            $this->fail('Property was not notified that it is deleted');
        } catch (\PHPCR\InvalidItemStateException $e) {
            // success
        }

        // Backend
        $this->session = $this->saveAndRenewSession();
        $this->assertFalse($this->session->nodeExists($path));
        $this->assertFalse($this->session->nodeExists($path . '/child'));
    }

    public function testWorkspaceDeleteProperty()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $workspace = $this->session->getWorkspace();
        $path = $this->node->getPath();
        $workspace->removeItem("$path/prop");

        // Session
        $this->assertFalse($this->session->propertyExists("$path/prop"));

        // Backend
        $session = $this->saveAndRenewSession();
        $this->assertFalse($session->propertyExists("$path/prop"));
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testWorkspaceDeleteNonExisting()
    {
        $workspace = $this->session->getWorkspace();
        $workspace->removeItem('/not/existing');
    }
}
