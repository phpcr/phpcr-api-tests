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

    static public function  setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('read/read/base.xml');
        self::$staticSharedFixture['session'] = getJCRSession(self::$staticSharedFixture['config']);
    }

    public function setUp() {
        parent::setUp();
        $this->rootNode = $this->sharedFixture['session']->getRootNode();
        $this->node = $this->rootNode->getNode('tests_read_access_base');
        $this->property = $this->node->getProperty('jcr:created');
        $this->multiProperty = $this->node->getNode('multiValueProperty')->getProperty('jcr:mixinTypes');
    }

    /*** item base methods for property ***/
    function testGetAncestor() {
        $ancestor = $this->multiProperty->getAncestor(0);
        $this->assertNotNull($ancestor);
        $this->assertTrue($ancestor instanceOf PHPCR_ItemInterface);
        $this->assertTrue($this->rootNode->isSame($ancestor));

        $ancestor = $this->multiProperty->getAncestor(1);
        $this->assertNotNull($ancestor);
        $this->assertTrue($ancestor instanceOf PHPCR_ItemInterface);
        $this->assertTrue($this->node->isSame($ancestor));

        //self
        $ancestor = $this->multiProperty->getAncestor($this->multiProperty->getDepth());
        $this->assertNotNull($ancestor);
        $this->assertTrue($ancestor instanceOf PHPCR_ItemInterface);
        $this->assertTrue($this->multiProperty->isSame($ancestor));
    }
    function testGetDepthProperty() {
        $this->assertEquals(2, $this->property->getDepth());
        $deepnode = $this->node->getNode('multiValueProperty');
        $this->assertEquals(3, $this->multiProperty->getDepth());
    }
     /* todo:  getName, getParent, getPath, getSession, isNew, isNode, isSame */
    function testGetName() {
        $name = $this->property->getName();
        $this->assertNotNull($name);
        $this->assertEquals('jcr:created', $name);
    }

    /*** property specific methods ***/
    public function testGetValue() {
        $val = $this->property->getValue();
        $this->assertType('object', $val);
        $this->assertTrue($val instanceOf PHPCR_ValueInterface);
    }

    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetValueValueFormatException() {
        $this->multiProperty->getValue();
    }

    /**
     * @expectedException PHPCR_RepostioryException
     */
    public function testGetValueRepositoryException() {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
    }

    public function testGetValues() {
        $vals = $this->multiProperty->getValues();
        $this->assertType('array', $vals);
        foreach ($vals as $val) {
            $this->assertTrue($val instanceOf PHPCR_ValueInterface);
        }
    }

    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetValuesValueFormatException() {
        $this->property->getValues();
    }

    /**
     * @expectedException PHPCR_RepostioryException
     */
    public function testGetValuesRepositoryException() {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
    }

    public function testGetString() {
        $expectedStr = date('o-m-d\T');
        $str = $this->property->getString();
        $this->assertType('string', $str);
        $this->assertEquals(0, strpos($str, $expectedStr));
    }

    public function testGetLong() {
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $num = $prop->getLong();
        $this->assertType('int', $num);
        $this->assertEquals(999, $num);
    }

    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetLongValueFormatException() {
        $this->multiProperty->getLong();
    }

    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetLongRepositoryException() {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
    }

    public function testGetDouble() {
        $node = $this->node->getNode('numberPropertyNode/jcr:content');
        $node->setProperty('newFloat', 3.9999);
        $prop = $node->getProperty('newFloat');
        $num = $prop->getDouble();
        $this->assertType('float', $num);
        $this->assertEquals(3.9999, $num);
    }

    public function testGetDecimal() {
        $prop = $this->node->getNode('numberPropertyNode/jcr:content')->getProperty('longNumber');
        $num = $prop->getDecimal();
        //we do not have an equivalent to java.math.BigDecimal. PHPCR just uses plain float
        $this->assertType('float', $num);
        $this->assertEquals(999, $num);
    }

    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetDoubleValueFormatException() {
        $this->multiProperty->getDouble();
    }

    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetDoubleRepositoryException() {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
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
        $this->assertType('object', $date);
        $this->assertTrue($date instanceOf DateTime);
        $this->assertEquals(floor($date->format('U') / 1000), floor(time() / 1000));
    }

    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetDateValueFormatExceptionMulti() {
        $vals = $this->multiProperty->getDate();
    }

    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetDateValueFormatException() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('foo', 'bar');
        $node->getProperty('foo')->getDate();
    }

    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetDateRepositoryException() {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
    }

    public function testGetBool() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBool', true);
        $this->assertTrue($node->getProperty('newBool')->getBoolean());;
    }

    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetBoolValueFormatExceptionMulti() {
        $vals = $this->multiProperty->getBoolean();
    }

    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetBoolValueFormatException() {
        $this->property->getBoolean();
    }

    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetBoolRepositoryException() {
        $this->markTestIncomplete('TODO: Figure out how to provoke this error.');
    }

    public function testGetNode() {
        $this->markTestIncomplete('TODO: Have a property referencing another node (weak, strong + path).');
/*
        $property->getNode();
        $this->assertType('object', $node);
        $this->assertTrue($node instanceOf PHPCR_NodeInterface);
        $this->assertEquals($node, $this->node);
*/
    }
    /**
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetNodeValueFormatException() {
        $node = $this->property->getNode();
    }
    /**
     * only nodes but not properties can be found with getNode
     * @expectedException PHPCR_ItemNotFoundException
     */
    public function testGetNodePropertyItemNotFound() {
        $this->markTestIncomplete('TODO: Have a path reference to an existing property.');
    }
    /**
     * @expectedException PHPCR_ItemNotFoundException
     */
    public function testGetNodePathItemNotFound() {
        $this->markTestIncomplete('TODO: Have an invalid path reference.');
    }
    /**
     * @expectedException PHPCR_ItemNotFoundException
     */
    public function testGetNodeWeakItemNotFound() {
        $this->markTestIncomplete('TODO: Have an invalid weak reference.');
    }

    /** PATH property, the path references another property */
    public function testGetProperty() {
        $this->markTestIncomplete('TODO: Implement');
    }

    public function testGetBinary() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBinary', 'foobar', PHPCR_PropertyType::BINARY);
        $bin = $node->getProperty('newBinary')->getBinary();
        $this->assertTrue($bin instanceof PHPCR_BinaryInterface);
        $this->assertEquals(6, $bin->getSize());
    }
    public function testGetBinaryValueFormatException() {
        $this->markTestIncomplete('TODO: Figure how multivalue binary properties can be set');
    }

    public function testGetLength() {
        $this->assertEquals(29, $this->property->getLength());
    }

    public function testGetLengthBinary() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBinary', 'foobar', PHPCR_PropertyType::BINARY);
        $this->assertEquals(6, $node->getProperty('newBinary')->getLength());
    }

    public function testGetLengthUnsuccessfull() {
        $this->markTestIncomplete('TODO: This should return -1 but how can I reproduce?');
    }

    /**
     * @expectedException PHPCR_ValueFormatException
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
     * @expectedException PHPCR_ValueFormatException
     */
    public function testGetLengthsValueFormatExceptionMulti() {
        $this->property->getLengths();
    }

    public function testGetTypeString() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newString', 'foobar', PHPCR_PropertyType::STRING);
        $this->assertEquals(PHPCR_PropertyType::STRING, $node->getProperty('newString')->getType());
    }

    public function testGetTypeBinary() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBin', 'foobar', PHPCR_PropertyType::BINARY);
        $this->assertEquals(PHPCR_PropertyType::BINARY, $node->getProperty('newBin')->getType());
    }

    public function testGetTypeLong() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newLong', 3, PHPCR_PropertyType::LONG);
        $this->assertEquals(PHPCR_PropertyType::LONG, $node->getProperty('newLong')->getType());
    }

    public function testGetTypeDouble() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newDouble', 3.5, PHPCR_PropertyType::DOUBLE);
        $this->assertEquals(PHPCR_PropertyType::DOUBLE, $node->getProperty('newDouble')->getType());
    }

    public function testGetTypeDate() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newDate', 'foobar', PHPCR_PropertyType::DATE);
        $this->assertEquals(PHPCR_PropertyType::DATE, $node->getProperty('newDate')->getType());
    }

    public function testGetTypeBoolean() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newBool', true, PHPCR_PropertyType::BOOLEAN);
        $this->assertEquals(PHPCR_PropertyType::BOOLEAN, $node->getProperty('newBool')->getType());
    }

    public function testGetTypeName() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newName', 'foobar', PHPCR_PropertyType::NAME);
        $this->assertEquals(PHPCR_PropertyType::NAME, $node->getProperty('newName')->getType());
    }

    public function testGetTypePath() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newPath', 'foobar', PHPCR_PropertyType::PATH);
        $this->assertEquals(PHPCR_PropertyType::PATH, $node->getProperty('newPath')->getType());
    }

    public function testGetTypeReference() {
        $node = $this->node->getNode('index.txt/jcr:content');
        $node->setProperty('newRef', 'foobar', PHPCR_PropertyType::REFERENCE);
        $this->assertEquals(PHPCR_PropertyType::REFERENCE, $node->getProperty('newRef')->getType());
    }
}
