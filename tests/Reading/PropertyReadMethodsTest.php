<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Reading;
use DateTime;
use PHPCR\ItemNotFoundException;
use PHPCR\ItemVisitorInterface;
use PHPCR\NodeInterface;
use PHPCR\PropertyInterface;
use PHPCR\PropertyType;
use PHPCR\SessionInterface;
use PHPCR\Test\BaseCase;
use PHPCR\ValueFormatException;

/**
 * javax.jcr.Property read methods
 * TODO: CONSTANTS.
 *
 * PropertyWriteMethods: isModified, refresh, save, remove, setValue (in many variants)
 *
 * Value conversions are tested according to
 * http://www.day.com/specs/jcr/2.0/3_Repository_Model.html#3.6.4%20Property%20Type%20Conversion
 */
class PropertyReadMethodsTest extends BaseCase
{
    /**
     * @var PropertyInterface
     */
    protected $rootNode;

    /**
     * @var PropertyInterface
     */
    protected $node;

    /**
     * @var PropertyInterface
     */
    protected $createdProperty;

    /**
     * @var PropertyInterface
     */
    protected $dateProperty;

    /**
     * @var PropertyInterface
     */
    protected $valProperty;

    /**
     * @var PropertyInterface
     */
    protected $multiValueProperty;

    public function setUp()
    {
        parent::setUp();

        $this->node = $this->rootNode->getNode('tests_general_base');
        $this->createdProperty = $this->node->getProperty('jcr:created');
        $this->dateProperty = $this->node->getProperty('index.txt/jcr:content/mydateprop');
        $this->valProperty = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('foo');
        $this->multiValueProperty = $this->node->getNode('index.txt/jcr:content')->getProperty('multivalue');
    }

    /*** item base methods for property ***/
    public function testGetAncestor()
    {
        $ancestor = $this->dateProperty->getAncestor(0);
        $this->assertNotNull($ancestor);
        $this->assertInstanceOf(NodeInterface::class, $ancestor);
        $this->assertTrue($this->rootNode->isSame($ancestor));

        $ancestor = $this->dateProperty->getAncestor(1);
        $this->assertNotNull($ancestor);
        $this->assertInstanceOf(NodeInterface::class, $ancestor);
        $this->assertTrue($this->node->isSame($ancestor));

        // Self
        $ancestor = $this->dateProperty->getAncestor($this->dateProperty->getDepth());
        $this->assertNotNull($ancestor);
        $this->assertInstanceOf(PropertyInterface::class, $ancestor);
        $this->assertTrue($this->dateProperty->isSame($ancestor));
    }

    public function testGetDepthProperty()
    {
        $this->assertEquals(2, $this->createdProperty->getDepth());
        $this->assertEquals(4, $this->dateProperty->getDepth());
    }

    public function testGetParent()
    {
        $parent = $this->createdProperty->getParent();
        $this->assertNotNull($parent);
        $this->assertTrue($this->node->isSame($parent));
    }

    public function testGetPath()
    {
        $path = $this->createdProperty->getPath();
        $this->assertEquals('/tests_general_base/jcr:created', $path);
    }

    public function testGetSession()
    {
        $sess = $this->createdProperty->getSession();
        $this->assertInstanceOf(SessionInterface::class, $sess);
        //how to further check if we got the right session?
    }

    public function testIsNew()
    {
        $this->assertFalse($this->createdProperty->isNew());
    }

    public function testIsNode()
    {
        $this->assertFalse($this->createdProperty->isNode());
    }
    //isSame implicitely tested in the path/parent tests

    public function testAccept()
    {
        $mock = $this->getMockBuilder(ItemVisitorInterface::class)
            ->setMethods(['visit'])
            ->getMock()
        ;

        $mock->expects($this->once())
            ->method('visit')
            ->with($this->equalTo($this->createdProperty));

        $this->createdProperty->accept($mock);
    }

    public function testGetPropertyName()
    {
        $name = $this->createdProperty->getName();
        $this->assertEquals('jcr:created', $name);
    }

    /*** property specific methods ***/

    public function testGetValue()
    {
        $this->assertEquals(PropertyType::DATE, $this->dateProperty->getType(), 'Expecting date type');
        $val = $this->dateProperty->getValue();
        $this->assertInstanceOf(DateTime::class, $val);
    }

    public function testGetValueMulti()
    {
        $vals = $this->multiValueProperty->getValue();
        $this->assertInternalType('array', $vals);
        foreach ($vals as $val) {
            $this->assertNotNull($val);
        }
    }

    /**
     * get a date property as string.
     *
     * everything can be converted to string
     */
    public function testGetString()
    {
        $expectedStr = '2011-04-21T14:34:20+01:00'; //date precision might not be down to milliseconds
        $str = $this->dateProperty->getString();
        $this->assertInternalType('string', $str);

        $this->assertEqualDateString($expectedStr, $str);
        $str = $this->valProperty->getString();
        $this->assertInternalType('string', $str);
        $this->assertEquals('bar', $str);
    }

    public function testJcrCreated()
    {
        $date = $this->createdProperty->getDate();
        $this->assertInstanceOf(DateTime::class, $date);
        // we can not assume a specific age here, as the fixtures might be anything
    }

    public function testGetStringMulti()
    {
        $arr = $this->multiValueProperty->getString();
        $this->assertInternalType('array', $arr);
        foreach ($arr as $v) {
            $this->assertInternalType('string', $v);
        }
    }

    /**
     * everything can be converted to string and then to binary.
     */
    public function testGetBinary()
    {
        $bin = $this->valProperty->getBinary();
        $this->assertTrue(is_resource($bin));
        $str = $this->valProperty->getString();
        $this->assertEquals($str, stream_get_contents($bin));
        $this->assertEquals($this->valProperty->getLength(), strlen($str));

        $prop = $this->node->getProperty('index.txt/jcr:content/jcr:data');
        $this->assertEquals(PropertyType::BINARY, $prop->getType(), 'Expected binary type');
        $bin = $prop->getValue();
        $this->assertTrue(is_resource($bin));
        $this->assertNotNull(stream_get_contents($bin));
        fclose($bin);
    }

    public function testGetBinaryMulti()
    {
        $prop = $this->node->getProperty('index.txt/jcr:content/multidata');
        $this->assertEquals(PropertyType::BINARY, $prop->getType(), 'Expected binary type');
        $arr = $prop->getValue();
        $this->assertInternalType('array', $arr);
        foreach ($arr as $bin) {
            $this->assertTrue(is_resource($bin));
            $this->assertNotNull(stream_get_contents($bin));
        }
    }

    public function testGetLong()
    {
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $num = $prop->getLong();
        $this->assertInternalType('integer', $num);
        $this->assertEquals(999, $num);
    }
    public function testGetLongMulti()
    {
        $arr = $this->multiValueProperty->getLong();
        $this->assertInternalType('array', $arr);
        foreach ($arr as $v) {
            $this->assertInternalType('integer', $v);
        }
    }
    /**
     * NAME can not be converted to long.
     */
    public function testGetLongValueFormatException()
    {
        $this->expectException(ValueFormatException::class);

        $this->node->getProperty('jcr:primaryType')->getLong();
    }

    public function testGetDouble()
    {
        $nv = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $number = $nv->getDouble();
        $this->assertInternalType('float', $number);
        $this->assertEquals(999, $number);
    }
    public function testGetDoubleMulti()
    {
        $arr = $this->multiValueProperty->getDouble();
        $this->assertInternalType('array', $arr);
        foreach ($arr as $v) {
            $this->assertInternalType('float', $v);
        }
    }

    /**
     * NAME can not be converted to double.
     */
    public function testGetDoubleValueFormatException()
    {
        $this->expectException(ValueFormatException::class);

        $this->node->getProperty('jcr:primaryType')->getDouble();
    }

    public function testGetDecimal()
    {
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');

        $num = $prop->getDecimal();
        //we do not have an equivalent to java.math.BigDecimal. PHPCR uses strings suitable for BC Math
        $this->assertInternalType('string', $num);
        $this->assertEquals(999, $num);
    }
    /**
     * NAME can not be converted to decimal.
     */
    public function testGetDecimalValueFormatException()
    {
        $this->expectException(ValueFormatException::class);

        $this->node->getProperty('jcr:primaryType')->getDecimal();
    }

    /**
     * The PHP Implementation requires that getDouble and getDecimal return the same.
     */
    public function testGetDoubleAndDecimalSame()
    {
        //TODO: this is wrong, decimal must be a string that can be handled with the bc_math extension
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $double = $prop->getDouble();
        $decimal = $prop->getDecimal();
        $this->assertEquals($double, $decimal);
    }

    public function testGetDate()
    {
        $date = $this->dateProperty->getDate();
        $this->assertInstanceOf(DateTime::class, $date);
        $this->assertEquals('1303392860', $date->format('U'));
    }

    public function testGetDateMulti()
    {
        $multidate = $this->node->getProperty('index.txt/jcr:content/multidate');
        $this->assertEquals(PropertyType::DATE, $multidate->getType());
        $arr = $multidate->getValue();
        $this->assertInternalType('array', $arr);

        foreach ($arr as $v) {
            $this->assertInstanceOf(DateTime::class, $v);
        }
        //check correct values and sort order
        $expectedArray = [
                new DateTime('2011-04-22T14:34:20+01:00'),
                new DateTime('2011-10-23T14:34:20+01:00'),
                new DateTime('2010-10-23T14:34:20+01:00'),
        ];

        foreach ($expectedArray as $key => $expected) {
            $this->assertEqualDateTime($expected, $arr[$key]);
        }
    }

    /**
     * Arbitrary string can not be converted to date.
     */
    public function testGetDateValueFormatException()
    {
        $this->expectException(ValueFormatException::class);

        $this->valProperty->getDate();
    }

    public function testGetBoolean()
    {
        $this->assertTrue($this->valProperty->getBoolean());
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('yesOrNo');
        $this->assertSame('true', $prop->getValue());
        $this->assertTrue($prop->getBoolean());

        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('thisIsNo');
        $this->assertFalse($prop->getBoolean());
        // php interprets everything as true except null, 0, '' and boolean false. thus even the string "false" is true.
        // we require getString to return something that evaluates to false (the empty string makes sense)
        $this->assertTrue(!$prop->getString(), 'boolean false returned as string should evaluate to php <false>');

        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('thisIsYes');
        $this->assertTrue($prop->getBoolean());
        $this->assertFalse(!$prop->getString());
    }

    public function testGetBooleanMulti()
    {
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('multiBoolean');
        $arr = $prop->getBoolean();
        $this->assertInternalType('array', $arr);
        foreach ($arr as $v) {
            $this->assertInternalType('boolean', $v);
        }
        $this->assertCount(2, $arr);
        $this->assertFalse($arr[0]);
        $this->assertTrue($arr[1]);
    }
    /**
     * NAME can not be converted to boolean.
     */
    public function testGetBooleanValueFormatException()
    {
        $this->expectException(ValueFormatException::class);

        $this->node->getProperty('jcr:primaryType')->getBoolean();
    }

    public function testGetNode()
    {
        $property = $this->node->getProperty('numberPropertyNode/jcr:content/ref');
        $idnode = $this->node->getNode('idExample');

        //TODO: is the type wrong because we import a document view? would it work with system view?

        $this->assertEquals(PropertyType::REFERENCE, $property->getType(), 'Expecting REFERENCE type');
        $target = $property->getNode();
        $this->assertInstanceOf(NodeInterface::class, $target);
        $this->assertSame($target, $idnode);
    }

    //TODO: testGetNodeWeak, testGetNodePath

    public function testGetNodeMulti()
    {
        $multiref = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('multiref');

        $arr = $multiref->getNode();
        $this->assertInternalType('array', $arr);
        foreach ($arr as $v) {
            $this->assertInstanceOf(NodeInterface::class, $v);
        }
    }

    public function testGetNodeValueFormatException()
    {
        $this->expectException(ValueFormatException::class);

        $this->dateProperty->getNode();
    }

    /**
     * Only nodes but not properties can be found with getNode.
     */
    public function testGetNodePropertyItemNotFound()
    {
        $this->expectException(ItemNotFoundException::class);

        $propertyPath = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('propertyPath');
        $propertyPath->getNode();
    }

    public function testGetNodePathItemNotFound()
    {
        $this->expectException(ItemNotFoundException::class);

        $invalidPath = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('invalidPath');
        $invalidPath->getNode();
    }

    public function testGetNodeWeakItemNotFound()
    {
        $this->expectException(ItemNotFoundException::class);

        $weak = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('invalidweak');
        $weak->getNode();
    }

    /**
     * PATH property, the path references another property.
     */
    public function testGetProperty()
    {
        $propertyPath = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('propertyPath');
        $property = $propertyPath->getProperty();
        $this->assertEquals('/tests_general_base/numberPropertyNode/jcr:content/foo', $property->getPath());
        $this->assertSame($this->valProperty, $property);
    }

    public function testGetPropertyMulti()
    {
        $propertyPath = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('multiPropertyPath');
        $properties = $propertyPath->getProperty();
        $this->assertCount(2, $properties);

        foreach ($properties as $prop) {
            $this->assertInstanceOf(PropertyInterface::class, $prop);
        }

        $this->assertEquals('/tests_general_base/numberPropertyNode/jcr:content/foo', $properties[0]->getPath());
        $this->assertEquals('/tests_general_base/index.txt/jcr:content/mydateprop', $properties[1]->getPath());

        $expected = [$this->valProperty, $this->dateProperty];
        $this->assertEquals($expected, $properties, '', 0, 3);
    }

    public function testGetPropertyNoPath()
    {
        $this->expectException(ValueFormatException::class);

        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $this->assertEquals(PropertyType::LONG, $prop->getType());
        $prop->getProperty();
    }

    public function testGetLength()
    {
        $this->assertEquals(29, $this->dateProperty->getLength());
    }

    //binary length is tested in BinaryReadMethodsTest

    // testGetLengthUnsuccessfull (return -1 on getLength) "should never happen" so no test

    public function testGetLengthMultivalue()
    {
        $this->assertEquals([3, 1, 3], $this->multiValueProperty->getLength());
    }

    //FIXME: we most definitely should not create properties here but read existing ones!

    public function testGetTypeString()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newString', 'foobar', PropertyType::STRING);
        $this->assertEquals(PropertyType::STRING, $node->getProperty('newString')->getType(), 'Expecting string type');
    }

    public function testGetTypeBinary()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBin', 'foobar', PropertyType::BINARY);
        $this->assertEquals(PropertyType::BINARY, $node->getProperty('newBin')->getType(), 'Expecting binary type');
    }

    public function testGetTypeLong()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newLong', 3, PropertyType::LONG);
        $this->assertEquals(PropertyType::LONG, $node->getProperty('newLong')->getType(), 'Expecting long type');
    }

    public function testGetTypeDouble()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newDouble', 3.5, PropertyType::DOUBLE);
        $this->assertEquals(PropertyType::DOUBLE, $node->getProperty('newDouble')->getType(), 'Expecting double type');
    }

    public function testGetTypeDate()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newDate', '2009-04-27T13:01:04.758+02:00', PropertyType::DATE);
        $this->assertEquals(PropertyType::DATE, $node->getProperty('newDate')->getType(), 'Expecting date type');
    }

    public function testGetTypeBoolean()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBool', true, PropertyType::BOOLEAN);
        $this->assertEquals(PropertyType::BOOLEAN, $node->getProperty('newBool')->getType(), 'Expecting boolean type');
    }

    public function testGetTypeName()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newName', 'foobar', PropertyType::NAME);
        $this->assertEquals(PropertyType::NAME, $node->getProperty('newName')->getType(), 'Expecting NAME type');
    }

    public function testGetTypePath()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newPath', 'foobar', PropertyType::PATH);
        $this->assertEquals(PropertyType::PATH, $node->getProperty('newPath')->getType(), 'Expecting PATH type');
    }

    public function testGetTypeReference()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newRef', '842e61c0-09ab-42a9-87c0-308ccc90e6f4', PropertyType::REFERENCE);
        $this->assertEquals(PropertyType::REFERENCE, $node->getProperty('newRef')->getType(), 'Expecting REFERENCE type');
    }

    public function testGetTypeWeakReference()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newWeakRef', '842e61c0-09ab-42a9-87c0-308ccc90e6f4', PropertyType::WEAKREFERENCE);
        $this->assertEquals(PropertyType::WEAKREFERENCE, $node->getProperty('newWeakRef')->getType(), 'Expecting WEAKREFERENCE type');
    }

    public function testIterator()
    {
        $this->assertTraversableImplemented($this->valProperty);

        $results = 0;
        foreach ($this->valProperty as $value) {
            $results++;
            $this->assertInternalType('string', $value);
            $this->assertEquals('bar', $value);
        }

        $this->assertEquals(1, $results, 'Single value iterator must have exactly one entry');
    }

    public function testIteratorMulti()
    {
        $this->assertTraversableImplemented($this->multiValueProperty);
        $expected = [200, 0, 100];
        $returned = [];
        foreach ($this->multiValueProperty as $value) {
            $returned[] = $value;
        }
        $this->assertEquals($expected, $returned);
    }
}
