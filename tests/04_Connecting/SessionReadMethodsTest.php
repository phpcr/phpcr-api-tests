<?php
namespace PHPCR\Tests\Connecting;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

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

class SessionReadMethodsTest extends \PHPCR\Test\BaseCase
{
    //4.4.3
    public function testGetRepository()
    {
        $rep = $this->sharedFixture['session']->getRepository();
        $this->assertInstanceOf('PHPCR\RepositoryInterface', $rep);
    }

    //4.4.1
    public function testGetUserId()
    {
        $user = $this->sharedFixture['session']->getUserId();
        $this->assertEquals(self::$loader->getUserId(), $user);
    }

    //4.4.2
    public function testGetAttributeNames()
    {
        $cr = self::$loader->getCredentials();
        $cr->setAttribute('foo', 'bar');
        $session = $this->assertSession($cr);
        $attrs = $session->getAttributeNames();
        $this->assertInternalType('array', $attrs);
        $this->assertContains('foo', $attrs);
    }

    public function testGetAttribute()
    {
        $cr = self::$loader->getCredentials();
        $cr->setAttribute('foo', 'bar');
        $session = $this->assertSession($cr);
        $val = $session->getAttribute('foo');
        $this->assertSame('bar', $val);
    }

    //4.5.1
    public function testGetWorkspace()
    {
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $this->assertInstanceOf('PHPCR\WorkspaceInterface', $workspace);
    }
}
