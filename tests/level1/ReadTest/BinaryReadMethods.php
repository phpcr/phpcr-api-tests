<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

// According to PHPCR_BinaryInterface

class jackalope_tests_level1_ReadTest_BinaryReadMethods extends jackalope_baseCase {
    protected $node;
    protected $binary;

    public function setUp() {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getRootNode()->getNode('tests_level1_access_base/numberPropertyNode/jcr:content');
        $this->binary = $this->node->getProperty('jcr:data')->getBinary();
        $this->assertTrue($this->binary instanceOf PHPCR_BinaryInterface);
    }

    public function testDispose() {
        $this->binary->dispose();
    }

    public function testGetSize() {
        $size = $this->binary->getSize();
        $this->assertEquals(392, $size);
    }

    /** @expectedException PHPCR_BadMethodCallException */
    public function testGetSizeDisposed() {
        $this->binary->dispose();
        $this->binary->getSize();
    }

    public function testGetStream() {
        $stream = $this->binary->getStream();
        $this->assertNotNull($stream);
        $this->markTestIncomplete('TODO: what is a stream here?');
        //var_dump($stream);
        //echo file_get_contents($stream);
    }

    /** @expectedException PHPCR_BadMethodCallException */
    public function testGetStreamDisposed() {
        $this->binary->dispose();
        $this->binary->getStream();
    }

    public function testRead() {
        $bytes='';
        $cnt = $this->binary->read($bytes, 0);
        $this->assertEquals(392, $cnt);
        $this->markTestIncomplete('TODO: check contents of $bytes');
    }

    /** @expectedException PHPCR_InvalidArgumentException */
    public function testReadInvalidArgument() {
        $bytes='';
        $this->binary->read($bytes, -1); //start from negative index
    }

    /** @expectedException PHPCR_BadMethodCallException */
    public function testReadDisposed() {
        $this->binary->dispose();
        $bytes='';
        $this->binary->read($bytes, 0);
    }

}
