<?php
namespace PHPCR\Tests\Writing;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Testing whether node property manipulations work correctly
 *
 * For every test we do the assertions twice:
 *   - Once after the property has been set in memory
 *   - Once after renewing the session and reading the property from the backend
 *
 * Covering jcr-2.8.3 spec $10.4.2
 */
class SetPropertyMethodsTest extends \PHPCR\Test\BaseCase
{
    protected $nodePath = '/tests_general_base/numberPropertyNode/jcr:content';
    protected $propPath = '/tests_general_base/numberPropertyNode/jcr:content/longNumber';

    public function setUp()
    {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getNode($this->nodePath);
        $this->property = $this->sharedFixture['session']->getProperty($this->propPath);
    }

    /**
     * \PHPCR\PropertyInterface::setValue
     */
    public function testSetValue()
    {
        $this->property->setValue(1024);
        $this->assertEquals(1024, $this->property->getLong());

        $this->saveAndRenewSession();
        $prop = $this->sharedFixture['session']->getProperty($this->propPath);
        $this->assertEquals(1024, $prop->getLong());
    }

    /**
     * \PHPCR\NodeInterface::setProperty
     */
    public function testSetPropertyExisting()
    {
        $this->assertTrue($this->node->hasProperty('longNumber'));
        $property = $this->node->setProperty('longNumber', 1024);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $property);
        $this->assertEquals(1024, $property->getLong());
        $this->assertTrue($property->isModified());
        $this->sharedFixture['session']->save();
        $this->assertFalse($property->isModified());

        $this->renewSession();
        $prop = $this->sharedFixture['session']->getNode($this->nodePath)->getProperty('longNumber');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals(1024, $prop->getLong());
    }


    /**
     * \PHPCR\NodeInterface::setProperty
     */
    public function testSetPropertyNew()
    {
        $property = $this->node->setProperty('newLongNumber', 1024);
        $this->assertInstanceOf('PHPCR\PropertyInterface', $property);
        $this->assertEquals(1024, $property->getLong());
        $this->assertTrue($property->isNew());
        $this->sharedFixture['session']->save();
        $this->assertFalse($property->isNew());
        $this->assertFalse($property->isModified());

        $this->renewSession();
        $prop = $this->sharedFixture['session']->getNode($this->nodePath)->getProperty('newLongNumber');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals(1024, $prop->getLong());
    }

    /**
     * Setting a property with the same name as an existing child node
     *
     * this is valid in jcr 2.0
     * http://www.day.com/specs/jcr/2.0/3_Repository_Model.html#3.4.2.2
     *
     * \PHPCR\NodeInterface::setProperty
     */
    public function testSetPropertyNewExistingNode()
    {
        $node = $this->sharedFixture['session']->getNode('/tests_general_base/idExample/jcr:content');
        //$node->getNode('weakreference_source1')->remove();
        $this->sharedFixture['session']->save();

        $property = $node->setProperty('weakreference_source1', 123);
        $this->assertEquals(123, $property->getLong());
        $this->assertTrue($property->isNew());
        $this->sharedFixture['session']->save();
        $this->assertFalse($property->isNew());
        $this->assertFalse($property->isModified());

        $this->renewSession();
        $prop = $this->sharedFixture['session']->getNode('/tests_general_base/idExample/jcr:content')->getProperty('weakreference_source1');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals(123, $prop->getLong());
    }

    /**
     * change type of existing property
     * \PHPCR\NodeInterface::setProperty
     */
    public function testSetPropertyWithType()
    {
        $prop = $this->node->setProperty('longNumber', 1024.5, \PHPCR\PropertyType::LONG);
        $this->assertEquals(1024, $prop->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $prop->getType());

        $this->saveAndRenewSession();
        $prop = $this->sharedFixture['session']->getNode($this->nodePath)->getProperty('longNumber');
        $this->assertEquals(1024, $prop->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $prop->getType());
    }

    /**
     * add new property
     * \PHPCR\NodeInterface::setProperty
     */
    public function testSetPropertyNewWithType()
    {
        $prop = $this->node->setProperty('newLongNumber', 102.5, \PHPCR\PropertyType::LONG);
        $this->assertEquals(102, $prop->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $prop->getType());
        $this->assertFalse($prop->isMultiple());

        $this->saveAndRenewSession();
        $prop = $this->sharedFixture['session']->getNode($this->nodePath)->getProperty('newLongNumber');
        $this->assertEquals(102, $prop->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $prop->getType());
        $this->assertFalse($prop->isMultiple());
    }

    public function testSetPropertyMultivalue()
    {
        $prop = $this->node->setProperty('multivalue', array(1, 2, 3));
        $this->assertEquals(array(1,2,3), $this->node->getPropertyValue('multivalue'));
        $this->assertEquals(\PHPCR\PropertyType::LONG, $prop->getType());
        $this->assertTrue($prop->isMultiple());

        $this->saveAndRenewSession();
        $node = $this->sharedFixture['session']->getNode($this->nodePath);
        $prop = $node->getProperty('multivalue');
        $this->assertEquals(\PHPCR\PropertyType::LONG, $prop->getType());
        $this->assertTrue($prop->isMultiple());
        $this->assertEquals(array(1,2,3), $prop->getValue('multivalue'));
    }

    public function testPropertyAddValue()
    {
        $prop = $this->node->getProperty('multiBoolean');
        $this->assertEquals(array(false,true), $prop->getValue());
        $this->assertTrue($prop->isMultiple());
        $prop->addValue(true);
        $this->assertEquals(array(false,true,true), $prop->getValue());

        $this->saveAndRenewSession();
        $node = $this->sharedFixture['session']->getNode($this->nodePath);
        $prop = $node->getProperty('multiBoolean');
        $this->assertEquals(\PHPCR\PropertyType::BOOLEAN, $prop->getType());
        $this->assertTrue($prop->isMultiple());
        $this->assertEquals(array(false,true,true), $prop->getValue());
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testPropertyAddValueNoMultivalue()
    {
        $prop = $this->node->getProperty('longNumber');
        $prop->addValue(33);
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testPropertySetValueNoMultivalue()
    {
        $prop = $this->node->getProperty('longNumber');
        $prop->setValue(array(33,34));
    }

    public function testNewNodeSetProperty()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node->addNode('child');
        $prop = $node->setProperty('p', 'abc');
        $this->assertTrue($session->nodeExists($this->nodePath . '/child'));
        $this->assertTrue($session->propertyExists($this->nodePath . '/child/p'));
        $this->assertInstanceOf('\PHPCR\PropertyInterface', $prop);
        $this->assertEquals(\PHPCR\PropertyType::STRING, $prop->getType());
        $this->assertEquals('abc', $prop->getString());

        $this->saveAndRenewSession();
        $session = $this->sharedFixture['session'];
        $this->assertTrue($session->nodeExists($this->nodePath . '/child'));
        $this->assertTrue($session->propertyExists($this->nodePath . '/child/p'));

        $node = $session->getNode($this->nodePath . '/child');
        $prop = $node->getProperty('p');

        $this->assertInstanceOf('\PHPCR\PropertyInterface', $prop);
        $this->assertEquals(\PHPCR\PropertyType::STRING, $prop->getType());
        $this->assertEquals('abc', $prop->getString());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidPropertyName()
    {
        $prop = $this->node->setProperty('invalid/name', 123);
    }

    public function testRemoveProperty()
    {
        $nodePath = '/tests_general_base/index.txt/jcr:content';

        $this->assertTrue($this->sharedFixture['session']->propertyExists($nodePath . '/jcr:data'));

        $node = $this->sharedFixture['session']->getNode($nodePath);
        $node->setProperty('jcr:data', null);

        $this->saveAndRenewSession();
        $this->assertFalse($this->sharedFixture['session']->propertyExists($nodePath . '/jcr:data'));
    }

    //TODO: is this all creation modes? the types are tested in SetPropertyTypes
}
