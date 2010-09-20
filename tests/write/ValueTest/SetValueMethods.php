<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * Covering jcr-2.8.3 spec $10.4.2
 */
class jackalope_tests_write_SetTest_SetValueMethods extends jackalope_baseCase {

    protected $node;

    public function setUp() {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getNode('/tests_write_value_base/numberPropertyNode/jcr:content');
        $this->property = $this->sharedFixture['session']->getProperty('/tests_write_value_base/numberPropertyNode/jcr:content/longNumber');
    }

    /**
     * @covers Property::setValue
     */
    public function testSetValue() {
        $this->property->setValue(1024);
        $this->assertEquals(1024, $this->property->getValue()->getLong());
    }

    /**
     * @covers Node::setProperty
     */
    public function testSetPropertyExisting() {
        $this->node->setProperty('longNumber', 1024);
        $this->assertEquals(1024, $this->node->getProperty('longNumber')->getValue()->getLong());
    }


    /**
     * @covers Node::setProperty
     */
    public function testSetPropertyNew() {
        $this->node->setProperty('newLongNumber', 1024);
        $this->assertEquals(1024, $this->node->getProperty('newLongNumber')->getValue()->getLong());
    }

    /**
     * change type of existing property
     * @covers Node::setProperty
     */
    public function testSetPropertyWithType() {
        $this->node->setProperty('longNumber', 1024, PHPCR_PropertyType::LONG);
        $this->assertEquals(1024, $this->node->getProperty('longNumber')->getValue()->getLong());
        $this->assertEquals(PHPCR_PropertyType::LONG, $this->node->getProperty('longNumber')->getType());
    }

    /**
     * add new property
     * @covers Node::setProperty
     */
    public function testSetPropertyNewWithType() {
        $this->node->setProperty('newLongNumber', 102, PHPCR_PropertyType::LONG);
        $this->assertEquals(102, $this->node->getProperty('newLongNumber')->getValue()->getLong());
        $this->assertEquals(PHPCR_PropertyType::LONG, $this->node->getProperty('newLongNumber')->getType());
    }
    //TODO: different types, no type and type detection logic
    //TODO: Property::setValue with value objects and values

    //TODO: Session::hasPendingChanges
}
