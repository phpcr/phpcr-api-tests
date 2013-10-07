<?php
namespace PHPCR\Tests\Writing;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Test javax.jcr.Node read methods (read) §5.6
 * With special characters.
 */
class EncodingTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = '10_Writing/encoding')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        parent::setUp();

        // because of the data provider the signature will not match
        $this->node = $this->rootNode->getNode('tests_write_encoding')->getNode('testEncoding');
    }

    /**
     * @dataProvider getNodeNames
     */
    public function testEncoding($name)
    {
        $node = $this->node->addNode($name);
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);

        $session = $this->saveAndRenewSession();
        $node = $session->getNode('/tests_write_encoding/testEncoding');
        $this->assertTrue($node->hasNode($name));
        $this->assertInstanceOf('PHPCR\NodeInterface', $node->getNode($name));
    }

    public static function getNodeNames()
    {
        return array(
            array("node-ä-x"),
            array("node-è-x"),
            array("node-ï-x"),
            array("node-%-x"),
            array("node-%2F-x"),
            array("node-;-x"),
            array("node- -x"),
            array("node-ç-x"),
            array("node-&-x"),
        );
    }

    /**
     * @dataProvider getPropertyValues
     */
    public function testEncodingPropertyValues($value, $type)
    {
        $this->node->setProperty($type, $value);
        $session = $this->saveAndRenewSession();
        $this->assertEquals($value, $session->getRootNode()->getNode('tests_write_encoding')->getNode('testEncoding')->getPropertyValue($type));
    }

    public static function getPropertyValues()
    {
        return array(
            array('PHPCR\Query\QueryInterface', 'backslash'),
            array('PHPCR\\\\Query\\\\QueryInterface', 'doublebackslash'),
            array('"\'', 'quotes'),
            array('a\\\'\\\'b\\\'\\\'c', 'quotesandbackslash'),
            array('foo & bar&baz', 'ampersand'),
        );
    }
}
