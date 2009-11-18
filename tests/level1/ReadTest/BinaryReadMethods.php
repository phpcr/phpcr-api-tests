<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

// According to PHPCR_BinaryInterface

class jackalope_tests_level1_ReadTest_BinaryReadMethods extends jackalope_baseCase {
    protected $node;
    public $JRbinary;

    public function setUp() {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getRootNode()->getNode('tests_level1_access_base/numberPropertyNode/jcr:content');
        $this->JRbinary = $this->node->getProperty('jcr:data')->getBinary();
        $this->assertTrue($this->JRbinary instanceOf PHPCR_BinaryInterface);
    }

    public function testDispose() {
        //just see if this throws any excaption. accessing methods after dispose is tested below
        $this->JRbinary->dispose();
    }

    public function testGetSize() {
        $size = $this->JRbinary->getSize();
        $this->assertEquals(392, $size);
    }

    /** @expectedException PHPCR_BadMethodCallException */
    public function testGetSizeDisposed() {
        $this->JRbinary->dispose();
        $this->JRbinary->getSize();
    }

    public function testGetStream() {
        $stream = $this->JRbinary->getStream();
        $this->assertNotNull($stream);
        $this->markTestIncomplete('TODO: what is a stream here?');
        //var_dump($stream);
        //echo file_get_contents($stream);
    }

    /** @expectedException PHPCR_BadMethodCallException */
    public function testGetStreamDisposed() {
        $this->JRbinary->dispose();
        $this->JRbinary->getStream();
    }

    public function testRead() {
        $bytes='';
        $cnt = $this->JRbinary->read($bytes, 0);
        $this->assertEquals(392, $cnt);
        $this->markTestIncomplete('TODO: check the resulting string when jr_cr_binary is working');
    }

    /** @expectedException PHPCR_InvalidArgumentException */
    public function testReadInvalidArgument() {
        $bytes='';
        $this->JRbinary->read($bytes, -1); //start from negative index
    }

    /** @expectedException PHPCR_BadMethodCallException */
    public function testReadDisposed() {
        $this->JRbinary->dispose();
        $bytes='';
        $this->JRbinary->read($bytes, 0);
    }

}
