<?php
require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
 * Testing whether the property correctly handles all types
 *
 * For every test we do the assertions twice:
 *   - Once after the property has been set in memory
 *   - Once after renewing the session and reading the property from the backend
 *
 * Covering jcr-2.8.3 spec $10.4.2
 */
class Writing_10_SetPropertyTypesTest extends phpcr_suite_baseCase
{

    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('10_Writing/nodetype');
    }

    public function setUp()
    {
        parent::setUp();

        $this->renewSession();
        $this->node = $this->sharedFixture['session']->getNode('/tests_nodetype_base/numberPropertyNode/jcr:content');
        $this->property = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/longNumber');
    }

    //TODO: have this for all types in PropertyType and each with and without the explicit type parameter. also test node->getPropertyValue for correct type

    public function testCreateString()
    {
        $value = $this->node->setProperty('x', '10.6 test');
        $this->assertSame('10.6 test', $value->getString());
        $this->assertSame(10, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::STRING, $value->getType());

        $this->saveAndRenewSession();
        $value = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/x');
        $this->assertSame('10.6 test', $value->getString());
        $this->assertSame(10, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::STRING, $value->getType());
    }
    public function testCreateValueBinary()
    {
        $bin = $this->node->setProperty('newBinary', 'foobar', PHPCR\PropertyType::BINARY);
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $bin->getType());
        $this->assertEquals('foobar', stream_get_contents($bin->getBinary()));

        $this->saveAndRenewSession();
        $bin = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/newBinary');
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $bin->getType());
        $this->assertEquals('foobar', stream_get_contents($bin->getBinary()));
    }
    public function testCreateValueInt()
    {
        $value = $this->node->setProperty('x', 100);
        $this->assertSame('100', $value->getString());
        $this->assertSame(100, $value->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $value->getType());

        $this->saveAndRenewSession();
        $value = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/x');
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

        $this->saveAndRenewSession();
        $value = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/x');
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
        $this->assertTrue($value->getString() == true, 'wrong string value'); //boolean converted to string must be true
        $this->assertSame(1, $value->getLong(), 'wrong integer value');

        $this->saveAndRenewSession();
        $value = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/x');
        $this->assertEquals(\PHPCR\PropertyType::BOOLEAN, $value->getType(), 'wrong type');
        $this->assertTrue($value->getBoolean(), 'boolean not true');
        $this->assertTrue($value->getString() == true, 'wrong string value'); //boolean converted to string must be true
        $this->assertSame(1, $value->getLong(), 'wrong integer value');
    }
    public function testCreateValueNode()
    {
        $node = $this->sharedFixture['session']->getNode('/tests_nodetype_base/multiValueProperty');
        $value = $this->node->setProperty('x', $node);
        $this->assertEquals(\PHPCR\PropertyType::REFERENCE, $value->getType(), 'wrong type');
        $this->assertEquals($node->getIdentifier(), $value->getString(), 'different uuid');

        $this->saveAndRenewSession();
        $value = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/x');
        $this->assertEquals(\PHPCR\PropertyType::REFERENCE, $value->getType(), 'wrong type');
        $this->assertEquals($node->getIdentifier(), $value->getString(), 'different uuid');
    }
    public function testCreateValueNodeWeak()
    {
        $node = $this->sharedFixture['session']->getRootNode()->getNode('tests_nodetype_base/multiValueProperty');
        $value = $this->node->setProperty('x', $node, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $value->getType());
        $this->assertEquals($node->getIdentifier(), $value->getString());

        $this->saveAndRenewSession();
        $value = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/x');
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $value->getType());
        $this->assertEquals($node->getIdentifier(), $value->getString());
    }
    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testCreateValueNodeNonReferencable()
    {
        $node = $this->sharedFixture['session']->getRootNode()->getNode('tests_nodetype_base/numberPropertyNode/jcr:content');
        $value = $this->node->setProperty('x', $node);
    }
    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testCreateValueNodeNonReferencableWeak()
    {
        $node = $this->sharedFixture['session']->getRootNode()->getNode('tests_nodetype_base/numberPropertyNode/jcr:content');
        $value = $this->node->setProperty('x', $node, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->fail("Exception should be thrown, but ". $value->getString() . " was returned.");
    }
    public function testCreateValueStringType()
    {
        $value = $this->node->setProperty('x', 33, \PHPCR\PropertyType::STRING);
        $this->assertEquals(\PHPCR\PropertyType::STRING, $value->getType());

        $this->saveAndRenewSession();
        $value = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/x');
        $this->assertEquals(\PHPCR\PropertyType::STRING, $value->getType());
    }
    public function testCreateValueDateType()
    {
        $time = time();
        $value = $this->node->setProperty('x', $time, \PHPCR\PropertyType::DATE);
        $this->assertEquals(\PHPCR\PropertyType::DATE, $value->getType());
        $this->assertEquals(date('Y-m-d\TH:i:s.000P', $time), $value->getString());

        $this->saveAndRenewSession();
        $value = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/x');
        $this->assertEquals(\PHPCR\PropertyType::DATE, $value->getType());
        $this->assertEquals(date('Y-m-d\TH:i:s.000P', $time), $value->getString());
    }
    public function testCreateValueUndefined()
    {
        $value = $this->node->setProperty('x', 'some value', \PHPCR\PropertyType::UNDEFINED);
        $this->assertNotEquals(\PHPCR\PropertyType::UNDEFINED, $value->getType(), 'getType should never return UNDEFINED');

        $this->saveAndRenewSession();
        $value = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/x');
        $this->assertNotEquals(\PHPCR\PropertyType::UNDEFINED, $value->getType(), 'getType should never return UNDEFINED');
    }
    public function testCreateValueName()
    {
        $value = $this->node->setProperty('x', 'jcr:name', \PHPCR\PropertyType::NAME);
        $this->assertEquals(\PHPCR\PropertyType::NAME, $value->getType());
        $this->assertEquals('jcr:name', $value->getString());

        $this->saveAndRenewSession();
        $value = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/x');
        $this->assertEquals(\PHPCR\PropertyType::NAME, $value->getType());
        $this->assertEquals('jcr:name', $value->getString());
    }
    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testCreateValueNameInvalidName()
    {
        // "namespace" is not a registered namespace
        $value = $this->node->setProperty('x', 'namespace:name', \PHPCR\PropertyType::NAME);
        $this->saveAndRenewSession();
    }
    public function testCreateValuePath()
    {
        $value = $this->node->setProperty('x', '/some/path', \PHPCR\PropertyType::PATH);
        $this->assertEquals(\PHPCR\PropertyType::PATH, $value->getType());
        $this->assertEquals('/some/path', $value->getString());

        $this->saveAndRenewSession();
        $value = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/x');
        $this->assertEquals(\PHPCR\PropertyType::PATH, $value->getType());
        $this->assertEquals('/some/path', $value->getString());
    }
    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testCreateValuePathInvalidPath()
    {
        // "Space"/ /" is not a valid path (space)
        $value = $this->node->setProperty('x', '/ /', \PHPCR\PropertyType::PATH);
        $this->saveAndRenewSession();
    }
    public function testCreateValueUri()
    {
        $value = $this->node->setProperty('x', 'http://some/uri', \PHPCR\PropertyType::URI);
        $this->assertEquals(\PHPCR\PropertyType::URI, $value->getType());
        $this->assertEquals('http://some/uri', $value->getString());

        $this->saveAndRenewSession();
        $value = $this->sharedFixture['session']->getProperty('/tests_nodetype_base/numberPropertyNode/jcr:content/x');
        $this->assertEquals(\PHPCR\PropertyType::URI, $value->getType());
        $this->assertEquals('http://some/uri', $value->getString());
    }
    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testCreateValueUriInvalidUri()
    {
        $value = $this->node->setProperty('x', '\\This/is\invalid', \PHPCR\PropertyType::URI);
        $this->saveAndRenewSession();
    }
}
