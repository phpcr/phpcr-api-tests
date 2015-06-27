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

//6.3.3 Session Namespace Remapping
class SessionNamespaceRemappingTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = false)
    {
        // do not care about the fixtures
        parent::setupBeforeClass($fixtures);
    }

    protected $nsBuiltIn = array('jcr' => 'http://www.jcp.org/jcr/1.0',
                                 'nt'  => 'http://www.jcp.org/jcr/nt/1.0',
                                 'mix' => 'http://www.jcp.org/jcr/mix/1.0',
                                 'xml' => 'http://www.w3.org/XML/1998/namespace',
                                 ''    => '', );

    public function testSetNamespacePrefix()
    {
        //acquire new session, as we fiddle around with namespace prefixes
        $session = self::$loader->getSession();

        $session->setNamespacePrefix('notyetexisting', 'http://www.jcp.org/jcr/mix/1.0');
        $ret = $session->getNamespacePrefixes();
        $this->assertInternalType('array', $ret);
        $this->assertContains('notyetexisting', $ret);

        $session->logout();
    }

    /**
     * @expectedException \PHPCR\NamespaceException
     */
    public function testSetNamespacePrefixXml()
    {
        $this->session->setNamespacePrefix('xmlwhatever', 'http://www.jcp.org/jcr/mix/1.0');
    }

    public function testGetNamespacePrefixes()
    {
        $ret = $this->session->getNamespacePrefixes();
        $this->assertInternalType('array', $ret);
        foreach ($this->nsBuiltIn as $prefix => $uri) {
            $this->assertContains($prefix, $ret);
        }
    }

    public function testGetNamespaceURI()
    {
        $ret = $this->session->getNamespaceURI('jcr');
        $this->assertEquals($this->nsBuiltIn['jcr'], $ret);
    }

    /**
     * @expectedException \PHPCR\NamespaceException
     */
    public function testGetNamespaceURINonExistent()
    {
        $this->session->getNamespaceURI('http://nonexistent/2.0');
    }

    public function testGetNamespacePrefix()
    {
        $ret = $this->session->getNamespacePrefix($this->nsBuiltIn['jcr']);
        $this->assertEquals('jcr', $ret);
    }

    /**
     * @expectedException \PHPCR\NamespaceException
     */
    public function testGetNamespacePrefixNonExistent()
    {
        $this->session->getNamespacePrefix('nonexistent');
    }
}
