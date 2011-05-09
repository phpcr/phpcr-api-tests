<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

// According to PHPCR\BinaryInterface

class Read_Read_BinaryReadMethodsTest extends jackalope_baseCase
{
    protected $node;
    public $binary;
    private $binarystring = 'aDEuIENoYXB0ZXIgMSBUaXRsZQoKCiogZm9vCiogYmFyCioqIGZvbzIKKiogZm9vMwoqIGZvbzAKCnx8IGhlYWRlciB8fCBiYXIgfHwKfCBoIHwgaiB8IAoKW0Zvb3wgaHR0cDovL2xpaXAuY2hdCgp7Y29kZX0KaGVsbG8gd29ybGQKe2NvZGV9CgojIGZvbwojIGIKCgpoMi4gU2VjdGlvbiAxLjEgVGl0bGUKCgpTdWJzZWN0aW9uIDEuMS4xIFRpdGxlCn5+fn5+fn5+fn5+fn5+fn5+fn5+fn4KClNlY3Rpb24gMS4yIFRpdGxlCi0tLS0tLS0tLS0tLS0tLS0tCgpDaGFwdGVyIDIgVGl0bGUKPT09PT09PT09PT09PT09Cg==';

    static public function  setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('read/read/base');
    }

    public function setUp()
    {
        // All those tests are disabled because at this point, we dont implement PHPCR\BinaryInterface, maybee later for performance improvements.
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getRootNode()->getNode('tests_read_read_base/numberPropertyNode/jcr:content');
        $this->binaryProperty = $this->node->getProperty('jcr:data');
        $this->binary = $this->binaryProperty->getBinary();
        $this->assertTrue(is_resource($this->binary));
    }

    public function testReadBinaryValue()
    {
        $this->assertEquals($this->binarystring, stream_get_contents($this->binary));
    }

    public function testGetLength()
    {
        $size = $this->binaryProperty->getLength();
        $this->assertEquals(392, $size);
    }
}
