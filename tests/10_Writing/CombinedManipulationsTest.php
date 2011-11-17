<?php
namespace PHPCR\Tests\Writing;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

use PHPCR\PropertyType as Type;

/**
 * test sequences of adding / moving / removing stuff inside a transaction
 */
class CombinedManipulationsTest extends \PHPCR\Test\BaseCase
{
    static public function setupBeforeClass($fixtures = '10_Writing/combinedmanipulations')
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
     * remove a node and then add a new one at the same path
     *
     * the old should disappear and a new one appear in place
     */
    public function testRemoveAndAdd()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node->getNode('child');
        $path = $node->getPath();
        $parentpath = $this->node->getPath();
        $this->assertInstanceOf('PHPCR\NodeType\NodeTypeInterface', $node->getPrimaryNodeType());
        $this->assertSame('nt:unstructured', $node->getPrimaryNodeType()->getName());

        $node->remove();
        $this->assertFalse($session->nodeExists($path));
        $this->assertFalse($this->node->hasNode('child'));
        $newnode = $this->node->addNode('child', 'nt:folder');

        $this->assertTrue($session->nodeExists($path));
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
     * remove a node and then move another node at the same path
     */
    public function testRemoveAndMove()
    {
        $session = $this->sharedFixture['session'];
        $node = $session->getNode($this->node->getPath().'/parent/child');
        $path = $node->getPath();
        $this->assertInstanceOf('PHPCR\NodeType\NodeTypeInterface', $node->getPrimaryNodeType());
        $this->assertSame('nt:unstructured', $node->getPrimaryNodeType()->getName());

        $node->remove();
        $this->assertFalse($session->nodeExists($path));
        $session->move($this->node->getPath().'/other', $path);
        $this->assertTrue($session->nodeExists($path));
        $parent = $this->node->getNode('parent');
        $this->assertTrue($parent->hasNode('child'));

        $session = $this->saveAndRenewSession();

        $node = $session->getNode($path);
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertSame('nt:folder', $node->getPrimaryNodeType()->getName());
        $this->assertFalse($node->hasNodes());
    }

    /*
     * TODO: add more combined manipulations:
     * move a not yet loaded node, then load it with the old path -> fail. with new path -> get it
     * same with moving child nodes not yet loaded and calling Node::getChildren. and loaded as well.
     * Test if order of write operations to backend is correct in larger batches. if i have
     * /some/path/parent/node and set the path of "parent" to /some/other/path/parent and in the same session change the path of node to /some/path/parent/something/node, result will depend on the order.
     * if you first move parent, then node, node ends up at the expected path.
     * if you first move node, then parent, node will end up in /some/other/path/parent/something/node, because a node is moved with all its children.
     *
     * what happens on save() for move /a/b/c, /a, remove /a/b? and what if we have /a/c and /a/b/c and want to remove /a/c, move /a/b/c, /a
     */

    public function testSessionHasPendingChanges()
    {
        $session = $this->sharedFixture['session'];
        $this->assertFalse($session->hasPendingChanges());
        $this->node->setProperty('prop', "New");
        $this->assertTrue($session->hasPendingChanges());
    }

    public function testSimpleSessionRefresh()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node;

        $node->setProperty('prop', 'New');
        $this->assertEquals('New', $node->getPropertyValue('prop'));

        $othersession = self::$loader->getSession();
        $othernode = $othersession->getNode($node->getPath());
        $othernode->setProperty('prop', 'Other');
        $othernode->setProperty('newprop', 'Test');
        $othersession->save();

        $session->refresh(false);
        $this->assertEquals('Other', $node->getPropertyValue('prop'));
        $this->assertTrue($node->hasProperty('newprop'));
        $this->assertEquals('Test', $node->getPropertyValue('newprop'));
    }

    public function testSimpleSessionRefreshKeepChanges()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node;

        $node->setProperty('prop', 'New');
        $this->assertEquals('New', $node->getPropertyValue('prop'));

        $othersession = self::$loader->getSession();
        $othernode = $othersession->getNode($node->getPath());
        $othernode->setProperty('prop', 'Other');
        $othernode->setProperty('newprop', 'Test');
        $othersession->save();

        $session->refresh(true);
        $this->assertEquals('New', $node->getPropertyValue('prop'));
        $this->assertTrue($node->hasProperty('newprop'));
        $this->assertEquals('Test', $node->getPropertyValue('newprop'));
    }

    public function testRemoveSessionRefresh()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node;

        $node->setProperty('prop', null);
        $this->assertFalse($node->hasProperty('prop'));
        $child = $node->getNode('child');
        $child->remove();
        $this->assertFalse($node->hasNode('child'));
        $this->assertFalse($session->nodeExists($node->getPath() . '/child'));

        $session->refresh(false);
        $this->assertEquals('Old', $node->getPropertyValue('prop'));
        $this->assertTrue($node->hasNode('child'));
        $this->assertTrue($session->nodeExists($node->getPath() . '/child'));
        $this->assertSame($child, $session->getNode($node->getPath() . '/child'));
    }

    public function testRemoveSessionRefreshKeepChanges()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node;

        $node->setProperty('prop', null);
        $this->assertFalse($node->hasProperty('prop'));
        $child = $node->getNode('child');
        $child->remove();
        $this->assertFalse($node->hasNode('child'));
        $this->assertFalse($session->nodeExists($node->getPath() . '/child'));

        $session->refresh(true);
        $this->assertEquals('Old', $node->getPropertyValue('prop'));
        $this->assertFalse($node->hasNode('child'));
        $this->assertFalse($session->nodeExists($node->getPath() . '/child'));
    }

    /**
     * remove a child node and a property in a different session. should
     * disappear on refresh, even if we want to keep changes
     */
    public function testRemoveOtherSessionRefreshKeepChanges()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node;
        $childprop = $this->sharedFixture['session']->getProperty($node->getPath().'/child/childprop');

        $othersession = self::$loader->getSession();
        $othernode = $othersession->getNode($node->getPath());
        $othernode->setProperty('prop', null);
        $othernode->getNode('child')->remove();
        $othersession->save();

        $childprop->refresh(true);
        try {
            $childprop->getValue();
            $this->fail('Should not be possible to get the value of a deleted property');
        } catch(\Exception $e) {
            //expected
        }
        $session->refresh(true);
        $this->assertFalse($node->hasProperty('prop'));
        $this->assertFalse($node->hasNode('child'));
    }

    public function testMoveSessionRefresh()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node;
        $child = $node->getNode('src/child');

        $session->move($node->getPath() . '/src/child', $node->getPath() . '/target/childnew');

        $this->assertFalse($session->nodeExists($node->getPath() . '/src/child'));
        $this->assertTrue($session->nodeExists($node->getPath() . '/target/childnew'));

        $session->refresh(false);

        $this->assertTrue($session->nodeExists($node->getPath() . '/src/child'));
        $this->assertFalse($session->nodeExists($node->getPath() . '/target/childnew'));
        $this->assertEquals($node->getPath() . '/src/child', $child->getPath());
        $src = $node->getNode('src');
        $this->assertTrue($src->hasNode('child'));
        $this->assertSame($child, $src->getNode('child'));
        $target = $node->getNode('target');
        $this->assertFalse($target->hasNode('childnew'));
    }

    public function testMoveSessionRefreshKeepChanges()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node;
        $child = $node->getNode('src/child');

        $session->move($node->getPath() . '/src/child', $node->getPath() . '/target/childnew');

        $this->assertFalse($session->nodeExists($node->getPath() . '/src/child'));
        $this->assertTrue($session->nodeExists($node->getPath() . '/target/childnew'));

        $session->refresh(true);

        $this->assertFalse($session->nodeExists($node->getPath() . '/src/child'));
        $this->assertTrue($session->nodeExists($node->getPath() . '/target/childnew'));
        $this->assertEquals($node->getPath() . '/target/childnew', $child->getPath());
        $src = $node->getNode('src');
        $this->assertFalse($src->hasNode('child'));
        $target = $node->getNode('target');
        $this->assertTrue($target->hasNode('childnew'));
        $this->assertSame($child, $target->getNode('childnew'));
    }

    public function testAddSessionRefresh()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node;
        $child = $node->addNode('child');
        $property = $node->setProperty('prop', 'Test');

        $session->refresh(false);

        $this->assertFalse($node->hasNode('child'));
        $this->assertFalse($node->hasProperty('prop'));

        $this->assertFalse($session->nodeExists($node->getPath().'/child'));
        $this->assertFalse($session->propertyExists($node->getPath().'/prop'));

        $this->setExpectedException('\PHPCR\PathNotFoundException');
        $node->getNode('child');
    }

    public function testAddSessionRefreshKeepChanges()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node;
        $child = $node->addNode('child');
        $property = $node->setProperty('prop', 'Test');

        $session->refresh(true);

        $this->assertTrue($node->hasNode('child'));
        $this->assertTrue($node->hasProperty('prop'));

        $this->assertTrue($session->nodeExists($node->getPath().'/child'));
        $this->assertTrue($session->propertyExists($node->getPath().'/prop'));

        $node->getNode('child');
    }

    public function testNodeRefresh()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node;

        $child = $node->addNode('child');
        $property = $node->setProperty('other', 'Test');
        $existingProperty = $node->setProperty('prop', 'New');

        $node->refresh(false);

        $this->assertSame($existingProperty, $node->getProperty('prop'));
        $this->assertSame('Old', $existingProperty->getValue());
        $this->assertFalse($node->hasNode('child'));
        $this->assertFalse($node->hasProperty('other'));
        $this->assertFalse($session->nodeExists($node->getPath().'/child'));
        $this->assertFalse($session->propertyExists($node->getPath().'/other'));

        $this->setExpectedException('\PHPCR\PathNotFoundException');
        $node->getPropertyValue('other');
    }

    public function testNodeRefreshKeepChanges()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node;

        $child = $node->addNode('child');
        $property = $node->setProperty('other', 'Test');
        $existingProperty = $node->setProperty('prop', 'New');

        $node->refresh(true);

        $this->assertSame($existingProperty, $node->getProperty('prop'));
        $this->assertSame('New', $existingProperty->getValue());
        $this->assertTrue($node->hasNode('child'));
        $this->assertTrue($node->hasProperty('other'));
        $this->assertTrue($session->nodeExists($node->getPath().'/child'));
        $this->assertTrue($session->propertyExists($node->getPath().'/other'));

        $this->assertSame($property, $node->getProperty('other'));
    }
}
