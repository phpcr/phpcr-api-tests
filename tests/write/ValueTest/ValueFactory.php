<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

// According to PHPCR_ValueFactoryInterface

class jackalope_tests_read_ReadTest_ValueFactory extends jackalope_baseCase {
    private $factory;

    public function setUp() {
        parent::setUp();
        $this->factory = $this->sharedFixture['session']->getValueFactory();
        $this->assertTrue($this->factory instanceof PHPCR_ValueFactoryInterface);
    }
    public function testCreateBinary() {
        $this->markTestSkipped('Figure out how to work with binary');
    }
    public function testCreateValueString() {
        $value = $this->factory->createValue('10.6 test');
        $this->assertEquals('10.6 test', $value->getString());
        $this->assertEquals(10, $value->getLong());
        $this->assertEquals(PHPCR_PropertyType::STRING, $value->getType());
    }
    public function testCreateValueBinary() {
        $this->markTestSkipped('Binary');
    }
    public function testCreateValueInt() {
        $value = $this->factory->createValue(100);
        $this->assertEquals('100', $value->getString());
        $this->assertEquals(100, $value->getLong());
        $this->assertEquals(PHPCR_PropertyType::LONG, $value->getType());
    }
    public function testCreateValueFloat() {
        $value = $this->factory->createValue(10.6);
        $this->assertEquals('10.6', $value->getString());
        $this->assertEquals(10.6, $value->getDouble());
        $this->assertEquals(10, $value->getLong());
        $this->assertEquals(PHPCR_PropertyType::DOUBLE, $value->getType());
    }
    public function testCreateValueBoolean() {
        $value = $this->factory->createValue(true);
        $this->assertTrue($value->getBoolean());
        $this->assertEquals('true', $value->getString());
        $this->assertEquals(1, $value->getLong());
        $this->assertEquals(PHPCR_PropertyType::BOOLEAN, $value->getType());
    }
    public function testCreateValueNode() {
        $node = $this->sharedFixture['session']->getRootNode()->getNode('tests_read_access_base/numberPropertyNode/jcr:content');
        $value = $this->factory->createValue($node);
        $this->assertEquals(PHPCR_PropertyType::REFERENCE, $value->getType());
        $this->assertEquals($node->getIdentifier(), $value->getString());
    }
    public function testCreateValueNodeWeak() {
        $node = $this->sharedFixture['session']->getRootNode()->getNode('tests_read_access_base/numberPropertyNode/jcr:content');
        $value = $this->factory->createValue($node, null, true);
        $this->assertEquals(PHPCR_PropertyType::WEAKREFERENCE, $value->getType());
        $this->assertEquals($node->getIdentifier(), $value->getString());
    }

    public function testCreateValueStringType() {
        $value = $this->factory->createValue(33, PHPCR_PropertyType::STRING);
        $this->assertEquals(PHPCR_PropertyType::STRING, $value->getType());
    }
    public function testCreateValueDateType() {
        $time = time();
        $value = $this->factory->createValue($time, PHPCR_PropertyType::DATE);
        $this->assertEquals(PHPCR_PropertyType::DATE, $value->getType());
        $this->assertEquals(date('c', $time), $value->getString());
    }
    public function testCreateValueNodeType() {
        $node = $this->sharedFixture['session']->getRootNode()->getNode('tests_read_access_base/numberPropertyNode/jcr:content');
        $value = $this->factory->createValue($node, PHPCR_PropertyType::WEAKREFERENCE);
        $this->assertEquals(PHPCR_PropertyType::WEAKREFERENCE, $value->getType());
        $this->assertEquals($node->getIdentifier(), $value->getString());
    }
}
