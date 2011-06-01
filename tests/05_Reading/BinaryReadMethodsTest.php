<?php
require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

// According to PHPCR\BinaryInterface

/**
 * §5.10.5
 */
class Reading_5_BinaryReadMethodsTest extends phpcr_suite_baseCase
{
    protected $node;
    private $decodedstring = 'h1. Chapter 1 Title

* foo
* bar
** foo2
** foo3
* foo0

|| header || bar ||
| h | j |

{code}
hello world
{code}

# foo
';

    static public function  setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('general/base');
    }

    public function setUp()
    {
        // All those tests are disabled because at this point, we dont implement PHPCR\BinaryInterface, maybee later for performance improvements.
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getRootNode()->getNode('tests_general_base/numberPropertyNode/jcr:content');
        $this->binaryProperty = $this->node->getProperty('jcr:data');
        $this->assertEquals(PHPCR\PropertyType::BINARY, $this->binaryProperty->getType());
    }

    public function testReadBinaryValue()
    {
        $binary = $this->binaryProperty->getBinary();
        $this->assertTrue(is_resource($binary));
        $this->assertEquals($this->decodedstring, stream_get_contents($binary));
    }

    public function testGetLength()
    {
        $size = $this->binaryProperty->getLength();
        $this->assertEquals(strlen($this->decodedstring), $size);
    }

    public function testReadBinaryValues()
    {
        $node = $this->sharedFixture['session']->getRootNode()->getNode('tests_general_base/index.txt/jcr:content');
        $binaryMulti = $node->getProperty('multidata');
        $this->assertTrue($binaryMulti->isMultiple());
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $binaryMulti->getType());
        $vals = $binaryMulti->getValue();
        $this->assertInternalType('array', $vals);
        foreach($vals as $value) {
            $this->assertEquals($this->decodedstring, stream_get_contents($value));
        }
    }

    public function testIterateBinaryValues()
    {
        foreach($this->binaryProperty as $value) {
            $this->assertEquals($this->decodedstring, stream_get_contents($value));
        }
    }

    public function testGetLengthMultivalue()
    {
        $node = $this->sharedFixture['session']->getRootNode()->getNode('tests_general_base/index.txt/jcr:content');
        $binaryMulti = $node->getProperty('multidata');
        $sizes = $binaryMulti->getLength();
        $this->assertInternalType('array', $sizes);
        foreach($sizes as $size) {
            $this->assertEquals(strlen($this->decodedstring), $size);
        }
    }
}
