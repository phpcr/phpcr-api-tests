<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * javax.jcr.Property read methods
 * TODO: CONSTANTS
 *
 * PropertyWriteMethods: isModified, refresh, save, remove, setValue (in many variants)
 */
class jackalope_tests_read_ReadTest_PropertyReadMethods extends jackalope_baseCase {
    protected $rootNode;
    protected $node;
    protected $property;
    protected $multiProperty;

    static public function  setupBeforeClass() {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('read/read/base.xml');
    }

    public function setUp() {
        parent::setUp();
        $this->rootNode = $this->sharedFixture['session']->getRootNode();
        $this->node = $this->rootNode->getNode('tests_read_access_base');
        $this->property = $this->node->getProperty('jcr:created');
        $this->valProperty = $this->sharedFixture['session']->getRootNode()->getNode('tests_read_access_base/numberPropertyNode/jcr:content')->getProperty('foo');
        $this->multiProperty = $this->node->getNode('multiValueProperty')->getProperty('jcr:mixinTypes');
    }

    /*** item base methods for property ***/
    function testGetAncestor() {
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
    function testGetDepthProperty() {
        $this->assertEquals(2, $this->property->getDepth());
        $deepnode = $this->node->getNode('multiValueProperty');
        $this->assertEquals(3, $this->multiProperty->getDepth());
    }
     /* todo:  getParent, getPath, getSession, isNew, isNode, isSame */
    function testGetName() {
        $name = $this->property->getName();
        $this->assertNotNull($name);
        $this->assertEquals('jcr:created', $name);
    }

    /*** property specific methods ***/

    public function testGetNativeValue() {
        $val = $this->property->getNativeValue();
        $this->assertType('string', $val);
    }
    public function testGetNativeValueMulti() {
        $vals = $this->multiProperty->getNativeValue();
        $this->assertType('array', $vals);
        foreach ($vals as $val) {
            $this->assertNotNull($val);
        }
    }

    public function testGetString() {
        $expectedStr = date('o-m-d\T');
        $str = $this->property->getString();
        $this->assertType('string', $str);
        $this->assertEquals(0, strpos($str, $expectedStr));

        $str = $this->valProperty->getString();
        $this->assertType('string', $str);
        $this->assertEquals('bar', $str);
    }

    public function testGetStringMulti() {
        $arr = $this->multiProperty->getString();
        $this->assertType('array', $arr);
        foreach($arr as $v) {
            $this->assertType('string', $v);
        }
    }

    public function testGetBinary() {
        $bin = $this->property->getBinary();
        $str = $this->property->getString();
        $this->assertEquals($bin->getSize(), strlen($str));
    }

    public function testGetBinaryMulti() {
        $this->markTestIncomplete('TODO: Figure how multivalue binary properties can be set');
    }

    public function testGetLong() {
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $num = $prop->getLong();
        $this->assertType('int', $num);
        $this->assertEquals(999, $num);
    }

    public function testGetLongMulti() {
        $arr = $this->multiProperty->getLong();
        $this->assertType('array', $arr);
        foreach($arr as $v) {
            $this->assertType('integer', $v);
        }
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetLongValueFormatException() {
        $this->markTestIncomplete('Have a property that can not be converted to this type');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetLongRepositoryException() {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
    }

    public function testGetDouble() {
        $nv = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $number = $nv->getDouble();
        $this->assertType('float', $number);
        $this->assertEquals(999, $number);
    }

    public function testGetDoubleMulti() {
        $arr = $this->multiProperty->getDouble();
        $this->assertType('array', $arr);
        foreach($arr as $v) {
            $this->assertType('float', $v);
        }
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetDoubleValueFormatException() {
        $this->markTestIncomplete('Have a property that can not be converted to this type');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetDoubleRepositoryException() {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
    }

    public function testGetDecimal() {
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $num = $prop->getDecimal();
        //we do not have an equivalent to java.math.BigDecimal. PHPCR just uses plain float
        $this->assertType('float', $num);
        $this->assertEquals(999, $num);
    }

    /**
     * The PHP Implementation requires that getDouble and getDecimal return the same
     */
    public function testGetDoubleAndDecimalSame() {
        $double = $this->property->getDouble();
        $decimal = $this->property->getDecimal();
        $this->assertEquals($double, $decimal);
    }

    public function testGetDate() {
        $date = $this->property->getDate();
        $this->assertType('DateTime', $date);
        $this->assertEquals(floor($date->format('U') / 1000), floor(time() / 1000));
        $this->assertEquals(1240830067, $date->format('U'));
    }

    public function testGetDateMulti() {
        $arr = $this->multiProperty->getDouble();
        $this->assertType('array', $arr);
        foreach($arr as $v) {
            $this->assertType('DateTime', $v);
        }
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetDateValueFormatException() {
        $this->property->getDate();
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetDateRepositoryException() {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
    }

    public function testGetBoolean() {
        $this->assertFalse($this->property->getBoolean()); //everything except "true" is false
        $bv = $this->node->getProperty('yesOrNo')->getNativeValue();
        $this->assertTrue($bv);
    }

    public function testGetBooleanMulti() {
        $arr = $this->multiProperty->getBoolean();
        $this->assertType('array', $arr);
        foreach($arr as $v) {
            $this->assertType('boolean', $v);
        }
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetBooleanValueFormatException() {
        $this->markTestSkipped('TODO: What would be an invalid value conversion?');
        $this->property->getBoolean();
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetBooleanRepositoryException() {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
    }

    public function testGetNode() {
        $this->markTestIncomplete('TODO: Have a property referencing another node (weak, strong + path).');
/*
        $property->getNode();
        $this->assertType('PHPCR\NodeInterface', $node);
        $this->assertEquals($node, $this->node);
*/
    }

    public function testGetNodeMulti() {
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
    public function testGetNodeValueFormatException() {
        $node = $this->property->getNode();
    }
    /**
     * only nodes but not properties can be found with getNode
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetNodePropertyItemNotFound() {
        $this->markTestIncomplete('TODO: Have a path reference to an existing property.');
    }
    /**
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetNodePathItemNotFound() {
        $this->markTestIncomplete('TODO: Have an invalid path reference.');
    }
    /**
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetNodeWeakItemNotFound() {
        $this->markTestIncomplete('TODO: Have an invalid weak reference.');
    }

    /** PATH property, the path references another property */
    public function testGetProperty() {
        $this->markTestIncomplete('TODO: Have a property referencing another property (weak, strong + path).');
    }

    public function testGetPropertyMulti() {
        $this->markTestIncomplete('TODO: Have a property referencing another property (weak, strong + path).');
        /*
        $arr = $this->multiProperty->getProperty();
        $this->assertType('array', $arr);
        foreach($arr as $v) {
            $this->assertType('PHPCR\PropertyInterface', $v);
        }
        */
    }

    public function testGetLength() {
        $this->assertEquals(29, $this->property->getLength());
    }

    public function testGetLengthBinary() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBinary', 'foobar', \PHPCR\PropertyType::BINARY);
        $this->assertEquals(6, $node->getProperty('newBinary')->getLength());
    }

    public function testGetLengthUnsuccessfull() {
        $this->markTestIncomplete('TODO: This should return -1 but how can I reproduce?');
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetLengthValueFormatExceptionMulti() {
        $this->multiProperty->getLength();
    }

    public function testGetLengths() {
        $this->assertEquals(array(17, 15), $this->multiProperty->getLengths());
    }

    public function testGetLengthsBinary() {
        $this->markTestIncomplete('TODO: Figure how multivalue binary properties can be set');
    }

    public function testGetLengthsUnsuccessfull() {
        $this->markTestIncomplete('TODO: This should return -1 but how can I reproduce?');
    }

    /**
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testGetLengthsValueFormatExceptionMulti() {
        $this->property->getLengths();
    }

    public function testGetTypeString() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newString', 'foobar', \PHPCR\PropertyType::STRING);
        $this->assertEquals(\PHPCR\PropertyType::STRING, $node->getProperty('newString')->getType());
    }

    public function testGetTypeBinary() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBin', 'foobar', \PHPCR\PropertyType::BINARY);
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $node->getProperty('newBin')->getType());
    }

    public function testGetTypeLong() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newLong', 3, \PHPCR\PropertyType::LONG);
        $this->assertEquals(\PHPCR\PropertyType::LONG, $node->getProperty('newLong')->getType());
    }

    public function testGetTypeDouble() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newDouble', 3.5, \PHPCR\PropertyType::DOUBLE);
        $this->assertEquals(\PHPCR\PropertyType::DOUBLE, $node->getProperty('newDouble')->getType());
    }

    public function testGetTypeDate() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newDate', 'foobar', \PHPCR\PropertyType::DATE);
        $this->assertEquals(\PHPCR\PropertyType::DATE, $node->getProperty('newDate')->getType());
    }

    public function testGetTypeBoolean() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBool', true, \PHPCR\PropertyType::BOOLEAN);
        $this->assertEquals(\PHPCR\PropertyType::BOOLEAN, $node->getProperty('newBool')->getType());
    }

    public function testGetTypeName() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newName', 'foobar', \PHPCR\PropertyType::NAME);
        $this->assertEquals(\PHPCR\PropertyType::NAME, $node->getProperty('newName')->getType());
    }

    public function testGetTypePath() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newPath', 'foobar', \PHPCR\PropertyType::PATH);
        $this->assertEquals(\PHPCR\PropertyType::PATH, $node->getProperty('newPath')->getType());
    }

    public function testGetTypeReference() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newRef', 'foobar', \PHPCR\PropertyType::REFERENCE);
        $this->assertEquals(\PHPCR\PropertyType::REFERENCE, $node->getProperty('newRef')->getType());
    }
}
