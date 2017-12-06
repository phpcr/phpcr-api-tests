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

use PHPCR\NamespaceException;
use PHPCR\NamespaceRegistryInterface;
use PHPCR\RepositoryException;
use PHPCR\Test\BaseCase;

//6.3.1 Namespace Registry
class NamespaceRegistryTest extends BaseCase
{
    protected $workspace;
    /**
     * @var NamespaceRegistryInterface
     */
    protected $nr;
    protected $nsBuiltIn = [
        'jcr' => 'http://www.jcp.org/jcr/1.0',
        'nt'  => 'http://www.jcp.org/jcr/nt/1.0',
        'mix' => 'http://www.jcp.org/jcr/mix/1.0',
        'xml' => 'http://www.w3.org/XML/1998/namespace',
        ''    => ''
    ];

    public function setUp()
    {
        parent::setUp();
        $this->workspace = $this->session->getWorkspace();
        $this->nr = $this->workspace->getNamespaceRegistry(); //this function is tested in ReadTest/WorkspaceReadMethods.php::testGetNamespaceRegistry
    }

    public function testGetPrefixes()
    {
        $ret = $this->nr->getPrefixes();
        $this->assertInternalType('array', $ret);
        $this->assertGreaterThanOrEqual(count($this->nsBuiltIn), count($ret));
    }

    public function testGetURIs()
    {
        $ret = $this->nr->getURIs();
        $this->assertInternalType('array', $ret);
        $this->assertGreaterThanOrEqual(count($this->nsBuiltIn), count($ret));
        //we test in getURI / getPrefix if the names match
    }

    public function testGetURI()
    {
        foreach ($this->nsBuiltIn as $prefix => $uri) {
            $ret = $this->nr->getURI($prefix);
            $this->assertEquals($uri, $ret);
        }
    }

    public function testGetURINamespaceException()
    {
        $this->expectException(NamespaceException::class);

        $this->nr->getURI('thisshouldnotexist');
    }

    public function testGetURIRepositoryException()
    {
        $this->expectException(RepositoryException::class);

        $this->nr->getURI('in:valid');
    }

    public function testGetPrefix()
    {
        foreach ($this->nsBuiltIn as $prefix => $uri) {
            $ret = $this->nr->getPrefix($uri);
            $this->assertEquals($prefix, $ret);
        }
    }

    public function testGetPrefixNamespaceException()
    {
        $this->expectException(NamespaceException::class);

        $this->nr->getPrefix('http://thisshouldnotexist.org/0.0');
    }

    public function testRegisterNamespace()
    {
        $uri = 'http://a_new_namespace';
        $prefix = 'new_prefix';
        $prefix2 = 'my_prefix';
        $this->nr->registerNamespace($prefix, $uri);
        $this->assertEquals($prefix, $this->nr->getPrefix($uri));
        $this->assertEquals($uri, $this->nr->getURI($prefix));
        $this->nr->registerNamespace($prefix2, $uri);
        $this->assertEquals($prefix2, $this->nr->getPrefix($uri));
        $this->assertEquals($uri, $this->nr->getURI($prefix2));

        $session = $this->renewSession();
        $nr = $session->getWorkspace()->getNamespaceRegistry();
        $this->assertEquals($uri, $nr->getURI($prefix2));
    }

    public function testRegisterUnregisterNamespace()
    {
        $uri = 'http://removable_namespace';
        $prefix = 'removable_prefix';

        $this->nr->registerNamespace($prefix, $uri);
        $this->nr->unregisterNamespaceByURI($uri);
        $this->assertNotContains($prefix, $this->nr->getPrefixes());
        $this->assertNotContains($uri, $this->nr->getURIs());
    }

    public function testRegisterNamespaceException()
    {
        $this->expectException(NamespaceException::class);

        $this->nr->registerNamespace('valid', $this->nsBuiltIn['jcr']);
    }

    public function testRegisterNamespacePrefixRepositoryException()
    {
        $this->expectException(RepositoryException::class);

        $this->nr->registerNamespace('in:valid', 'http://a_new_namespace');
    }

    public function testUnregisterNamespaceException()
    {
        $this->expectException(NamespaceException::class);

        $this->nr->unregisterNamespaceByURI('http://thisshouldnotexist.org/0.0');
    }

    public function testIterator()
    {
        $this->assertTraversableImplemented($this->nr);
        $results = 0;
        foreach ($this->nr as $prefix => $url) {
            $results++;
            $this->assertInternalType('string', $prefix);
            $this->assertInternalType('string', $url);
            $this->assertEquals($url, $this->nr->getURI($prefix));
        }
        $this->assertGreaterThan(3, $results, 'Not enough namespaces');
    }
}
