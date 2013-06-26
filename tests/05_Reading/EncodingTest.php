<?php
namespace PHPCR\Tests\Reading;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Test javax.jcr.Node read methods (read) §5.6
 * With special characters.
 */
class EncodingTest extends \PHPCR\Test\BaseCase
{

    static public function setupBeforeClass($fixtures = '05_Reading/encoding')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        parent::setUp();

        // because of the data provider the signature will not match
        $this->node = $this->rootNode->getNode('tests_read_encoding')->getNode('testEncoding');
    }

    /**
     * @dataProvider getNodeNames
     */
    public function testEncoding($name)
    {
        $this->assertTrue($this->node->hasNode($name));
        $node = $this->node->getNode($name);
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
    }

    public static function getNodeNames()
    {
        return array(
            array("node-ä-x"),
            array("node-è-x"),
            array("node-ï-x"),
            array("node-%-x"),
            array("node-%2F-x"),
            array("node- -x"),
            array("node-ç-x"),
            array("node-&-x"),
        );
    }
}
