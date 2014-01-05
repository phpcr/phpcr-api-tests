<?php
namespace PHPCR\Tests\Reading;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Test Session read methods
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
class SessionReadMethodsTest extends \PHPCR\Test\BaseCase
{
    //5.1.1
    public function testGetRootNode()
    {
        $node = $this->session->getRootNode();
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals( '/', $node->getPath());
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetRootNodeRepositoryException()
    {
        $this->markTestIncomplete('TODO: Figure out how to test this');
    }

    //5.1.3, 5.1.6
    public function testGetItem()
    {
        $node = $this->session->getItem('/tests_general_base');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('tests_general_base', $node->getName());

        $node = $this->session->getItem('/tests_general_base/index.txt');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('index.txt', $node->getName());

        $prop = $this->session->getItem('/tests_general_base/numberPropertyNode/jcr:content/foo');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals('foo', $prop->getName());
        $this->assertEquals('bar', $prop->getString());
        $prop = $this->session->getItem('/tests_general_base/numberPropertyNode/jcr:content/specialChars');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals('specialChars', $prop->getName());
        $this->assertEquals('üöäøéáñâêèàçæëìíîïþ', $prop->getString());
    }

    //5.1.3, 5.1.6
    public function testGetNode()
    {
        $node = $this->session->getNode('/tests_general_base/numberPropertyNode');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('numberPropertyNode', $node->getName());

        $node = $this->session->getNode('/tests_general_base/index.txt');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('index.txt', $node->getName());
    }

    public function testGetNodes()
    {
        $nodes = $this->session->getNodes(array(
            '/tests_general_base',
            '/tests_general_base/numberPropertyNode',
            '/not_existing',
            '/tests_general_base/../not_existing',
        ));
        $this->assertCount(2, $nodes);
        $this->assertTrue(isset($nodes['/tests_general_base']));
        $this->assertTrue(isset($nodes['/tests_general_base/numberPropertyNode']));
        foreach ($nodes as $key => $node) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $node);
            $this->assertEquals($key, $node->getPath());
        }
    }

    /**
     * make sure getNodes works with a traversable object as well
     */
    public function testGetNodesTraversable()
    {
        $nodes = $this->session->getNodes(new \ArrayIterator(array(
            '/tests_general_base',
            '/tests_general_base/numberPropertyNode',
            '/not_existing',
            '/tests_general_base/../not_existing',
        )));
        $this->assertCount(2, $nodes);
        $this->assertTrue(isset($nodes['/tests_general_base']));
        $this->assertTrue(isset($nodes['/tests_general_base/numberPropertyNode']));
        foreach ($nodes as $key => $node) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $node);
            $this->assertEquals($key, $node->getPath());
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetNodesInvalidArgument()
    {
        $this->session->getNodes('no iterable thing');
    }

    /**
     * Get something that is a property and not a node
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetNodeInvalid()
    {
        $this->session->getNode('/tests_general_base/idExample/jcr:primaryType');
    }
    /**
     * Get something that is a node and not a property
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetPropertyInvalid()
    {
        $this->session->getProperty('/tests_general_base/idExample');
    }

    //5.1.3, 5.1.6
    public function testGetProperty()
    {
        $prop = $this->session->getProperty('/tests_general_base/idExample/jcr:primaryType');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals('jcr:primaryType', $prop->getName());
        $this->assertEquals('nt:file', $prop->getString());
    }

    public function testGetProperties()
    {
        $properties = $this->session->getProperties(array(
            '/tests_general_base/jcr:primaryType',
            '/tests_general_base/numberPropertyNode/jcr:primaryType',
            '/not_existing/jcr:primaryType',
            '/tests_general_base/../not_existing/jcr:primaryType',
        ));
        $this->assertCount(2, $properties);
        $this->assertTrue(isset($properties['/tests_general_base/jcr:primaryType']));
        $this->assertTrue(isset($properties['/tests_general_base/numberPropertyNode/jcr:primaryType']));
        foreach ($properties as $key => $property) {
            $this->assertInstanceOf('PHPCR\PropertyInterface', $property);
            $this->assertEquals($key, $property->getPath());
        }
    }

    public function testGetPropertiesTraversable()
    {
        $properties = $this->session->getProperties(new \ArrayIterator(array(
            '/tests_general_base/jcr:primaryType',
            '/tests_general_base/numberPropertyNode/jcr:primaryType',
            '/not_existing/jcr:primaryType',
            '/tests_general_base/../not_existing/jcr:primaryType',
        )));
        $this->assertCount(2, $properties);
        $this->assertTrue(isset($properties['/tests_general_base/jcr:primaryType']));
        $this->assertTrue(isset($properties['/tests_general_base/numberPropertyNode/jcr:primaryType']));
        foreach ($properties as $key => $property) {
            $this->assertInstanceOf('PHPCR\PropertyInterface', $property);
            $this->assertEquals($key, $property->getPath());
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetPropertiesInvalidArgument()
    {
        $this->session->getProperties('no iterable thing');
    }

    /**
     * it is forbidden to call getItem on the session with a relative path
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetItemRelativePathException()
    {
        $node = $this->session->getItem('tests_general_base');
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetItemPathNotFound()
    {
        $this->session->getItem('/foobarmooh');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
     public function testGetItemRepositoryException()
     {
         $this->session->getItem('//');
     }

     //5.1.2
    public function testItemExists()
    {
        $this->assertTrue($this->session->itemExists('/'));
        $this->assertTrue($this->session->itemExists('/tests_general_base'));
        $this->assertFalse($this->session->itemExists('/foobar'));
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testItemExistsRelativePath()
    {
        $this->session->itemExists('tests_general_base');
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testItemExistsInvalidPath()
    {
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
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testNodeExistsRelativePath()
    {
        $this->session->nodeExists('tests_general_base');
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testNodeExistsInvalidPath()
    {
        $this->session->nodeExists('//');
    }

    public function testPropertyExists()
    {
        $this->assertTrue($this->session->propertyExists('/tests_general_base/numberPropertyNode/jcr:content/foo'));
        //a node is not a property
        $this->assertFalse($this->session->propertyExists('/tests_general_base'));
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testPropertyExistsRelativePath()
    {
        $this->session->propertyExists('tests_general_base/numberPropertyNode/jcr:content/foo');
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testPropertyExistsInvalidPath()
    {
        $this->session->propertyExists('//');
    }

    public function testGetNodeByIdentifier()
    {
        $node = $this->session->getNodeByIdentifier('842e61c0-09ab-42a9-87c0-308ccc90e6f4');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('/tests_general_base/idExample', $node->getPath());
    }

    public function testGetNodesByIdentifier()
    {
        $nodes = $this->session->getNodesByIdentifier(array(
            '842e61c0-09ab-42a9-87c0-308ccc90e6f4',
            '00000000-0000-0000-0000-000000000000',
            '13543fc6-1abf-4708-bfcc-e49511754b40',
        ));
        $this->assertCount(2, $nodes);
        list($key, $node) = each($nodes);
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('/tests_general_base/idExample', $node->getPath());
        list($key, $node) = each($nodes);
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('/tests_general_base/idExample/jcr:content/weakreference_target', $node->getPath());
    }

    public function testGetNodesByIdentifierTraversable()
    {
        $nodes = $this->session->getNodesByIdentifier(new \ArrayIterator(array(
            '842e61c0-09ab-42a9-87c0-308ccc90e6f4',
            '00000000-0000-0000-0000-000000000000',
            '13543fc6-1abf-4708-bfcc-e49511754b40',
        )));
        $this->assertCount(2, $nodes);
        list($key, $node) = each($nodes);
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('/tests_general_base/idExample', $node->getPath());
        list($key, $node) = each($nodes);
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('/tests_general_base/idExample/jcr:content/weakreference_target', $node->getPath());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetNodesByIdentifierInvalidArgument()
    {
        $this->session->getNodesByIdentifier('not a traversable');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetNodeByIdentifierRepositoryException()
    {
        $this->session->getNodeByIdentifier('foo');
    }

    /**
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetNodeByIdentifierItemNotFoundException()
    {
        $this->session->getNodeByIdentifier('00000000-0000-0000-0000-000000000000'); //FIXME: is the identifier format defined by the repository? how to generically get a valid but inexistent id?
    }

    /**
     * spec 4.3
     * @expectedException JavaException
     */
    public function testImpersonate()
    {
        $cr = self::$loader->getRestrictedCredentials();
        $session = $this->session->impersonate($cr);
        $this->markTestIncomplete('TODO: do some tests with the impersonated session');
    }

    //TODO: Write tests for LoginException and RepositoryException with impersonate

    //4.4.4, 4.4.5
    public function testIsLiveLogout()
    {
        $ses = $this->assertSession();
        $this->assertTrue($ses->isLive());
        $ses->logout();
        $this->assertFalse($ses->isLive());
    }
}
