<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

// According to PHPCR\BinaryInterface

class Read_Read_BinaryReadMethodsTest extends jackalope_baseCase {
    protected $node;
    public $binary;
    private $binarystring = 'aDEuIENoYXB0ZXIgMSBUaXRsZQoKCiogZm9vCiogYmFyCioqIGZvbzIKKiogZm9vMwoqIGZvbzAKCnx8IGhlYWRlciB8fCBiYXIgfHwKfCBoIHwgaiB8IAoKW0Zvb3wgaHR0cDovL2xpaXAuY2hdCgp7Y29kZX0KaGVsbG8gd29ybGQKe2NvZGV9CgojIGZvbwojIGIKCgpoMi4gU2VjdGlvbiAxLjEgVGl0bGUKCgpTdWJzZWN0aW9uIDEuMS4xIFRpdGxlCn5+fn5+fn5+fn5+fn5+fn5+fn5+fn4KClNlY3Rpb24gMS4yIFRpdGxlCi0tLS0tLS0tLS0tLS0tLS0tCgpDaGFwdGVyIDIgVGl0bGUKPT09PT09PT09PT09PT09Cg==';

    static public function  setupBeforeClass() {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('read/read/base.xml');
    }

    public function setUp() {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getRootNode()->getNode('tests_read_access_base/numberPropertyNode/jcr:content');
        $this->binary = $this->node->getProperty('jcr:data')->getBinary();
        $this->assertType('PHPCR\BinaryInterface', $this->binary);
    }

    public function testDispose() {
        //just see if this throws any excaption. accessing methods after dispose is tested below
        $this->binary->dispose();
    }

    public function testGetSize() {
        $size = $this->binary->getSize();
        $this->assertEquals(392, $size);
    }

    /** @expectedException PHPCR\BadMethodCallException */
    public function testGetSizeDisposed() {
        $this->binary->dispose();
        $this->binary->getSize();
    }

    public function testGetStream() {
        $stream = $this->binary->getStream();
        $this->assertNotNull($stream);
        $bytes = fread($stream, $this->binary->getSize());
        $this->assertEquals($this->binarystring, $bytes);
    }

    /** @expectedException PHPCR\BadMethodCallException */
    public function testGetStreamDisposed() {
        $this->binary->dispose();
        $this->binary->getStream();
    }

    public function testRead() {
        $bytes='';
        $cnt = $this->binary->read($bytes, 0);
        $this->assertEquals(392, $cnt);
        $this->assertEquals($this->binarystring, $bytes);
    }

    /** @expectedException PHPCR\InvalidArgumentException */
    public function testReadInvalidArgument() {
        $bytes='';
        $this->binary->read($bytes, -1); //start from negative index
    }

    /** @expectedException PHPCR\BadMethodCallException */
    public function testReadDisposed() {
        $this->binary->dispose();
        $bytes='';
        $this->binary->read($bytes, 0);
    }

}
