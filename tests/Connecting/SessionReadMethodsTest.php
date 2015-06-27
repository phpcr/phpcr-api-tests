<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2013 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Connecting;

/** test javax.cr.Session read methods (level 1)
 *  most of the pdf specification is in section 4.4 and 5.1.
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
        $rep = $this->session->getRepository();
        $this->assertInstanceOf('PHPCR\RepositoryInterface', $rep);
    }

    //4.4.1
    public function testGetUserId()
    {
        $user = $this->session->getUserId();
        $this->assertEquals(self::$loader->getUserId(), $user);
    }

    //4.4.2
    public function testGetAttributeNames()
    {
        $cr = self::$loader->getCredentials();
        if (!$cr instanceof \PHPCR\SimpleCredentials) {
            $this->markTestSkipped('This implementation is not using the SimpleCredentials. We can not know if there is anything about attributes. You need to test getAttributeNames in your implementation specific tests');
        }
        $cr->setAttribute('foo', 'bar');
        $session = $this->assertSession($cr);
        $attrs = $session->getAttributeNames();
        $this->assertInternalType('array', $attrs);
        $this->assertContains('foo', $attrs);
    }

    public function testGetAttribute()
    {
        $cr = self::$loader->getCredentials();
        if (!$cr instanceof \PHPCR\SimpleCredentials) {
            $this->markTestSkipped('This implementation is not using the SimpleCredentials. We can not know if there is anything about attributes. You need to test getAttribute in your implementation specific tests');
        }
        $cr->setAttribute('foo', 'bar');
        $session = $this->assertSession($cr);
        $val = $session->getAttribute('foo');
        $this->assertSame('bar', $val);
    }

    //4.5.1
    public function testGetWorkspace()
    {
        $workspace = $this->session->getWorkspace();
        $this->assertInstanceOf('PHPCR\WorkspaceInterface', $workspace);
    }
}
