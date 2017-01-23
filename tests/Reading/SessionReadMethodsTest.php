<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Reading;

use ArrayIterator;
use InvalidArgumentException;
use JavaException;
use PHPCR\ItemNotFoundException;
use PHPCR\NodeInterface;
use PHPCR\PathNotFoundException;
use PHPCR\PropertyInterface;
use PHPCR\RepositoryException;
use PHPCR\Test\BaseCase;

/**
 * Test Session read methods.
 *
 * exportSystemView, exportDocumentView are covered in chapter 7
 * getNamespacePrefix, getNamespacePrefixes, getNamespaceURI, setNamespacePrefix are covered in SessionNamespaceRemappingTest
 *
 * session write methods are covered in chapter 10
 * (hasPendingChanges, getValueFactory, move, refresh, removeItem, save)
 *
 * Retention: getRetentionManager
 * Access Control: getAccessControlManager
 */
class SessionReadMethodsTest extends BaseCase
{
    // 5.1.1
    public function testGetRootNode()
    {
        $node = $this->session->getRootNode();
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('/', $node->getPath());
    }

    public function testGetRootNodeRepositoryException()
    {
        $this->expectException(RepositoryException::class);

        $this->markTestIncomplete('TODO: Figure out how to test this');
    }

    // 5.1.3, 5.1.6
    public function testGetItem()
    {
        $node = $this->session->getItem('/tests_general_base');
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('tests_general_base', $node->getName());

        $node = $this->session->getItem('/tests_general_base/index.txt');
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('index.txt', $node->getName());

        $prop = $this->session->getItem('/tests_general_base/numberPropertyNode/jcr:content/foo');
        $this->assertInstanceOf(PropertyInterface::class, $prop);
        $this->assertEquals('foo', $prop->getName());
        $this->assertEquals('bar', $prop->getString());
        $prop = $this->session->getItem('/tests_general_base/numberPropertyNode/jcr:content/specialChars');
        $this->assertInstanceOf(PropertyInterface::class, $prop);
        $this->assertEquals('specialChars', $prop->getName());
        $this->assertEquals('üöäøéáñâêèàçæëìíîïþ', $prop->getString());
    }

    // 5.1.3, 5.1.6
    public function testGetNode()
    {
        $node = $this->session->getNode('/tests_general_base/numberPropertyNode');
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('numberPropertyNode', $node->getName());

        $node = $this->session->getNode('/tests_general_base/index.txt');
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('index.txt', $node->getName());
    }

    public function testGetNodes()
    {
        $nodes = $this->session->getNodes([
            '/tests_general_base',
            '/tests_general_base/numberPropertyNode',
            '/not_existing',
            '/tests_general_base/../not_existing',
        ]);

        $this->assertCount(2, $nodes);
        $this->assertTrue(isset($nodes['/tests_general_base']));
        $this->assertTrue(isset($nodes['/tests_general_base/numberPropertyNode']));

        foreach ($nodes as $key => $node) {
            $this->assertInstanceOf(NodeInterface::class, $node);
            $this->assertEquals($key, $node->getPath());
        }
    }

    /**
     * make sure getNodes works with a traversable object as well.
     */
    public function testGetNodesTraversable()
    {
        $nodes = $this->session->getNodes(new ArrayIterator([
            '/tests_general_base',
            '/tests_general_base/numberPropertyNode',
            '/not_existing',
            '/tests_general_base/../not_existing',
        ]));

        $this->assertCount(2, $nodes);
        $this->assertTrue(isset($nodes['/tests_general_base']));
        $this->assertTrue(isset($nodes['/tests_general_base/numberPropertyNode']));

        foreach ($nodes as $key => $node) {
            $this->assertInstanceOf(NodeInterface::class, $node);
            $this->assertEquals($key, $node->getPath());
        }
    }

    public function testGetNodesInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->session->getNodes('no iterable thing');
    }

    /**
     * Get something that is a property and not a node.
     */
    public function testGetNodeInvalid()
    {
        $this->expectException(PathNotFoundException::class);

        $this->session->getNode('/tests_general_base/idExample/jcr:primaryType');
    }

    /**
     * Get something that is a node and not a property.
     */
    public function testGetPropertyInvalid()
    {
        $this->expectException(PathNotFoundException::class);

        $this->session->getProperty('/tests_general_base/idExample');
    }

    //5.1.3, 5.1.6
    public function testGetProperty()
    {
        $prop = $this->session->getProperty('/tests_general_base/idExample/jcr:primaryType');
        $this->assertInstanceOf(PropertyInterface::class, $prop);
        $this->assertEquals('jcr:primaryType', $prop->getName());
        $this->assertEquals('nt:file', $prop->getString());
    }

    public function testGetProperties()
    {
        $properties = $this->session->getProperties([
            '/tests_general_base/jcr:primaryType',
            '/tests_general_base/numberPropertyNode/jcr:primaryType',
            '/not_existing/jcr:primaryType',
            '/tests_general_base/../not_existing/jcr:primaryType',
        ]);
        $this->assertCount(2, $properties);
        $this->assertTrue(isset($properties['/tests_general_base/jcr:primaryType']));
        $this->assertTrue(isset($properties['/tests_general_base/numberPropertyNode/jcr:primaryType']));
        foreach ($properties as $key => $property) {
            $this->assertInstanceOf(PropertyInterface::class, $property);
            $this->assertEquals($key, $property->getPath());
        }
    }

    public function testGetPropertiesTraversable()
    {
        $properties = $this->session->getProperties(new \ArrayIterator([
            '/tests_general_base/jcr:primaryType',
            '/tests_general_base/numberPropertyNode/jcr:primaryType',
            '/not_existing/jcr:primaryType',
            '/tests_general_base/../not_existing/jcr:primaryType',
        ]));
        $this->assertCount(2, $properties);
        $this->assertTrue(isset($properties['/tests_general_base/jcr:primaryType']));
        $this->assertTrue(isset($properties['/tests_general_base/numberPropertyNode/jcr:primaryType']));
        foreach ($properties as $key => $property) {
            $this->assertInstanceOf(PropertyInterface::class, $property);
            $this->assertEquals($key, $property->getPath());
        }
    }

    public function testGetPropertiesInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->session->getProperties('no iterable thing');
    }

    /**
     * it is forbidden to call getItem on the session with a relative path.
     */
    public function testGetItemRelativePathException()
    {
        $this->expectException(PathNotFoundException::class);

        $this->session->getItem('tests_general_base');
    }

    public function testGetItemPathNotFound()
    {
        $this->expectException(PathNotFoundException::class);

        $this->session->getItem('/foobarmooh');
    }

    public function testGetItemRepositoryException()
    {
        $this->expectException(RepositoryException::class);
        $this->session->getItem('//');
    }

     //5.1.2
    public function testItemExists()
    {
        $this->assertTrue($this->session->itemExists('/'));
        $this->assertTrue($this->session->itemExists('/tests_general_base'));
        $this->assertFalse($this->session->itemExists('/foobar'));
    }

    public function testItemExistsRelativePath()
    {
        $this->expectException(RepositoryException::class);

        $this->session->itemExists('tests_general_base');
    }

    public function testItemExistsInvalidPath()
    {
        $this->expectException(RepositoryException::class);

        $this->session->itemExists('//');
    }

    public function testNodeExists()
    {
        $this->assertTrue($this->session->nodeExists('/'));
        $this->assertTrue($this->session->nodeExists('/tests_general_base'));
        $this->assertFalse($this->session->nodeExists('/foobar'));
        //a property is not a node
        $this->assertFalse($this->session->nodeExists('/tests_general_base/numberPropertyNode/jcr:content/foo'));
    }

    public function testNodeExistsRelativePath()
    {
        $this->expectException(RepositoryException::class);

        $this->session->nodeExists('tests_general_base');
    }

    public function testNodeExistsInvalidPath()
    {
        $this->expectException(RepositoryException::class);

        $this->session->nodeExists('//');
    }

    public function testPropertyExists()
    {
        $this->assertTrue($this->session->propertyExists('/tests_general_base/numberPropertyNode/jcr:content/foo'));
        //a node is not a property
        $this->assertFalse($this->session->propertyExists('/tests_general_base'));
    }

    public function testPropertyExistsRelativePath()
    {
        $this->expectException(RepositoryException::class);

        $this->session->propertyExists('tests_general_base/numberPropertyNode/jcr:content/foo');
    }

    public function testPropertyExistsInvalidPath()
    {
        $this->expectException(RepositoryException::class);
        $this->session->propertyExists('//');
    }

    public function testGetNodeByIdentifier()
    {
        $node = $this->session->getNodeByIdentifier('842e61c0-09ab-42a9-87c0-308ccc90e6f4');
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('/tests_general_base/idExample', $node->getPath());
    }

    public function testGetNodesByIdentifier()
    {
        $nodes = (array) $this->session->getNodesByIdentifier([
            '842e61c0-09ab-42a9-87c0-308ccc90e6f4',
            '00000000-0000-0000-0000-000000000000',
            '13543fc6-1abf-4708-bfcc-e49511754b40',
        ]);

        $this->assertCount(2, $nodes);
        list($key, $node) = each($nodes);
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('/tests_general_base/idExample', $node->getPath());
        list($key, $node) = each($nodes);
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('/tests_general_base/idExample/jcr:content/weakreference_target', $node->getPath());
    }

    public function testGetNodesByIdentifierTraversable()
    {
        $nodes = (array) $this->session->getNodesByIdentifier(new ArrayIterator([
            '842e61c0-09ab-42a9-87c0-308ccc90e6f4',
            '00000000-0000-0000-0000-000000000000',
            '13543fc6-1abf-4708-bfcc-e49511754b40',
        ]));

        $this->assertCount(2, $nodes);
        list($key, $node) = each($nodes);
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('/tests_general_base/idExample', $node->getPath());
        list($key, $node) = each($nodes);
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('/tests_general_base/idExample/jcr:content/weakreference_target', $node->getPath());
    }

    public function testGetNodesByIdentifierInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->session->getNodesByIdentifier('not a traversable');
    }

    public function testGetNodeByIdentifierRepositoryException()
    {
        $this->expectException(RepositoryException::class);

        $this->session->getNodeByIdentifier('foo');
    }

    public function testGetNodeByIdentifierItemNotFoundException()
    {
        $this->expectException(ItemNotFoundException::class);

        $this->session->getNodeByIdentifier('00000000-0000-0000-0000-000000000000'); //FIXME: is the identifier format defined by the repository? how to generically get a valid but inexistent id?
    }

    /**
     * spec 4.3.
     */
    public function testImpersonate()
    {
        $this->expectException(JavaException::class);

        $cr = self::$loader->getRestrictedCredentials();
        $this->session->impersonate($cr);
        $this->markTestIncomplete('TODO: do some tests with the impersonated session');
    }

    //TODO: Write tests for LoginException and RepositoryException with impersonate

    // 4.4.4, 4.4.5
    public function testIsLiveLogout()
    {
        $ses = $this->assertSession();
        $this->assertTrue($ses->isLive());
        $ses->logout();
        $this->assertFalse($ses->isLive());
    }
}
