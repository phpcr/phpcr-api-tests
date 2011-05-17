<?php
require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/** test javax.cr.Session read methods (level 1)
 *  most of the pdf specification is in section 4.4 and 5.1
 *
 *  ExportTest: importXML, getImportContentHandler, exportSystemView, exportDocumentView
 *  NamespacesTest: getNamespacePrefix, getNamespacePrefixes, getNamespaceURI, setNamespacePrefix
 *
 *  level2: SessionWriteMethods: hasPendingChanges, getValueFactory, move, refresh, removeItem, save
 *  Retention: getRetentionManager
 *  Access Control: getAccessControlManager
 */

class Reading_4_SessionReadMethodsTest extends jackalope_baseCase
{
    static public function  setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('general/base');
    }

    //4.4.3
    public function testGetRepository()
    {
        $rep = $this->sharedFixture['session']->getRepository();
        $this->assertType('PHPCR\RepositoryInterface', $rep);
    }

    //4.4.1
    public function testGetUserId()
    {
        $user = $this->sharedFixture['session']->getUserId();
        $this->assertEquals($this->sharedFixture['config']['user'], $user);
    }

    //4.4.2
    public function testGetAttributeNames()
    {
        $this->markTestSkipped('TODO: Figure why Jackrabbit is not returning the AttributeNames');
        $cr = $this->assertSimpleCredentials($this->sharedFixture['config']['user'], $this->sharedFixture['config']['pass']);
        $cr->setAttribute('foo', 'bar');
        $session = $this->assertSession($this->sharedFixture['config'], $cr);
        $attrs = $session->getAttributeNames();
        $this->assertType('array', $attrs);
        $this->assertContains('foo', $attrs);
    }

    public function testGetAttribute()
    {
        $this->markTestSkipped('TODO: Figure why Jackrabbit is not returning the Attribute');
        $cr = $this->assertSimpleCredentials($this->sharedFixture['config']['user'], $this->sharedFixture['config']['pass']);
        $cr->setAttribute('foo', 'bar');
        $session = $this->assertSession($this->sharedFixture['config'], $cr);
        $val = $session->getAttribute('foo');
        $this->assertSame($val, 'bar');
    }

    //4.5.1
    public function testGetWorkspace()
    {
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $this->assertType('PHPCR\WorkspaceInterface', $workspace);
    }
}
