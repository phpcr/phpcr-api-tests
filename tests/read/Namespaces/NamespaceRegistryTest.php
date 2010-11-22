<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.3.1 Namespace Registry
class Read_Namespaces_NamespaceRegistryTest extends jackalope_baseCase
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
        $this->assertType('array', $ret);
        $this->assertTrue(count($ret) >= count($this->nsBuiltIn));
    }

    public function testGetURIs()
    {
        $ret = $this->nr->getURIs();
        $this->assertType('array', $ret);
        $this->assertTrue(count($ret) >= count($this->nsBuiltIn));
        //we test in getURI / getPrefix if the names match
    }

    public function testGetURI()
    {
        foreach($this->nsBuiltIn as $prefix => $uri) {
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
        foreach($this->nsBuiltIn as $prefix => $uri) {
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

    public function testRegisterUnregisterNamespace()
    {
        $uri = 'http://a_new_namespace';
        $prefix = 'new_prefix';
        $prefix2 = 'my_prefix';
        $this->nr->registerNamespace($prefix, $uri);
        $this->assertEquals($this->nr->getPrefix($uri), $prefix);
        $this->assertEquals($this->nr->getURI($prefix), $uri);
        $this->nr->registerNamespace($prefix2, $uri);
        $this->assertEquals($this->nr->getPrefix($uri), $prefix2);
        $this->assertEquals($this->nr->getURI($prefix2), $uri);

        $this->markTestSkipped('TODO: has this signature changed or is jackrabbit just wrong? expects uri instead of prefix');
        $this->nr->unregisterNamespace($prefix2);
        $this->assertNotContains($prefix2, $this->nr->getPrefixes());
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
}
