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
        $node = $this->sharedFixture['session']->getRootNode();
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
        $node = $this->sharedFixture['session']->getItem('/tests_general_base');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('tests_general_base', $node->getName());

        $node = $this->sharedFixture['session']->getItem('/tests_general_base/index.txt');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('index.txt', $node->getName());

        $prop = $this->sharedFixture['session']->getItem('/tests_general_base/numberPropertyNode/jcr:content/foo');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals('foo', $prop->getName());
        $this->assertEquals('bar', $prop->getString());
        $prop = $this->sharedFixture['session']->getItem('/tests_general_base/numberPropertyNode/jcr:content/specialChars');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals('specialChars', $prop->getName());
        $this->assertEquals('üöäøéáñâêèàçæëìíîïþ', $prop->getString());
    }

    //5.1.3, 5.1.6
    public function testGetNode()
    {
        $node = $this->sharedFixture['session']->getNode('/tests_general_base/numberPropertyNode');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('numberPropertyNode', $node->getName());

        $node = $this->sharedFixture['session']->getNode('/tests_general_base/index.txt');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('index.txt', $node->getName());
    }

    public function testGetNodes()
    {
        $nodes = $this->sharedFixture['session']->getNodes(array(
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
     * Get something that is a property and not a node
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetNodeInvalid()
    {
        $this->sharedFixture['session']-> getNode('/tests_general_base/idExample/jcr:primaryType');
    }
    /**
     * Get something that is a node and not a property
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetPropertyInvalid()
    {
        $this->sharedFixture['session']-> getProperty('/tests_general_base/idExample');
    }

    //5.1.3, 5.1.6
    public function testGetProperty()
    {
        $prop = $this->sharedFixture['session']->getProperty('/tests_general_base/idExample/jcr:primaryType');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals('jcr:primaryType', $prop->getName());
        $this->assertEquals('nt:file', $prop->getString());
    }

    public function testGetProperties()
    {
        $properties = $this->sharedFixture['session']->getProperties(array(
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

    /**
     * it is forbidden to call getItem on the session with a relative path
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetItemRelativePathException()
    {
        $node = $this->sharedFixture['session']->getItem('tests_general_base');
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testGetItemPathNotFound()
    {
        $this->sharedFixture['session']->getItem('/foobarmooh');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
     public function testGetItemRepositoryException()
     {
         $this->sharedFixture['session']->getItem('//');
     }

     //5.1.2
    public function testItemExists()
    {
        $this->assertTrue($this->sharedFixture['session']->itemExists('/tests_general_base'));
        $this->assertFalse($this->sharedFixture['session']->itemExists('/foobar'));
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testItemExistsRelativePath()
    {
        $this->sharedFixture['session']->itemExists('tests_general_base');
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testItemExistsInvalidPath()
    {
        $this->sharedFixture['session']->itemExists('//');
    }


    public function testNodeExists()
    {
        $this->assertTrue($this->sharedFixture['session']->nodeExists('/tests_general_base'));
        $this->assertFalse($this->sharedFixture['session']->nodeExists('/foobar'));
        //a property is not a node
        $this->assertFalse($this->sharedFixture['session']->nodeExists('/tests_general_base/numberPropertyNode/jcr:content/foo'));
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testNodeExistsRelativePath()
    {
        $this->sharedFixture['session']->nodeExists('tests_general_base');
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testNodeExistsInvalidPath()
    {
        $this->sharedFixture['session']->nodeExists('//');
    }

    public function testPropertyExists()
    {
        $this->assertTrue($this->sharedFixture['session']->propertyExists('/tests_general_base/numberPropertyNode/jcr:content/foo'));
        //a node is not a property
        $this->assertFalse($this->sharedFixture['session']->propertyExists('/tests_general_base'));
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testPropertyExistsRelativePath()
    {
        $this->sharedFixture['session']->propertyExists('tests_general_base/numberPropertyNode/jcr:content/foo');
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testPropertyExistsInvalidPath()
    {
        $this->sharedFixture['session']->propertyExists('//');
    }

    public function testGetNodeByIdentifier()
    {
        $node = $this->sharedFixture['session']->getNodeByIdentifier('842e61c0-09ab-42a9-87c0-308ccc90e6f4');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertEquals('/tests_general_base/idExample', $node->getPath());
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetNodeByIdentifierRepositoryException()
    {
        $this->sharedFixture['session']->getNodeByIdentifier('foo');
    }

    /**
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetNodeByIdentifierItemNotFoundException()
    {
        $this->sharedFixture['session']->getNodeByIdentifier('00000000-0000-0000-0000-000000000000'); //FIXME: is the identifier format defined by the repository? how to generically get a valid but inexistent id?
    }

    /**
     * spec 4.3
     * @expectedException JavaException
     */
    public function testImpersonate()
    {
        $cr = self::$loader->getRestrictedCredentials();
        $session = $this->sharedFixture['session']->impersonate($cr);
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
