<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/** test javax.cr.Session read methods (level 1)
 *  most of the pdf specification is in section 4.4 and 5.1
 *
 *  todo: hasCapability, hasPermission, checkPermission
 *
 *  ExportTest: importXML, getImportContentHandler, exportSystemView, exportDocumentView
 *  NamespacesTest: getNamespacePrefix, getNamespacePrefixes, getNamespaceURI, setNamespacePrefix
 *
 *  level2: SessionWriteMethods: hasPendingChanges, getValueFactory, move, refresh, removeItem, save
 *  Retention: getRetentionManager
 *  Access Control: getAccessControlManager
 */

class jackalope_tests_level1_ReadTest_SessionReadMethods extends jackalope_baseCase {
    protected $path = 'level1/read';

    //4.4.3
    public function testGetRepository() {
        $rep = $this->sharedFixture['session']->getRepository();
        $this->assertTrue(is_object($rep));
        $this->assertTrue($rep instanceOf PHPCR_RepositoryInterface);
    }

    //4.4.1
    public function testGetUserId() {
        $user = $this->sharedFixture['session']->getUserId();
        $this->assertEquals($this->sharedFixture['config']['user'], $user);
    }

    //4.4.2
    public function testGetAttributeNames() {
        $this->markTestSkipped('TODO: Figure why Jackrabbit is not returning the AttributeNames');
        $cr = $this->assertSimpleCredentials($this->sharedFixture['config']['user'], $this->sharedFixture['config']['pass']);
        $cr->setAttribute('foo', 'bar');
        $session = $this->assertSession($this->sharedFixture['config'], $cr);
        $attrs = $session->getAttributeNames();
        $this->assertTrue(is_array($attrs));
        $this->assertContains('foo', $attrs);
    }

    public function testGetAttribute() {
        $this->markTestSkipped('TODO: Figure why Jackrabbit is not returning the Attribute');
        $cr = $this->assertSimpleCredentials($this->sharedFixture['config']['user'], $this->sharedFixture['config']['pass']);
        $cr->setAttribute('foo', 'bar');
        $session = $this->assertSession($this->sharedFixture['config'], $cr);
        $val = $session->getAttribute('foo');
        $this->assertTrue(is_string($val));
        $this->assertEquals($val, 'bar');
    }

    //4.5.1
    public function testGetWorkspace() {
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $this->assertTrue($workspace instanceOf PHPCR_WorkspaceInterface);
    }

    //5.1.1
    public function testGetRootNode() {
        $node = $this->sharedFixture['session']->getRootNode();
        $this->assertTrue($node instanceOf PHPCR_NodeInterface);
        $this->assertEquals($node->getPath(), '/');
    }

    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetRootNodeRepositoryException() {
        $this->markTestIncomplete('TODO: Figure out how to test this');
    }

    //5.1.3, 5.1.6
    public function testGetItem() {
        $node = $this->sharedFixture['session']->getItem('/tests_level1_access_base');
        $this->assertTrue($node instanceOf PHPCR_NodeInterface);
        $this->assertEquals($node->getName(), 'tests_level1_access_base');

        $node = $this->sharedFixture['session']->getItem('/tests_level1_access_base/index.txt');
        $this->assertTrue($node instanceOf PHPCR_NodeInterface);
        $this->assertEquals($node->getName(), 'index.txt');

        $prop = $this->sharedFixture['session']->getProperty('/tests_level1_access_base/numberPropertyNode/jcr:content/foo');
        $this->assertTrue($prop instanceOf PHPCR_PropertyInterface);
        $this->assertEquals($prop->getName(), 'foo');
        $this->assertEquals($prop->getString(), 'bar');
    }
    //5.1.3, 5.1.6
    public function testGetNode() {
        $node = $this->sharedFixture['session']->getNode('/tests_level1_access_base');
        $this->assertTrue($node instanceOf PHPCR_NodeInterface);
        $this->assertEquals($node->getName(), 'tests_level1_access_base');

        $node = $this->sharedFixture['session']->getNode('/tests_level1_access_base/index.txt');
        $this->assertTrue($node instanceOf PHPCR_NodeInterface);
        $this->assertEquals($node->getName(), 'index.txt');
    }
    //5.1.3, 5.1.6
    public function testGetProperty() {
        $prop = $this->sharedFixture['session']->getProperty('/tests_level1_access_base/idExample/jcr:primaryType');
        $this->assertTrue($prop instanceOf PHPCR_PropertyInterface);
        $this->assertEquals($prop->getName(), 'jcr:primaryType');
        $this->assertEquals($prop->getString(), 'nt:file');
    }

    /**
     * it is forbidden to call getItem on the session with a relative path
     * @expectedException PHPCR_PathNotFoundException
     */
    public function testGetItemRelativePathException() {
        $node = $this->sharedFixture['session']->getItem('tests_level1_access_base');
    }

    /**
     * @expectedException PHPCR_PathNotFoundException
     */
    public function testGetItemPathNotFound() {
        $this->sharedFixture['session']->getItem('/foobar');
    }

    /**
     * @expectedException PHPCR_RepositoryException
     */
     public function testGetItemRepositoryException() {
         $this->sharedFixture['session']->getItem('//');
     }

     //5.1.2
    public function testItemExists() {
        $this->assertTrue($this->sharedFixture['session']->itemExists('/tests_level1_access_base'));
        $this->assertFalse($this->sharedFixture['session']->itemExists('/foobar'));
    }
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testItemExistsRelativePath() {
        $this->sharedFixture['session']->itemExists('tests_level1_access_base');
    }
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testItemExistsInvalidPath() {
        $this->sharedFixture['session']->itemExists('//');
    }


    public function testNodeExists() {
        $this->assertTrue($this->sharedFixture['session']->nodeExists('/tests_level1_access_base'));
        $this->assertFalse($this->sharedFixture['session']->nodeExists('/foobar'));
        //a property is not a node
        $this->assertFalse($this->sharedFixture['session']->nodeExists('/tests_level1_access_base/numberPropertyNode/jcr:content/foo'));
    }
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testNodeExistsRelativePath() {
        $this->sharedFixture['session']->nodeExists('tests_level1_access_base');
    }
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testNodeExistsInvalidPath() {
        $this->sharedFixture['session']->nodeExists('//');
    }

    public function testPropertyExists() {
        $this->assertTrue($this->sharedFixture['session']->propertyExists('/tests_level1_access_base/numberPropertyNode/jcr:content/foo'));
        //a node is not a property
        $this->assertFalse($this->sharedFixture['session']->propertyExists('/tests_level1_access_base'));
    }
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testPropertyExistsRelativePath() {
        $this->sharedFixture['session']->propertyExists('tests_level1_access_base/numberPropertyNode/jcr:content/foo');
    }
    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testPropertyExistsInvalidPath() {
        $this->sharedFixture['session']->propertyExists('//');
    }

    public function testGetNodeByIdentifier() {
        $node1 = $this->sharedFixture['session']->getNode('/tests_level1_access_base/idExample');
        $node2 = $this->sharedFixture['session']->getNodeByIdentifier('842e61c0-09ab-42a9-87c0-308ccc90e6f4');
        $this->assertTrue($node2 instanceOf PHPCR_NodeInterface);
        $this->assertEquals($node1, $node2);
    }

    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetNodeByIdentifierRepositoryException() {
        $this->sharedFixture['session']->getNodeByIdentifier('foo');
    }

    /**
     * @expectedException PHPCR_ItemNotFoundException
     */
    public function testGetNodeByIdentifierItemNotFoundException() {
        $this->sharedFixture['session']->getNodeByIdentifier(jr_cr_node::uuid());
    }

    /**
     * spec 4.3
     * @expectedException JavaException
     */
    public function testImpersonate() {
        //TODO: Check if that's implemented in newer jackrabbit versions.
        //TODO: Write tests for LoginException and RepositoryException
        $cr = $this->assertSimpleCredentials($this->sharedFixture['config']['user'], $this->sharedFixture['config']['pass']);
        $ses = $this->sharedFixture['session']->impersonate($cr);
    }

    //4.4.4, 4.4.5
    public function testIsLiveLogout() {
        $ses = $this->assertSession($this->sharedFixture['config']);
        $this->assertTrue($ses->isLive());
        $ses->logout();
        $this->assertTrue($ses instanceOf PHPCR_SessionInterface);
        $this->assertFalse($ses->isLive());
    }
}
