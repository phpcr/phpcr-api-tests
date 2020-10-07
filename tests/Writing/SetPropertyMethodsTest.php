<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Writing;

use InvalidArgumentException;
use PHPCR\NodeInterface;
use PHPCR\PropertyInterface;
use PHPCR\PropertyType;
use PHPCR\Test\BaseCase;
use PHPCR\ValueFormatException;

/**
 * Testing whether node property manipulations work correctly.
 *
 * For every test we do the assertions twice:
 *   - Once after the property has been set in memory
 *   - Once after renewing the session and reading the property from the backend
 *
 * Covering jcr-2.8.3 spec $10.4.2
 */
class SetPropertyMethodsTest extends BaseCase
{
    protected $nodePath = '/tests_general_base/numberPropertyNode/jcr:content';
    protected $propPath = '/tests_general_base/numberPropertyNode/jcr:content/longNumber';

    /**
     * @var PropertyInterface
     */
    private $property;

    public function setUp(): void
    {
        parent::setUp();
        $this->node = $this->session->getNode($this->nodePath);
        $this->property = $this->session->getProperty($this->propPath);
    }

    /**
     * \PHPCR\PropertyInterface::setValue.
     */
    public function testSetValue()
    {
        $this->property->setValue(1024);
        $this->assertEquals(1024, $this->property->getLong());

        $this->saveAndRenewSession();
        $prop = $this->session->getProperty($this->propPath);
        $this->assertEquals(1024, $prop->getLong());
    }

    /**
     * \PHPCR\NodeInterface::setProperty.
     */
    public function testSetPropertyExisting()
    {
        $this->assertTrue($this->node->hasProperty('longNumber'));
        $this->assertSame(1024, $this->node->getPropertyValue('longNumber'));
        $property = $this->node->setProperty('longNumber', 1023);
        $this->assertInstanceOf(PropertyInterface::class, $property);
        $this->assertEquals(1023, $property->getLong());
        $this->assertTrue($property->isModified());
        $this->session->save();
        $this->assertFalse($property->isModified());

        $this->renewSession();
        $prop = $this->session->getNode($this->nodePath)->getProperty('longNumber');
        $this->assertInstanceOf(PropertyInterface::class, $prop);
        $this->assertEquals(1023, $prop->getLong());
    }

    /**
     * @see NodeInterface::setProperty.
     */
    public function testSetPropertyNew()
    {
        $property = $this->node->setProperty('newLongNumber', 1024);
        $this->assertInstanceOf(PropertyInterface::class, $property);
        $this->assertEquals(1024, $property->getLong());
        $this->assertTrue($property->isNew());
        $this->session->save();
        $this->assertFalse($property->isNew());
        $this->assertFalse($property->isModified());

        $this->renewSession();
        $prop = $this->session->getNode($this->nodePath)->getProperty('newLongNumber');
        $this->assertInstanceOf(PropertyInterface::class, $prop);
        $this->assertEquals(1024, $prop->getLong());
    }

    /**
     * Setting a property with the same name as an existing child node.
     *
     * this is valid in jcr 2.0
     * http://www.day.com/specs/jcr/2.0/3_Repository_Model.html#3.4.2.2
     *
     * @see NodeInterface::setProperty
     */
    public function testSetPropertyNewExistingNode()
    {
        $node = $this->session->getNode('/tests_general_base/idExample/jcr:content');
        //$node->getNode('weakreference_source1')->remove();
        $this->session->save();

        $property = $node->setProperty('weakreference_source1', 123);
        $this->assertEquals(123, $property->getLong());
        $this->assertTrue($property->isNew());
        $this->session->save();
        $this->assertFalse($property->isNew());
        $this->assertFalse($property->isModified());

        $this->renewSession();
        $prop = $this->session->getNode('/tests_general_base/idExample/jcr:content')->getProperty('weakreference_source1');
        $this->assertInstanceOf(PropertyInterface::class, $prop);
        $this->assertEquals(123, $prop->getLong());
    }

    /**
     * change type of existing property
     * \PHPCR\NodeInterface::setProperty.
     */
    public function testSetPropertyWithType()
    {
        $prop = $this->node->setProperty('longNumber', 1024.5, PropertyType::LONG);
        $this->assertEquals(1024, $prop->getLong());
        $this->assertEquals(PropertyType::LONG, $prop->getType());

        $this->saveAndRenewSession();
        $prop = $this->session->getNode($this->nodePath)->getProperty('longNumber');
        $this->assertEquals(1024, $prop->getLong());
        $this->assertEquals(PropertyType::LONG, $prop->getType());
    }

    /**
     * add new property
     * \PHPCR\NodeInterface::setProperty.
     */
    public function testSetPropertyNewWithType()
    {
        $prop = $this->node->setProperty('newLongNumber', 102.5, PropertyType::LONG);
        $this->assertEquals(102, $prop->getLong());
        $this->assertEquals(PropertyType::LONG, $prop->getType());
        $this->assertFalse($prop->isMultiple());

        $this->saveAndRenewSession();
        $prop = $this->session->getNode($this->nodePath)->getProperty('newLongNumber');
        $this->assertEquals(102, $prop->getLong());
        $this->assertEquals(PropertyType::LONG, $prop->getType());
        $this->assertFalse($prop->isMultiple());
    }

    public function testSetPropertyMultivalue()
    {
        $prop = $this->node->setProperty('multivalue', [1, 2, 3]);
        $this->assertEquals([1, 2, 3], $this->node->getPropertyValue('multivalue'));
        $this->assertEquals(PropertyType::LONG, $prop->getType());
        $this->assertTrue($prop->isMultiple());

        $this->saveAndRenewSession();
        $node = $this->session->getNode($this->nodePath);
        $prop = $node->getProperty('multivalue');
        $this->assertEquals(PropertyType::LONG, $prop->getType());
        $this->assertTrue($prop->isMultiple());
        $this->assertEquals([1, 2, 3], $prop->getValue());
    }

    public function testSetPropertyMultivalueOne()
    {
        $prop = $this->node->setProperty('multivalue2', [1]);
        $this->assertEquals([1], $this->node->getPropertyValue('multivalue2'));
        $this->assertEquals(PropertyType::LONG, $prop->getType());
        $this->assertTrue($prop->isMultiple());

        $this->saveAndRenewSession();
        $node = $this->session->getNode($this->nodePath);
        $prop = $node->getProperty('multivalue2');
        $this->assertEquals(PropertyType::LONG, $prop->getType());
        $this->assertTrue($prop->isMultiple());
        $this->assertEquals([1], $prop->getValue());
    }

    /**
     * 10.4.2.5 Multi-value Properties and Null
     *
     * Null values must be removed from the list of values.
     */
    public function testSetPropertyMultivalueNull()
    {
        $prop = $this->node->setProperty('multivalue_null', [1, null, 3]);
        $this->assertEquals([1, 3], $this->node->getPropertyValue('multivalue_null'));
        $this->assertEquals(PropertyType::LONG, $prop->getType());
        $this->assertTrue($prop->isMultiple());

        $this->saveAndRenewSession();
        $node = $this->session->getNode($this->nodePath);
        $prop = $node->getProperty('multivalue_null');
        $this->assertEquals(PropertyType::LONG, $prop->getType());
        $this->assertTrue($prop->isMultiple());
        $this->assertEquals([1, 3], $prop->getValue());
    }

    /**
     * 10.4.2.5 Multi-value Properties and Null
     *
     * Null values must be removed from the list of values.
     */
    public function testSetPropertyMultivalueAllNull()
    {
        $prop = $this->node->setProperty('multivalue_allnull', [null, null, null]);
        $this->assertEquals([], $this->node->getPropertyValue('multivalue_allnull'));
        $this->assertEquals(PropertyType::STRING, $prop->getType());
        $this->assertTrue($prop->isMultiple());

        $this->saveAndRenewSession();
        $node = $this->session->getNode($this->nodePath);
        $prop = $node->getProperty('multivalue_allnull');
        $this->assertEquals(PropertyType::STRING, $prop->getType());
        $this->assertTrue($prop->isMultiple());
        $this->assertEquals([], $prop->getValue());
    }

    public function testSetPropertyMultivalueRef()
    {
        $ids = ['842e61c0-09ab-42a9-87c0-308ccc90e6f4', '13543fc6-1abf-4708-bfcc-e49511754b40', '14e18ef3-be20-4985-bee9-7bb4763b31de'];
        $prop = $this->node->setProperty('multiref', $ids, PropertyType::WEAKREFERENCE);
        $this->assertEquals($ids, $this->node->getProperty('multiref')->getString());
        $this->assertEquals(PropertyType::WEAKREFERENCE, $prop->getType());
        $this->assertTrue($prop->isMultiple());

        $this->saveAndRenewSession();
        $node = $this->session->getNode($this->nodePath);
        $prop = $node->getProperty('multiref');
        $this->assertEquals(PropertyType::WEAKREFERENCE, $prop->getType());
        $this->assertTrue($prop->isMultiple());
        $this->assertEquals($ids, $prop->getString());
        $nodes = $prop->getValue();
        $this->assertIsArray($nodes);
        $this->assertCount(3, $nodes);
        $this->assertInstanceOf(NodeInterface::class, reset($nodes));
    }

    public function testPropertyAddValue()
    {
        $prop = $this->node->getProperty('multiBoolean');
        $this->assertEquals([false, true], $prop->getValue());
        $this->assertTrue($prop->isMultiple());
        $prop->addValue(true);
        $this->assertEquals([false, true, true], $prop->getValue());

        $this->saveAndRenewSession();
        $node = $this->session->getNode($this->nodePath);
        $prop = $node->getProperty('multiBoolean');
        $this->assertEquals(PropertyType::BOOLEAN, $prop->getType());
        $this->assertTrue($prop->isMultiple());
        $this->assertEquals([false, true, true], $prop->getValue());
    }

    public function testPropertyAddValueNoMultivalue()
    {
        $this->expectException(ValueFormatException::class);

        $prop = $this->node->getProperty('longNumber');
        $prop->addValue(33);
    }

    public function testPropertySetValueNoMultivalue()
    {
        $this->expectException(ValueFormatException::class);

        $prop = $this->node->getProperty('longNumber');
        $prop->setValue([33, 34]);
    }

    public function testNewNodeSetProperty()
    {
        $node = $this->node->addNode('child');
        $prop = $node->setProperty('p', 'abc');
        $this->assertTrue($this->session->nodeExists($this->nodePath.'/child'));
        $this->assertTrue($this->session->propertyExists($this->nodePath.'/child/p'));
        $this->assertInstanceOf(PropertyInterface::class, $prop);
        $this->assertEquals(PropertyType::STRING, $prop->getType());
        $this->assertEquals('abc', $prop->getString());

        $session = $this->saveAndRenewSession();

        $this->assertTrue($session->nodeExists($this->nodePath.'/child'));
        $this->assertTrue($session->propertyExists($this->nodePath.'/child/p'));

        $node = $session->getNode($this->nodePath.'/child');
        $prop = $node->getProperty('p');

        $this->assertInstanceOf(PropertyInterface::class, $prop);
        $this->assertEquals(PropertyType::STRING, $prop->getType());
        $this->assertEquals('abc', $prop->getString());
    }

    public function testInvalidPropertyName()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->node->setProperty('invalid/name', 123);
    }

    public function testRemoveProperty()
    {
        $nodePath = '/tests_general_base/index.txt/jcr:content';

        $this->assertTrue($this->session->propertyExists($nodePath.'/jcr:data'));

        $node = $this->session->getNode($nodePath);
        $node->setProperty('jcr:data', null);

        $this->saveAndRenewSession();
        $this->assertFalse($this->session->propertyExists($nodePath.'/jcr:data'));
    }
}
