<?php
require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
 * javax.jcr.Property read methods
 * TODO: CONSTANTS
 *
 * PropertyWriteMethods: isModified, refresh, save, remove, setValue (in many variants)
 */
class Reading_5_PropertyReadMethodsTest extends phpcr_suite_baseCase
{
    protected $rootNode;
    protected $node;
    protected $property;
    protected $multiProperty;

    static public function  setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('general/base');
    }

    public function setUp()
    {
        parent::setUp();
        $this->node = $this->rootNode->getNode('tests_general_base');
        $this->createdProperty = $this->node->getProperty('jcr:created');
        $this->dateProperty = $this->node->getProperty('index.txt/jcr:content/mydateprop');
        $this->valProperty = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('foo');
        $this->multiProperty = $this->node->getNode('multiValueProperty')->getProperty('jcr:mixinTypes');
    }

    /*** item base methods for property ***/
    function testGetAncestor()
    {
        $ancestor = $this->multiProperty->getAncestor(0);
        $this->assertNotNull($ancestor);
        $this->assertInstanceOf('PHPCR\ItemInterface', $ancestor);
        $this->assertTrue($this->rootNode->isSame($ancestor));

        $ancestor = $this->multiProperty->getAncestor(1);
        $this->assertNotNull($ancestor);
        $this->assertInstanceOf('PHPCR\ItemInterface', $ancestor);
        $this->assertTrue($this->node->isSame($ancestor));

        //self
        $ancestor = $this->multiProperty->getAncestor($this->multiProperty->getDepth());
        $this->assertNotNull($ancestor);
        $this->assertInstanceOf('PHPCR\ItemInterface', $ancestor);
        $this->assertTrue($this->multiProperty->isSame($ancestor));
    }
    function testGetDepthProperty()
    {
        $this->assertEquals(2, $this->createdProperty->getDepth());
        $this->assertEquals(3, $this->multiProperty->getDepth());
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
        $this->assertInstanceOf('PHPCR\SessionInterface', $sess);
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
        $mock = $this->getMock('PHPCR\ItemVisitorInterface', array('visit'));
        $mock->expects($this->once())
            ->method('visit')
            ->with($this->equalTo($this->createdProperty));

        $this->createdProperty->accept($mock);
    }

    function testGetName()
    {
        $name = $this->createdProperty->getName();
        $this->assertEquals('jcr:created', $name);
    }

    /*** property specific methods ***/

    public function testGetValue()
    {
        $this->assertEquals(\PHPCR\PropertyType::DATE, $this->dateProperty->getType(), 'Expecting date type');
        $val = $this->dateProperty->getValue();
        $this->assertInstanceOf('DateTime', $val);
    }
    public function testGetValueMulti()
    {
        $vals = $this->multiProperty->getValue();
        $this->assertInternalType('array', $vals);
        foreach ($vals as $val) {
            $this->assertNotNull($val);
        }
    }

    /**
     * get a date property as string
     *
     * (do NOT use jcr:created as this might be controlled by the repository
     */
    public function testGetString()
    {
        $expectedStr = '2011-04-21T14:34:20'; //date precision might not be down to milliseconds
        $str = $this->dateProperty->getString();
        $this->assertInternalType('string', $str);
        $this->assertStringStartsWith($expectedStr, $str);

        $str = $this->valProperty->getString();
        $this->assertInternalType('string', $str);
        $this->assertEquals('bar', $str);

    }

    public function testJcrCreated()
    {
        $expectedStr = date('o-m-d\T');
        $str = $this->createdProperty->getString();
        $this->assertInternalType('string', $str);
        $this->assertStringStartsWith($expectedStr, $str, "jcr:created should be current date as fixture was just imported");
    }

    public function testGetStringMulti()
    {
        $arr = $this->multiProperty->getString();
        $this->assertInternalType('array', $arr);
        foreach ($arr as $v) {
            $this->assertInternalType('string', $v);
        }
    }

    public function testGetBinary()
    {
        $bin = $this->valProperty->getBinary();
        $this->assertTrue(is_resource($bin));
        $str = $this->valProperty->getString();
        $this->assertEquals($str, stream_get_contents($bin));
        $this->assertEquals($this->valProperty->getLength(), strlen($str));

        $prop = $this->node->getProperty('index.txt/jcr:content/jcr:data');
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $prop->getType(), 'Expected binary type');
        $bin = $prop->getValue();
        $this->assertTrue(is_resource($bin));
        $this->assertNotNull(stream_get_contents($bin));
        fclose($bin);
    }

    public function testGetBinaryMulti()
    {
        $this->markTestIncomplete('TODO: Figure out how the fixture must look for jackrabbit to import multivalue binaries');

        $prop = $this->node->getProperty('index.txt/jcr:content/multidata');
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $prop->getType(), 'Expected binary type');
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
        $arr = $this->multiProperty->getLong();
        $this->assertInternalType('array', $arr);
        foreach ($arr as $v) {
            $this->assertInternalType('integer', $v);
        }
    }

    //everything can be converted to long, no ValueFormatException

    //under normal circumstances, no RepositoryException

    public function testGetDouble()
    {
        $nv = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $number = $nv->getDouble();
        $this->assertInternalType('float', $number);
        $this->assertEquals(999, $number);
    }

    public function testGetDoubleMulti()
    {
        $arr = $this->multiProperty->getDouble();
        $this->assertInternalType('array', $arr);
        foreach ($arr as $v) {
            $this->assertInternalType('float', $v);
        }
    }

    //everything can be converted to double, no ValueFormatException

    //under normal circumstances, no RepositoryException

    public function testGetDecimal()
    {
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $num = $prop->getDecimal();
        //we do not have an equivalent to java.math.BigDecimal. PHPCR uses strings suitable for BC Math
        $this->assertInternalType('string', $num);
        $this->assertEquals(999, $num);
    }

    /**
     * The PHP Implementation requires that getDouble and getDecimal return the same
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
        $this->assertInstanceOf('DateTime', $date);
        $this->assertEquals('1303392860', $date->format('U'));
    }

    public function testGetDateMulti()
    {
        $multidate = $this->node->getProperty('index.txt/jcr:content/multidate');
        $this->assertEquals(\PHPCR\PropertyType::DATE, $multidate->getType());
        $arr = $multidate->getValue();
        $this->assertInternalType('array', $arr);
        foreach ($arr as $v) {
            $this->assertInstanceOf('DateTime', $v);
        }
        //check correct values and sort order
        $expected = array(
                new DateTime('2011-04-22T14:34:20+01:00'),
                new DateTime('2011-10-23T14:34:20+01:00'),
                new DateTime('2010-10-23T14:34:20+01:00'));
        $this->assertEquals($expected, $arr);
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetDateMultiValueFormatException()
    {
        $this->multiProperty->getDate();
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetDateValueFormatException()
    {
        $this->valProperty->getDate();
    }

    // under normal circumstances, no RepositoryException

    public function testGetBoolean()
    {
        $this->assertTrue($this->dateProperty->getBoolean());
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('yesOrNo');
        $this->assertSame('true', $prop->getValue());
        $this->assertTrue($prop->getBoolean());

        $prop = $this->sharedFixture['session']->getRootNode()->getNode('tests_general_base/index.txt/jcr:content')->getProperty('zeronumber');
        $this->assertFalse($prop->getBoolean(), 'this boolean property should be false');
        $this->assertTrue(! $prop->getString(), 'boolean false as string should be false');
    }

    public function testGetBooleanMulti()
    {
        $arr = $this->multiProperty->getBoolean();
        $this->assertInternalType('array', $arr);
        foreach ($arr as $v) {
            $this->assertInternalType('boolean', $v);
        }
    }

    //no boolean value conversion

    public function testGetNode()
    {
        $property = $this->node->getProperty('numberPropertyNode/jcr:content/ref');
        $idnode = $this->node->getNode('idExample');

        //TODO: is the type wrong because we import a document view? would it work with system view?

        $this->assertEquals(\PHPCR\PropertyType::REFERENCE, $property->getType(), 'Expecting REFERENCE type');
        $target = $property->getNode();
        $this->assertInstanceOf('PHPCR\NodeInterface', $target);
        $this->assertEquals($target, $idnode);
    }

    //TODO: testGetNodeWeak, testGetNodePath

    public function testGetNodeMulti()
    {
        $multiref = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('multiref');

        $arr = $multiref->getNode();
        $this->assertInternalType('array', $arr);
        foreach ($arr as $v) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $v);
        }
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetNodeValueFormatException()
    {
        $node = $this->dateProperty->getNode();
    }
    /**
     * only nodes but not properties can be found with getNode
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetNodePropertyItemNotFound()
    {
        $propertyPath = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('propertyPath');
        $propertyPath->getNode();
    }
    /**
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetNodePathItemNotFound()
    {
        $invalidPath = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('invalidPath');
        $invalidPath->getNode();
    }

    /**
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetNodeWeakItemNotFound()
    {
        $weak = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('invalidweak');
        $weak->getNode();
    }

    /**
     * PATH property, the path references another property
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
        $this->assertEquals(2, count($properties));
        foreach($properties as $prop) {
            $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        }
        $this->assertEquals('/tests_general_base/numberPropertyNode/jcr:content/foo', $properties[0]->getPath());
        $this->assertEquals('/tests_general_base/index.txt/jcr:content/mydateprop', $properties[1]->getPath());

        $expected = array($this->valProperty, $this->dateProperty);
        $this->assertSame($expected, $properties);
    }

    public function testGetLength()
    {
        $this->assertEquals(29, $this->dateProperty->getLength());
    }

    //binary length is tested in BinaryReadMethodsTest

    // testGetLengthUnsuccessfull (return -1 on getLength) "should never happen" so no test

    public function testGetLengthMultivalue()
    {
        $this->assertEquals(array(17, 15), $this->multiProperty->getLength());
    }

    //FIXME: we most definitely should not create properties here but read existing ones!

    public function testGetTypeString()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newString', 'foobar', \PHPCR\PropertyType::STRING);
        $this->assertEquals(\PHPCR\PropertyType::STRING, $node->getProperty('newString')->getType(), 'Expecting string type');
    }

    public function testGetTypeBinary()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBin', 'foobar', \PHPCR\PropertyType::BINARY);
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $node->getProperty('newBin')->getType(), 'Expecting binary type');
    }

    public function testGetTypeLong()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newLong', 3, \PHPCR\PropertyType::LONG);
        $this->assertEquals(\PHPCR\PropertyType::LONG, $node->getProperty('newLong')->getType(), 'Expecting long type');
    }

    public function testGetTypeDouble()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newDouble', 3.5, \PHPCR\PropertyType::DOUBLE);
        $this->assertEquals(\PHPCR\PropertyType::DOUBLE, $node->getProperty('newDouble')->getType(), 'Expecting double type');
    }

    public function testGetTypeDate()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newDate', '2009-04-27T13:01:04.758+02:00', \PHPCR\PropertyType::DATE);
        $this->assertEquals(\PHPCR\PropertyType::DATE, $node->getProperty('newDate')->getType(), 'Expecting date type');
    }

    public function testGetTypeBoolean()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBool', true, \PHPCR\PropertyType::BOOLEAN);
        $this->assertEquals(\PHPCR\PropertyType::BOOLEAN, $node->getProperty('newBool')->getType(), 'Expecting boolean type');
    }

    public function testGetTypeName()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newName', 'foobar', \PHPCR\PropertyType::NAME);
        $this->assertEquals(\PHPCR\PropertyType::NAME, $node->getProperty('newName')->getType(), 'Expecting NAME type');
    }

    public function testGetTypePath()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newPath', 'foobar', \PHPCR\PropertyType::PATH);
        $this->assertEquals(\PHPCR\PropertyType::PATH, $node->getProperty('newPath')->getType(), 'Expecting PATH type');
    }

    public function testGetTypeReference()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newRef', '842e61c0-09ab-42a9-87c0-308ccc90e6f4', \PHPCR\PropertyType::REFERENCE);
        $this->assertEquals(\PHPCR\PropertyType::REFERENCE, $node->getProperty('newRef')->getType(), 'Expecting REFERENCE type');
    }

    public function testGetTypeWeakReference()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newWeakRef', '842e61c0-09ab-42a9-87c0-308ccc90e6f4', \PHPCR\PropertyType::WEAKREFERENCE);
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $node->getProperty('newWeakRef')->getType(), 'Expecting WEAKREFERENCE type');
    }

    public function testIterator() {
        $this->assertTraversableImplemented($this->valProperty);

        $results = 0;
        foreach ($this->valProperty as $value) {
            $results++;
            $this->assertInternalType('string', $value);
            $this->assertEquals('bar', $value);
        }

        $this->assertEquals(1, $results, 'Single value iterator must have exactly one entry');
    }

    public function testIteratorMulti() {
        $this->assertTraversableImplemented($this->multiProperty);
        $expected = array('mix:referenceable', 'mix:versionable');
        $returned = array();
        foreach ($this->multiProperty as $value) {
            $returned[] = $value;
        }
        $this->assertEquals($expected, $returned);
    }

}
