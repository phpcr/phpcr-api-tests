<?php
namespace PHPCR\Tests\Writing;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

//6.3.1 Namespace Registry
class NamespaceRegistryTest extends \PHPCR\Test\BaseCase
{
    protected $workspace;
    protected $nr; //the NamespaceRegistry
    protected $nsBuiltIn = array('jcr' => 'http://www.jcp.org/jcr/1.0',
                                 'nt'  => 'http://www.jcp.org/jcr/nt/1.0',
                                 'mix' => 'http://www.jcp.org/jcr/mix/1.0',
                                 'xml' => 'http://www.w3.org/XML/1998/namespace',
                                 ''    => '');

    function setUp()
    {
        parent::setUp();
        $this->workspace = $this->sharedFixture['session']->getWorkspace();
        $this->nr = $this->workspace->getNamespaceRegistry(); //this function is tested in ReadTest/WorkspaceReadMethods.php::testGetNamespaceRegistry
    }

    public function testGetPrefixes()
    {
        $ret = $this->nr->getPrefixes();
        $this->assertInternalType('array', $ret);
        $this->assertTrue(count($ret) >= count($this->nsBuiltIn));
    }

    public function testGetURIs()
    {
        $ret = $this->nr->getURIs();
        $this->assertInternalType('array', $ret);
        $this->assertTrue(count($ret) >= count($this->nsBuiltIn));
        //we test in getURI / getPrefix if the names match
    }

    public function testGetURI()
    {
        foreach ($this->nsBuiltIn as $prefix => $uri) {
            $ret = $this->nr->getURI($prefix);
            $this->assertEquals($uri, $ret);
        }
    }

    /**
     * @expectedException \PHPCR\NamespaceException
     */
    public function testGetURINamespaceException()
    {
        $this->nr->getURI('thisshouldnotexist');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetURIRepositoryException()
    {
        $this->nr->getURI('in:valid');
    }

    public function testGetPrefix()
    {
        foreach ($this->nsBuiltIn as $prefix => $uri) {
            $ret = $this->nr->getPrefix($uri);
            $this->assertEquals($prefix, $ret);
        }
    }

    /**
     * @expectedException \PHPCR\NamespaceException
     */
    public function testGetPrefixNamespaceException()
    {
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
        $this->nr->unregisterNamespace($prefix);
        $this->assertNotContains($prefix, $this->nr->getPrefixes());
        $this->assertNotContains($uri, $this->nr->getURIs());
    }

    /**
     * @expectedException \PHPCR\NamespaceException
     */
    public function testRegisterNamespaceException()
    {
        $this->nr->registerNamespace('valid', $this->nsBuiltIn['jcr']);
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testRegisterNamespacePrefixRepositoryException()
    {
        $this->nr->registerNamespace('in:valid', 'http://a_new_namespace');
    }

    /**
     * @expectedException \PHPCR\NamespaceException
     */
    public function testUnregisterNamespaceException()
    {
        $this->nr->unregisterNamespace('http://thisshouldnotexist.org/0.0');
    }

    public function testIterator() {
        $this->assertTraversableImplemented($this->nr);
        $results = 0;
        foreach ($this->nr as $prefix => $url) {
            $results++;
            $this->assertInternalType('string', $prefix);
            $this->assertInternalType('string', $url);
            $this->assertEquals($url, $this->nr->getURI($prefix));
        }
        $this->assertTrue($results>3, 'Not enough namespaces');
    }

}
