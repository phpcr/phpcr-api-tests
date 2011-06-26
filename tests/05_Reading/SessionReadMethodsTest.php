<?php
require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

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
class Reading_5_SessionReadMethodsTest extends phpcr_suite_baseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('general/base');
    }

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
        //TODO: Check if that's implemented in newer jackrabbit versions.
        //TODO: Write tests for LoginException and RepositoryException
        //$cr = $this->assertSimpleCredentials($this->sharedFixture['config']['user'], $this->sharedFixture['config']['pass']);
        //$ses = $this->sharedFixture['session']->impersonate($cr);
        $this->markTestIncomplete('TODO: check if we should implement this method.');

    }

    //4.4.4, 4.4.5
    public function testIsLiveLogout()
    {
        $ses = $this->assertSession($this->sharedFixture['config']);
        $this->assertTrue($ses->isLive());
        $ses->logout();
        $this->assertInstanceOf('PHPCR\SessionInterface', $ses);
        $this->assertFalse($ses->isLive());
    }

    public function testCheckPermission()
    {
        // A test without assertion is automatically marked skipped so here we
        // test no exception has occured
        $flag = false;
        try {
            $this->sharedFixture['session']->checkPermission('/tests_general_base', 'read');
        } catch (\PHPCR\Security\AccessControlException $ex) {
            $flag = true;
        }
        $this->assertFalse($flag);

        $flag = false;
        try {
            $this->sharedFixture['session']->checkPermission('/tests_general_base/numberPropertyNode/jcr:content/foo', 'read');
        } catch (\PHPCR\Security\AccessControlException $ex) {
            $flag = true;
        }
        $this->assertFalse($flag);
    }
    public function testHasPermission()
    {
        $this->assertTrue($this->sharedFixture['session']->hasPermission('/tests_general_base', 'read'));
        $this->assertTrue($this->sharedFixture['session']->hasPermission('/tests_general_base/numberPropertyNode/jcr:content/foo', 'read'));
        // TODO: check a WTF, this is supposed to fail, right? yet it succeeds
        //       if the test is moved after testCheckPermissionAccessControlException it fails
        $this->assertTrue($this->sharedFixture['session']->hasPermission('/tests_general_base/numberPropertyNode/jcr:content/foo', 'add_node')); //we have permission, but this node is not capable of the operation
    }

    /**
     * @expectedException \PHPCR\Security\AccessControlException
     */
    public function testCheckPermissionAccessControlException()
    {
        // Login as anonymous
        if (isset(self::$staticSharedFixture['session'])) {
            self::$staticSharedFixture['session']->logout();
        }
        $config = self::$staticSharedFixture['config'];
        $config['user'] = 'anonymous';
        self::$staticSharedFixture['session'] = getPHPCRSession($config);
        $session = self::$staticSharedFixture['session'];

        $session->checkPermission('/tests_general_base/numberPropertyNode/jcr:content/foo', 'add_node');
        $session->logout();

        // TODO: check if the session is correctly renewed...
        $this->saveAndRenewSession();
    }
    public function testHasCapability()
    {
        $node = $this->sharedFixture['session']->getNode('/tests_general_base');
        $this->assertTrue($this->sharedFixture['session']->hasCapability('getReferences', $node, array()), 'Does not have getReferences capability');
        $this->assertTrue($this->sharedFixture['session']->hasCapability('getProperty', $node, array('foo')));
        $property = $this->sharedFixture['session']->getProperty('/tests_general_base/numberPropertyNode/jcr:content/foo');
        $this->assertTrue($this->sharedFixture['session']->hasCapability('getNode', $property, array()));
        //$this->assertFalse($this->sharedFixture['session']->hasCapability('inexistentXXX', $property, array()));
        //actually, the repository is not required to know, it can always say that the info can not be determined and return true. this makes me think that this method is pretty useless...
    }
}
