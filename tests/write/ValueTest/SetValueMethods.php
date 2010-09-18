<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * Covering jcr-2.8.3 spec $10.4.2
 *
 * SetMethods: Node::setProperty Property::setValue
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
        $this->assertEquals(1024, $this->property->getValue());
    }

    /**
     * @covers Node::setProperty
     */
    public function testSetPropertyExisting() {
        $this->node->setProperty('longNumber', 1024);
        $this->assertEquals(1024, $this->node->getProperty('longNumber')->getValue());
    }


    /**
     * @covers Node::setProperty
     */
    public function testSetPropertyNew() {
        $this->node->setProperty('newLongNumber', 1024);
        $this->assertEquals(1024, $this->node->getProperty('newLongNumber')->getValue());
    }

    /**
     * @covers Node::setProperty
     */
    public function testSetPropertyWithType() {
        $date = '2010-09-18T00:00:00.000+02:00';
        $this->node->setProperty('jcr:created', $date, PHPCR_PropertyType::DATE);
        $this->assertEquals($date, $this->node->getProperty('jcr:created')->getValue());
    }



}

