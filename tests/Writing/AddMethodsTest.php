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

use DateTime;
use InvalidArgumentException;
use PHPCR\ItemExistsException;
use PHPCR\NodeInterface;
use PHPCR\NodeType\ConstraintViolationException;
use PHPCR\NodeType\NoSuchNodeTypeException;
use PHPCR\PathNotFoundException;
use PHPCR\PropertyType;
use PHPCR\RepositoryException;
use PHPCR\RepositoryInterface;
use PHPCR\Test\BaseCase;
use PHPCR\ValueFormatException;

/**
 * Covering jcr-283 spec $10.4.
 */
class AddMethodsTest extends BaseCase
{
    public static function setupBeforeClass($fixtures = '10_Writing/add'): void
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp(): void
    {
        $this->renewSession();
        parent::setUp();
        //all tests in this suite rely on the trick to have the node populated from the fixtures
        $this->assertInstanceOf(NodeInterface::class, $this->node, 'Something went wrong with fixture loading');
    }

    public function testAddNode()
    {
        $this->markTestSkipped('TODO: Find a case where the parent type specifies the type for this node'); //with nt:folder, this is also not working with the java jackrabbit, so it seems not to be an implementation issue
        // should take the primaryType

        $new = $this->node->addNode('newNode');
        $this->assertNotNull($this->session->getNode($this->node->getPath().'/newNode'), 'Node newNode was not created');
        $this->session->save();
        $this->assertFalse($new->isNew(), 'Node was not saved');

        $this->renewSession();

        $this->assertNotNull($this->session->getNode($this->node->getPath().'/newNode'), 'Node newNode was not properly saved');
    }

    public function testAddNodeWithPath()
    {
        $new = $this->node->addNode('test:namespacedNode/newNode', 'nt:unstructured');
        $this->assertNotNull($this->session->getNode($this->node->getPath().'/test:namespacedNode/newNode'), 'Node newNode was not created');
        $this->session->save();
        $this->assertFalse($new->isNew(), 'Node was not saved');

        $this->renewSession();

        $this->assertNotNull($this->session->getNode($this->node->getPath().'/test:namespacedNode/newNode'), 'Node newNode was not properly saved');
    }

    public function testAddNodeFileType()
    {
        $path = $this->node->getPath();
        $newNode = $this->node->addNode('newFileNode', 'nt:file');
        $contentNode = $newNode->addNode('jcr:content', 'nt:resource');
        $contentNode->setProperty('jcr:mimeType', 'text/plain', PropertyType::STRING);
        $contentNode->setProperty('jcr:data', 'Hello', PropertyType::BINARY);
        $contentNode->setProperty('jcr:lastModified', new DateTime('2010-12-12'), PropertyType::DATE);

        $this->assertNotNull($newNode, 'Node newFileNode was not created');
        $this->assertTrue($newNode->isNew(), 'Node newFileNode is not marked dirty');
        $this->session->save();
        $this->assertFalse($newNode->isNew(), 'Node newFileNode was not saved');

        $this->renewSession();

        $newNode = $this->session->getNode($path.'/newFileNode');
        $this->assertNotNull($newNode, 'Node newFileNode was not created');
        $this->assertEquals('nt:file', $newNode->getPrimaryNodeType()->getName(), 'Node newFileNode was not created');
        $lastModified = $newNode->getNode('jcr:content')->getPropertyValue('jcr:lastModified');
        $this->assertInstanceOf(DateTime::class, $lastModified);
        $this->assertEquals('2010-12-12', $lastModified->format('Y-m-d'));
    }

    public function testAddNodeUnstructuredType()
    {
        $new = $this->node->addNode('newUnstructuredNode', 'nt:unstructured');
        $this->assertNotNull($this->session->getNode($this->node->getPath().'/newUnstructuredNode'), 'Node newUnstructuredNode was not created');
        $this->session->save();
        $this->assertFalse($new->isNew(), 'Node was not saved');

        $this->renewSession();

        $this->assertNotNull($this->session->getNode($this->node->getPath().'/newUnstructuredNode'), 'Node newUnstructuredNode was not created');
    }

    public function testAddNodeNoNameException()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->node->addNode(null, 'nt:folder');
    }

    public function testAddNodeAutoNamedEmptyNamehint()
    {
        $node = $this->node->getNode('jcr:content');
        $new = $node->addNodeAutoNamed('', 'nt:unstructured');
        $this->assertInstanceOf(NodeInterface::class, $new);
        $nodes = $node->getNodes();
        $this->assertCount(1, $nodes);
        $newnode = $nodes->current();
        $name = $newnode->getName();
        $this->assertEquals(0, substr_count(':', $name));

        $this->session->save();
        $this->assertFalse($new->isNew(), 'Node was not saved');

        $this->renewSession();

        $this->assertNotNull($this->session->getNode($this->node->getPath().'/jcr:content/'.$name), 'Node newNode was not properly saved');
    }

    public function testAddNodeAutoNamedNullNamehint()
    {
        $node = $this->node->getNode('jcr:content');
        $new = $node->addNodeAutoNamed(null, 'nt:unstructured');
        $this->assertInstanceOf(NodeInterface::class, $new);
        $nodes = $node->getNodes();
        $this->assertCount(1, $nodes);
        $newnode = $nodes->current();
        $name = $newnode->getName();

        $this->session->save();
        $this->assertFalse($new->isNew(), 'Node was not saved');

        $this->renewSession();

        $this->assertNotNull($this->session->getNode($this->node->getPath().'/jcr:content/'.$name), 'Node newNode was not properly saved');
    }

    public function testAddNodeAutoNamedValidNamespaceNamehint()
    {
        $node = $this->node->getNode('jcr:content');
        $new = $node->addNodeAutoNamed('jcr:', 'nt:unstructured');
        $this->assertInstanceOf(NodeInterface::class, $new);
        $nodes = $node->getNodes();
        $this->assertCount(1, $nodes);
        $newnode = $nodes->current();
        $name = $newnode->getName();
        $this->assertEquals('jcr:', substr($name, 0, 4));

        $this->session->save();
        $this->assertFalse($new->isNew(), 'Node was not saved');

        $this->renewSession();

        $this->assertNotNull($this->session->getNode($this->node->getPath().'/jcr:content/'.$name), 'Node newNode was not properly saved');
    }

    public function testAddPropertyOnUnstructured()
    {
        $path = $this->node->getPath();
        $node = $this->node->addNode('unstructuredNode', 'nt:unstructured');
        $node->setProperty('testprop', 'val');
        $node->setProperty('refprop', $this->node->getNode('ref'));

        $this->session->save();
        $this->assertFalse($node->isNew(), 'Node was not saved');

        $this->renewSession();
        $node = $this->session->getNode($path.'/unstructuredNode');

        $this->assertNotNull($node, 'Node was not created');
        $this->assertEquals('val', $node->getPropertyValue('testprop'), 'Property was not saved correctly');

        $node->setProperty('test2', 'val2');

        $this->session->save();
        $this->assertFalse($node->isNew(), 'Node was not saved');
        $this->assertFalse($node->getProperty('test2')->isNew(), 'Property was not saved');
        $this->renewSession();
        $node = $this->session->getNode($path.'/unstructuredNode');

        $this->assertEquals('val2', $node->getPropertyValue('test2'), 'Property was not added correctly');
    }

    public function testAddMultiValuePropertyOnUnstructured()
    {
        $path = $this->node->getPath();

        $node = $this->node->addNode('unstructuredNode2', 'nt:unstructured');
        $node->setProperty('test', ['val', 'val2']);

        $this->session->save();
        $this->assertFalse($node->isNew(), 'Node was not saved');

        $this->renewSession();
        $node = $this->session->getNode($path.'/unstructuredNode2');

        $this->assertNotNull($node, 'Node was not created');
        $this->assertEquals(['val', 'val2'], $node->getPropertyValue('test'), 'Property was not saved correctly');

        $node->setProperty('test2', ['val3', 'val4']);

        $this->session->save();
        $this->assertFalse($node->isNew(), 'Node was not saved');
        $this->assertFalse($node->getProperty('test2')->isNew(), 'Property was not saved');
        $this->renewSession();
        $node = $this->session->getNode($path.'/unstructuredNode2');

        $this->assertEquals(['val3', 'val4'], $node->getPropertyValue('test2'), 'Property was not added correctly');
    }

    public function testAddNodeMissingType()
    {
        $this->expectException(ConstraintViolationException::class);

        $this->node->addNode('newNode');
    }


    public function testAddNodeIllegalType()
    {
        $this->expectException(ConstraintViolationException::class);

        $this->node->addNode('newNode', 'nt:unstructured');
        $this->saveAndRenewSession();
    }

    public function testAddNodeWithInexistingType()
    {
        $this->expectException(NoSuchNodeTypeException::class);

        $this->node->addNode('newFileNode', 'inexistenttype');
    }

    /**
     * Test adding an already existing child.
     *
     * nt:folder do not allow same-name siblings
     */
    public function testAddNodeExisting()
    {
        $this->expectException(ItemExistsException::class);

        $this->node->addNode('child', 'nt:file');
    }

    /**
     * Tests adding the same child to the same node in 2 different sessions.
     */
    public function testAddNodeInParallel()
    {
        $this->expectException(ItemExistsException::class);

        $path = $this->node->getPath();

        $session1 = $this->session;
        $session2 = self::$loader->getSession();

        $node1 = $session1->getNode($path);
        $c1 = $node1->addNode('test', 'nt:file');
        $c1->addNode('jcr:content', 'nt:unstructured');
        $node2 = $session2->getNode($path);
        $c2 = $node2->addNode('test', 'nt:file');
        $c2->addNode('jcr:content', 'nt:unstructured');

        $session1->save();
        $session2->save();
    }

    /**
     * try to add a node below a not existing node.
     */
    public function testAddNodePathNotFound()
    {
        $this->expectException(PathNotFoundException::class);

        $this->node->addNode('nonExistent/newNode', 'nt:unstructured');
    }

    /**
     * try to add a node below a property.
     */
    public function testAddNodeToProperty()
    {
        $this->expectException(ConstraintViolationException::class);

        $this->node->addNode('prop/failNode', 'nt:unstructured');
    }

    /**
     * try to add a property of the wrong type.
     */
    public function testAddPropertyWrongType()
    {
        $file = $this->node->addNode('file', 'nt:file');
        $data = $file->addNode('jcr:content', 'nt:resource');
        try {
            $data->setProperty('jcr:lastModified', true);
            $this->saveAndRenewSession();
        } catch (ValueFormatException $e) {
            //correct according to JSR-287 3.6.4 Property Type Conversion
            return;
        } catch (ConstraintViolationException $e) {
            //also correct
            return;
        }
        $this->fail('Expected PHPCR\\NodeType\\ConstraintViolationException or PHPCR\\ValueFormatException');
    }

    public function testAddNodeWithIndex()
    {
        $this->expectException(RepositoryException::class);

        $this->node->addNode('name[3]', 'nt:unstructured');
    }

    /**
     * Add a node and a child node to it.
     */
    public function testAddNodeChild()
    {
        $newNode = $this->node->addNode('parent', 'nt:unstructured');
        $newChild = $newNode->addNode('child', 'nt:unstructured');
        $path = $newChild->getPath();

        $this->assertTrue($this->session->nodeExists('/tests_write_manipulation_add/testAddNodeChild/parent/child'), 'Child node not found [Session]');

        $this->session->save();
        $this->assertFalse($newChild->isNew(), 'Node was not saved');

        // dispatch to backend
        $session = $this->saveAndRenewSession();
        $this->assertTrue($session->nodeExists($path));
        $this->assertInstanceOf(NodeInterface::class, $session->getNode($path));
    }

    /**
     * Add a node and a child node with some properties.
     */
    public function testAddNodeChildProperties()
    {
        $parent = $this->node->addNode('parent', 'nt:folder');
        $child = $parent->addNode('child', 'nt:file');
        $content = $child->addNode('jcr:content', 'nt:resource');
        $content->setProperty('jcr:data', '1234', PropertyType::BINARY);
        $path = $child->getPath();

        $this->saveAndRenewSession();

        $child = $this->session->getNode($path);
        $this->assertInstanceOf(NodeInterface::class, $child);
    }

    /**
     * try to add a node with an unregistered namespace.
     */
    public function testAddNodeWithUnregisteredNamespace()
    {
        $this->expectException(RepositoryException::class);

        $namespace = 'testUnregisteredNamespace';
        $nodeName =  'child';

        //add the node with an unregistered namespace, should throw a RepositoryException
        $this->node->addNode($namespace.':'.$nodeName, 'nt:unstructured');

        //save the changes
        $this->saveAndRenewSession();
    }

    /**
     * try to add a property with an unregistered namespace.
     */
    public function testAddPropertyWithUnregisteredNamespace()
    {
        $this->expectException(RepositoryException::class);

        $namespace = 'testUnregisteredNamespace';
        $propertyName =  'prop';

        //add a property with an unregistered namespace, should throw a RepositoryException
        $this->node->setProperty($namespace.':'.$propertyName, 'some value');

        //save the changes
        $this->saveAndRenewSession();
    }

    public function testAddNodeWithAutoCreatedNode()
    {
        if ($this->skipIfNotSupported(RepositoryInterface::NODE_TYPE_MANAGEMENT_AUTOCREATED_DEFINITIONS_SUPPORTED)) {
            return;
        }

        $workspace = $this->session->getWorkspace();
        $cnd = file_get_contents(__DIR__.'/../../fixtures/10_Writing/add_auto_create.cnd');
        $workspace->getNodeTypeManager()->registerNodeTypesCnd($cnd, true);
        $this->node->addNode('foo', 'test:testautocreate');
        $this->session->save();

        $childNode = $this->session->getNode($this->node->getPath().'/foo');
        $this->assertNotNull($childNode);

        $childNode = $this->session->getNode($this->node->getPath().'/foo/autocreated');
        $this->assertNotNull($childNode);
        $primaryType = $childNode->getPrimaryNodeType();
        $this->assertEquals('nt:unstructured', $primaryType->getName());

        $childNode = $this->session->getNode($this->node->getPath().'/foo/autocreatedwithchild');
        $this->assertNotNull($childNode);
        $primaryType = $childNode->getPrimaryNodeType();
        $this->assertEquals('test:testAutoCreateChild', $primaryType->getName());

        $childNode = $this->session->getNode($this->node->getPath().'/foo/autocreatedwithchild/foo');
        $this->assertNotNull($childNode);
        $primaryType = $childNode->getPrimaryNodeType();
        $this->assertEquals('nt:unstructured', $primaryType->getName());
    }
}
