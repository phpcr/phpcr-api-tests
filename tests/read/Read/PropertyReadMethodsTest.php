<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * javax.jcr.Property read methods
 * TODO: CONSTANTS
 *
 * PropertyWriteMethods: isModified, refresh, save, remove, setValue (in many variants)
 */
class Read_Read_PropertyReadMethodsTest extends jackalope_baseCase
{
    protected $rootNode;
    protected $node;
    protected $property;
    protected $multiProperty;

    static public function  setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('read/read/base');
    }

    public function setUp()
    {
        parent::setUp();
        $this->node = $this->rootNode->getNode('tests_read_access_base');
        $this->property = $this->node->getProperty('jcr:created');
        $this->valProperty = $this->sharedFixture['session']->getRootNode()->getNode('tests_read_access_base/numberPropertyNode/jcr:content')->getProperty('foo');
        $this->multiProperty = $this->node->getNode('multiValueProperty')->getProperty('jcr:mixinTypes');
        $this->dateProperty = $this->node->getNode('index.txt/jcr:content')->getProperty('jcr:lastModified');
    }

    /*** item base methods for property ***/
    function testGetAncestor()
    {
        $ancestor = $this->multiProperty->getAncestor(0);
        $this->assertNotNull($ancestor);
        $this->assertType('PHPCR\ItemInterface', $ancestor);
        $this->assertTrue($this->rootNode->isSame($ancestor));

        $ancestor = $this->multiProperty->getAncestor(1);
        $this->assertNotNull($ancestor);
        $this->assertType('PHPCR\ItemInterface', $ancestor);
        $this->assertTrue($this->node->isSame($ancestor));

        //self
        $ancestor = $this->multiProperty->getAncestor($this->multiProperty->getDepth());
        $this->assertNotNull($ancestor);
        $this->assertType('PHPCR\ItemInterface', $ancestor);
        $this->assertTrue($this->multiProperty->isSame($ancestor));
    }
    function testGetDepthProperty()
    {
        $this->assertEquals(2, $this->property->getDepth());
        $deepnode = $this->node->getNode('multiValueProperty');
        $this->assertEquals(3, $this->multiProperty->getDepth());
    }
    public function testGetParent()
    {
        $parent = $this->property->getParent();
        $this->assertNotNull($parent);
        $this->assertTrue($this->node->isSame($parent));
    }
    public function testGetPath()
    {
        $path = $this->property->getPath();
        $this->assertEquals('/tests_read_access_base/jcr:created', $path);
    }
    public function testGetSession()
    {
        $sess = $this->property->getSession();
        $this->assertType('PHPCR\SessionInterface', $sess);
        //how to further check if we got the right session?
    }
    public function testIsNew()
    {
        $this->assertFalse($this->property->isNew());
    }
    public function testIsNode()
    {
        $this->assertFalse($this->property->isNode());
    }
    //isSame implicitely tested in the path/parent tests

    public function testAccept()
    {
        $mock = $this->getMock('PHPCR\ItemVisitorInterface', array('visit'));
        $mock->expects($this->once())
            ->method('visit')
            ->with($this->equalTo($this->property));

        $this->property->accept($mock);
    }

    function testGetName()
    {
        $name = $this->property->getName();
        $this->assertNotNull($name);
        $this->assertEquals('jcr:created', $name);
    }

    /*** property specific methods ***/

    //TODO: check for all properties if the type is correctly read from backend

    public function testGetValue()
    {
        $val = $this->property->getValue();
        $this->assertType('DateTime', $val);
    }
    public function testGetValueMulti()
    {
        $vals = $this->multiProperty->getValue();
        $this->assertType('array', $vals);
        foreach ($vals as $val) {
            $this->assertNotNull($val);
        }
    }

    public function testGetString()
    {
        $expectedStr = date('o-m-d\T');
        $str = $this->property->getString();
        $this->assertType('string', $str);
        $this->assertStringStartsWith($expectedStr, $str, "jcr:created should start with a date, when converted from date to string.");

        $str = $this->valProperty->getString();
        $this->assertType('string', $str);
        $this->assertEquals('bar', $str);
    }

    public function testGetStringMulti()
    {
        $arr = $this->multiProperty->getString();
        $this->assertType('array', $arr);
        foreach($arr as $v) {
            $this->assertType('string', $v);
        }
    }

    public function testGetBinary()
    {
        $bin = $this->valProperty->getBinary();
        $str = $this->valProperty->getString();
        $this->assertEquals(stream_get_contents($bin), $str);
        $this->assertEquals($this->valProperty->getLength(), strlen($str));
    }

    public function testGetBinaryMulti()
    {
        $this->markTestIncomplete('TODO: Figure how multivalue binary properties can be set');
    }

    public function testGetLong()
    {
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $num = $prop->getLong();
        $this->assertType('int', $num);
        $this->assertEquals(999, $num);
    }

    public function testGetLongMulti()
    {
        $arr = $this->multiProperty->getLong();
        $this->assertType('array', $arr);
        foreach($arr as $v) {
            $this->assertType('integer', $v);
        }
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetLongValueFormatException()
    {
        $this->markTestIncomplete('Have a property that can not be converted to this type');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetLongRepositoryException()
    {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
    }

    public function testGetDouble()
    {
        $nv = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $number = $nv->getDouble();
        $this->assertType('float', $number);
        $this->assertEquals(999, $number);
    }

    public function testGetDoubleMulti()
    {
        $arr = $this->multiProperty->getDouble();
        $this->assertType('array', $arr);
        foreach($arr as $v) {
            $this->assertType('float', $v);
        }
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetDoubleValueFormatException()
    {
        $this->markTestIncomplete('Have a property that can not be converted to this type');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetDoubleRepositoryException()
    {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
    }

    public function testGetDecimal()
    {
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $num = $prop->getDecimal();
        //we do not have an equivalent to java.math.BigDecimal. PHPCR just uses plain float
        $this->assertType('float', $num);
        $this->assertEquals(999, $num);
    }

    /**
     * The PHP Implementation requires that getDouble and getDecimal return the same
     */
    public function testGetDoubleAndDecimalSame()
    {
        $double = $this->valProperty->getDouble();
        $decimal = $this->valProperty->getDecimal();
        $this->assertEquals($double, $decimal);
    }

    public function testGetDate()
    {
        $date = $this->dateProperty->getDate();
        $this->assertType('DateTime', $date);
        $this->assertEquals(1240830067, $date->format('U'));
    }

    public function testGetDateMulti()
    {
        $this->markTestIncomplete('TODO: we need a property definition that can hold multiple dates');

        $arr = $this->multiDateProperty->getDate();
        $this->assertType('array', $arr);
        foreach($arr as $v) {
            $this->assertType('DateTime', $v);
        }
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

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetDateRepositoryException()
    {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
    }

    public function testGetBoolean()
    {
        $this->assertFalse($this->property->getBoolean()); //everything except "true" is false
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('yesOrNo');
        $this->assertSame($prop->getValue(), 'true');
        $this->assertTrue($prop->getBoolean());
    }

    public function testGetBooleanMulti()
    {
        $arr = $this->multiProperty->getBoolean();
        $this->assertType('array', $arr);
        foreach($arr as $v) {
            $this->assertType('boolean', $v);
        }
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetBooleanValueFormatException()
    {
        $this->markTestSkipped('TODO: What would be an invalid value conversion?');
        $this->property->getBoolean();
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetBooleanRepositoryException()
    {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
    }

    public function testGetNode()
    {
        $property = $this->node->getProperty('numberPropertyNode/jcr:content/ref');
        $idnode = $this->node->getNode('idExample');

        //TODO: is the type wrong because we import a document view? would it work with system view?

        $this->assertEquals(\PHPCR\PropertyType::REFERENCE, $property->getType(), "Property has wrong type");
        $target = $property->getNode();
        $this->assertType('PHPCR\NodeInterface', $target);
        $this->assertEquals($target, $idnode);
    }

    //TODO: testGetNodeWeak, testGetNodePath

    public function testGetNodeMulti()
    {
        $this->markTestIncomplete('TODO: Have a property referencing another node (weak, strong + path).');
        /*
        $arr = $this->multiProperty->getNode();
        $this->assertType('array', $arr);
        foreach($arr as $v) {
            $this->assertType('PHPCR\NodeInterface', $v);
        }
        */
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetNodeValueFormatException()
    {
        $node = $this->property->getNode();
    }
    /**
     * only nodes but not properties can be found with getNode
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetNodePropertyItemNotFound()
    {
        $this->markTestIncomplete('TODO: Have a path reference to an existing property.');
    }
    /**
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetNodePathItemNotFound()
    {
        $this->markTestIncomplete('TODO: Have an invalid path reference.');
    }
    /**
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetNodeWeakItemNotFound()
    {
        $this->markTestIncomplete('TODO: Have an invalid weak reference.');
    }

    /** PATH property, the path references another property */
    public function testGetProperty()
    {
        $this->markTestIncomplete('TODO: Have a property referencing another property (path).');
    }

    public function testGetPropertyMulti()
    {
        $this->markTestIncomplete('TODO: Have a property referencing another property (path).');
        /*
        $arr = $this->multiProperty->getProperty();
        $this->assertType('array', $arr);
        foreach($arr as $v) {
            $this->assertType('PHPCR\PropertyInterface', $v);
        }
        */
    }

    public function testGetLength()
    {
        $this->assertEquals(29, $this->property->getLength());
    }

    public function testGetLengthBinary()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBinary', 'foobar', \PHPCR\PropertyType::BINARY);
        $property = $node->getProperty('newBinary');
        $this->assertSame(\PHPCR\PropertyType::BINARY, $property->getType(), 'wrong property type');
        $this->assertEquals(6, $node->getProperty('newBinary')->getLength());
    }

    public function testGetLengthUnsuccessfull()
    {
        $this->markTestIncomplete('TODO: This should return -1 but how can I reproduce?');
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetLengthValueFormatExceptionMulti()
    {
        $this->multiProperty->getLength();
    }

    public function testGetLengths()
    {
        $this->assertEquals(array(17, 15), $this->multiProperty->getLengths());
    }

    public function testGetLengthsBinary()
    {
        $this->markTestIncomplete('TODO: Figure how multivalue binary properties can be set');
    }

    public function testGetLengthsUnsuccessfull()
    {
        $this->markTestIncomplete('TODO: This should return -1 but how can I reproduce?');
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetLengthsValueFormatExceptionMulti()
    {
        $this->property->getLengths();
    }

    public function testGetTypeString()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newString', 'foobar', \PHPCR\PropertyType::STRING);
        $this->assertEquals(\PHPCR\PropertyType::STRING, $node->getProperty('newString')->getType(), 'Wrong type');
    }

    public function testGetTypeBinary()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBin', 'foobar', \PHPCR\PropertyType::BINARY);
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $node->getProperty('newBin')->getType(), 'Wrong type');
    }

    public function testGetTypeLong()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newLong', 3, \PHPCR\PropertyType::LONG);
        $this->assertEquals(\PHPCR\PropertyType::LONG, $node->getProperty('newLong')->getType(), 'Wrong type');
    }

    public function testGetTypeDouble()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newDouble', 3.5, \PHPCR\PropertyType::DOUBLE);
        $this->assertEquals(\PHPCR\PropertyType::DOUBLE, $node->getProperty('newDouble')->getType(), 'Wrong type');
    }

    public function testGetTypeDate()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newDate', '2009-04-27T13:01:04.758+02:00', \PHPCR\PropertyType::DATE);
        $this->assertEquals(\PHPCR\PropertyType::DATE, $node->getProperty('newDate')->getType(), 'Wrong type');
    }

    public function testGetTypeBoolean()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBool', true, \PHPCR\PropertyType::BOOLEAN);
        $this->assertEquals(\PHPCR\PropertyType::BOOLEAN, $node->getProperty('newBool')->getType(), 'Wrong type');
    }

    public function testGetTypeName()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newName', 'foobar', \PHPCR\PropertyType::NAME);
        $this->assertEquals(\PHPCR\PropertyType::NAME, $node->getProperty('newName')->getType(), 'Wrong type');
    }

    public function testGetTypePath()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newPath', 'foobar', \PHPCR\PropertyType::PATH);
        $this->assertEquals(\PHPCR\PropertyType::PATH, $node->getProperty('newPath')->getType(), 'Wrong type');
    }

    public function testGetTypeReference()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newRef', '842e61c0-09ab-42a9-87c0-308ccc90e6f4', \PHPCR\PropertyType::REFERENCE);
        $this->assertEquals(\PHPCR\PropertyType::REFERENCE, $node->getProperty('newRef')->getType(), 'Wrong type');
    }

    public function testGetTypeWeakReference()
    {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newWRef', '842e61c0-09ab-42a9-87c0-308ccc90e6f4', \PHPCR\PropertyType::WEAKREFERENCE);
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $node->getProperty('newRef')->getType(), 'Wrong type');
    }

    public function testIterator() {
        $this->assertTraversableImplemented($this->valProperty);

        $results = 0;
        foreach($this->valProperty as $value) {
            $results++;
            $this->assertType('string', $value);
            $this->assertEquals('bar', $value);
        }

        $this->assertTrue($results==1, 'Single value iterator must have exactly one entry');
    }

    public function testIteratorMulti() {
        $this->assertTraversableImplemented($this->multiProperty);
        $expected = array('mix:referenceable', 'mix:versionable');
        $returned = array();
        foreach($this->multiProperty as $value) {
            $returned[] = $value;
        }
        $this->assertEquals($expected, $returned);
    }

}
