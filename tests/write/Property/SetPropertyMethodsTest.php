<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * Testing whether node property manipulations work correctly
 *
 * Covering jcr-2.8.3 spec $10.4.2
 */
class Write_Property_SetPropertyMethodsTest extends jackalope_baseCase {

    private $node;
    private $property;

    static public function setupBeforeClass() {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('write/value/base.xml');
    }

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
        $this->assertEquals(1024, $this->property->getLong());
    }

    /**
     * @covers Node::setProperty
     */
    public function testSetPropertyExisting() {
        $this->assertTrue($this->node->hasProperty('longNumber'));
        $property = $this->node->setProperty('longNumber', 1024);
        $this->assertType('PHPCR\PropertyInterface', $property);
        $this->assertEquals(1024, $this->node->getProperty('longNumber')->getLong());
    }


    /**
     * @covers Node::setProperty
     */
    public function testSetPropertyNew() {
        $property = $this->node->setProperty('newLongNumber', 1024);
        $this->assertType('PHPCR\PropertyInterface', $property);
        $this->assertEquals(1024, $this->node->getProperty('newLongNumber')->getLong());
    }

    /**
     * change type of existing property
     * @covers Node::setProperty
     */
    public function testSetPropertyWithType() {
        $this->node->setProperty('longNumber', 1024.5, \PHPCR\PropertyType::LONG);
        $this->assertEquals(1024, $this->node->getProperty('longNumber')->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $this->node->getProperty('longNumber')->getType());
    }

    /**
     * add new property
     * @covers Node::setProperty
     */
    public function testSetPropertyNewWithType() {
        $this->node->setProperty('newLongNumber', 102.5, \PHPCR\PropertyType::LONG);
        $this->assertEquals(102, $this->node->getProperty('newLongNumber')->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $this->node->getProperty('newLongNumber')->getType());
    }
    //TODO: is this all creation modes? the types are tested in SetPropertyTypes

    //TODO: Session::hasPendingChanges
}
