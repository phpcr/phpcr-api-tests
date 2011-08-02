<?php
namespace PHPCR\Tests\Reading;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

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
                                 ''    => '');

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
        $this->sharedFixture['session']->setNamespacePrefix('xmlwhatever', 'http://www.jcp.org/jcr/mix/1.0');
    }

    public function testGetNamespacePrefixes()
    {
        $ret = $this->sharedFixture['session']->getNamespacePrefixes();
        $this->assertInternalType('array', $ret);
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
