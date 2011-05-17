<?php
require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

//6.3.3 Session Namespace Remapping
class Reading_SessionNamespaceRemappingTest extends jackalope_baseCase
{
    protected $nsBuiltIn = array('jcr' => 'http://www.jcp.org/jcr/1.0',
                                 'nt'  => 'http://www.jcp.org/jcr/nt/1.0',
                                 'mix' => 'http://www.jcp.org/jcr/mix/1.0',
                                 'xml' => 'http://www.w3.org/XML/1998/namespace',
                                 ''    => '');

    public function testSetNamespacePrefix()
    {
        //acquire new session, as we fiddle around with namespace prefixes
        $session = getJCRSession($this->sharedFixture['config']);
        $session->setNamespacePrefix('notyetexisting', 'http://www.jcp.org/jcr/mix/1.0');
        $ret = $session->getNamespacePrefixes();
        $this->assertType('array', $ret);
        $this->assertContains('notyetexisting', $ret);
    }

    /**
     * @expectedException \PHPCR\NamespaceException
     */
    public function testSetNamespacePrefixXml()
    {
        $this->sharedFixture['session']->setNamespacePrefix('xmlwhatever', 'http://www.jcp.org/jcr/mix/1.0');
    }

    /**
     * @expectedException \PHPCR\NamespaceException
     */
    public function testSetNamespaceUnregistered()
    {
        $this->markTestSkipped('TODO: jackrabbit just adds the namespace URI if it is not yet existing. Spec tells us "A NamespaceException will also be thrown if the specified uri is not among those registered in the NamespaceRegistry."');
        //$this->sharedFixture['session']->setNamespacePrefix('whatever', 'http://nonexistent/1.0');
    }

    public function testGetNamespacePrefixes()
    {
        $ret = $this->sharedFixture['session']->getNamespacePrefixes();
        $this->assertType('array', $ret);
        foreach ($this->nsBuiltIn as $prefix => $uri) {
            $this->assertContains($prefix, $ret);
        }
    }

    public function testGetNamespaceURI()
    {
        $ret = $this->sharedFixture['session']->getNamespaceURI('jcr');
        $this->assertEquals($this->nsBuiltIn['jcr'], $ret);
    }

    /**
     * @expectedException \PHPCR\NamespaceException
     */
    public function testGetNamespaceURINonExistent()
    {
        $this->sharedFixture['session']->getNamespaceURI('http://nonexistent/2.0');
    }

    public function testGetNamespacePrefix()
    {
        $ret = $this->sharedFixture['session']->getNamespacePrefix($this->nsBuiltIn['jcr']);
        $this->assertEquals('jcr', $ret);
    }

    /**
     * @expectedException \PHPCR\NamespaceException
     */
    public function testGetNamespacePrefixNonExistent()
    {
        $this->sharedFixture['session']->getNamespacePrefix('nonexistent');
    }
}
