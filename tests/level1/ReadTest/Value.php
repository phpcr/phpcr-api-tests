<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

// According to PHPCR_ValueInterface

class jackalope_tests_level1_ReadTest_Value extends jackalope_baseCase {
    protected $node;
    protected $value;

    public function setUp() {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getRootNode()->getNode('tests_level1_access_base/numberPropertyNode/jcr:content');
        $this->value = $this->node->getProperty('foo')->getValue();
        $this->assertTrue($this->value instanceOf PHPCR_ValueInterface);
    }

    public function testGetString() {
        $str = $this->value->getString();
        $this->assertType('string', $str);
        $this->assertEquals('bar', $str);
    }

    public function testGetBinary() {
        $bin = $this->value->getBinary();
        $str = $this->value->getString();
        $this->assertEquals($bin->getSize(), strlen($str));
    }
    public function testGetBoolean() {
        $this->assertEquals(false, $this->value->getBoolean()); //everything except "true" is false
        $bv = $this->node->getProperty('yesOrNo')->getValue();
        $this->assertEquals(true, $bv->getBoolean());
    }
    public function testGetDate() {
        $dv = $this->node->getProperty('jcr:lastModified')->getValue();
        $date = $dv->getDate();
        $this->assertType('object', $date);
        $this->assertTrue($date instanceOf DateTime);
        $this->assertEquals(1240830067, $date->format('U'));
    }
    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetDateValueFormatException() {
        $date = $this->value->getDate();
    }
    public function testGetDecimal() {
        $nv = $this->node->getProperty('longNumber')->getValue();
        $number = $nv->getDecimal();
        $this->assertType('float', $number);
        $this->assertEquals(999, $number);
    }

    public function testGetDouble() {
        $nv = $this->node->getProperty('longNumber')->getValue();
        $number = $nv->getDouble();
        $this->assertType('float', $number);
        $this->assertEquals(999, $number);
    }
    public function testGetLong() {
        $nv = $this->node->getProperty('longNumber')->getValue();
        $number = $nv->getLong();
        $this->assertType('int', $number);
        $this->assertEquals(999, $number);
    }
    public function testGetType() {
        $this->assertEquals(PHPCR_PropertyType::STRING, $this->value->getType());
        /* TODO: test more types. how does jackrabbit determine types? jcr:lastModified is of type string and not date...
         $dv = $this->node->getProperty('jcr:lastModified')->getValue();
         $this->assertEquals(PHPCR_PropertyType::DATE, $dv->getType());
         */
    }

}
