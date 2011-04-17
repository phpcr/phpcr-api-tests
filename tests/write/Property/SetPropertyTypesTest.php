<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * Testing whether the property correctly handles all types
 *
 * Covering jcr-2.8.3 spec $10.4.2
 */
class Write_Property_SetPropertyTypesTest extends jackalope_baseCase
{

    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('write/value/base');
    }

    public function setUp()
    {
        parent::setUp();

        $this->renewSession();
        $this->node = $this->sharedFixture['session']->getNode('/tests_write_value_base/numberPropertyNode/jcr:content');
        $this->property = $this->sharedFixture['session']->getProperty('/tests_write_value_base/numberPropertyNode/jcr:content/longNumber');
    }

    //TODO: have this for all types in PropertyType and each with and without the explicit type parameter. also test node->getPropertyValue for correct type

    public function testCreateBinary()
    {
        $this->markTestSkipped('Figure out how to work with binary');
    }
    public function testCreateString()
    {
        $value = $this->node->setProperty('x', '10.6 test');
        $this->assertSame('10.6 test', $value->getString());
        $this->assertSame(10, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::STRING, $value->getType());
    }
    public function testCreateValueBinary()
    {
        $bin = $this->node->setProperty('newBinary', 'foobar', PHPCR\PropertyType::BINARY);
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $bin->getType());
        $this->assertEquals('foobar', $bin->getBinary());
    }
    public function testCreateValueInt()
    {
        $value = $this->node->setProperty('x', 100);
        $this->assertSame('100', $value->getString());
        $this->assertSame(100, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $value->getType());
    }
    public function testCreateValueDouble()
    {
        $value = $this->node->setProperty('x', 10.6);
        $this->assertSame('10.6', $value->getString());
        $this->assertSame(10.6, $value->getDouble());
        $this->assertSame(10, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::DOUBLE, $value->getType());
    }
    public function testCreateValueBoolean()
    {
        $value = $this->node->setProperty('x', true);
        $this->assertEquals(\PHPCR\PropertyType::BOOLEAN, $value->getType(), 'wrong type');
        $this->assertTrue($value->getBoolean(), 'boolean not true');
        $this->assertSame('true', $value->getString(), 'wrong string value'); //boolean converted to string must be the word true
        $this->assertSame(1, $value->getLong(), 'wrong integer value');
    }
    public function testCreateValueNode()
    {
        $node = $this->sharedFixture['session']->getNode('/tests_write_value_base/multiValueProperty');
        $value = $this->node->setProperty('x', $node);
        $this->assertEquals(\PHPCR\PropertyType::REFERENCE, $value->getType(), 'wrong type');
        $this->assertEquals($node->getIdentifier(), $value->getString(), 'different uuid');
    }
    public function testCreateValueNodeWeak()
    {
        $node = $this->sharedFixture['session']->getRootNode()->getNode('tests_write_value_base/multiValueProperty');
        $value = $this->node->setProperty('x', $node, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $value->getType());
        $this->assertEquals($node->getIdentifier(), $value->getString());
    }
    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testCreateValueNodeNonReferencable()
    {
        $node = $this->sharedFixture['session']->getRootNode()->getNode('tests_write_value_base/numberPropertyNode/jcr:content');
        $value = $this->node->setProperty('x', $node);
    }
    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testCreateValueNodeNonReferencableWeak()
    {
        $node = $this->sharedFixture['session']->getRootNode()->getNode('tests_write_value_base/numberPropertyNode/jcr:content');
        $value = $this->node->setProperty('x', $node, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->fail("Exception should be thrown, but ". $value->getString() . " was returned.");
    }
    public function testCreateValueStringType()
    {
        $value = $this->node->setProperty('x', 33, \PHPCR\PropertyType::STRING);
        $this->assertEquals(\PHPCR\PropertyType::STRING, $value->getType());
    }
    public function testCreateValueDateType()
    {
        $time = time();
        $value = $this->node->setProperty('x', $time, \PHPCR\PropertyType::DATE);
        $this->assertEquals(\PHPCR\PropertyType::DATE, $value->getType());
        $this->assertEquals(date('Y-m-d\TH:i:s.000P', $time), $value->getString());
    }
}
