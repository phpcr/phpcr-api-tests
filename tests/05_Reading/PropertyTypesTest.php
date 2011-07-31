<?php
namespace PHPCR\Tests\Reading;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

/**
 * a test for the PHPCR\PropertyType class
 */
class PropertyTypesTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass()
    {
        // do not care about the fixtures
        parent::setupBeforeClass(false);
    }

    /** key = numeric type constant names as defined by api
     *  value = expected value of the TYPENAME_<TYPE> constants
     */
    protected static $names = array(
        'UNDEFINED'      => 'undefined',
        'STRING'         => 'String',
        'BINARY'         => 'Binary',
        'LONG'           => 'Long',
        'DOUBLE'         => 'Double',
        'DATE'           => 'Date',
        'BOOLEAN'        => 'Boolean',
        'NAME'           => 'Name',
        'PATH'           => 'Path',
        'REFERENCE'      => 'Reference',
        'WEAKREFERENCE'  => 'WeakReference',
        'URI'            => 'URI',
        'DECIMAL'        => 'Decimal'
    );
    /** key = numeric type constant names as defined by api
     *  value = string for valueFromType
     */
    protected static $types = array(
        'STRING'         => 'String',
        'LONG'           => 'int',
        'LONG'           => 'Integer',
        'DOUBLE'         => 'Double',
        'DOUBLE'         => 'Float',
        'DATE'           => 'Datetime',
        'BOOLEAN'        => 'Boolean',
        'BOOLEAN'        => 'Bool',
        'UNDEFINED'      => 'something',
        'UNDEFINED'      => 'undefined',
    );

    static public function dataValueFromName()
    {
        $data = array();
        foreach (self::$names as $key => $value) {
            $data[] = array($key,$value);
        }
        return $data;
    }

    /**
     * @dataProvider dataValueFromName
     */
    public function testNameFromValue($field, $name)
    {
        $this->assertEquals($name, \PHPCR\PropertyType::nameFromValue(constant("PHPCR\PropertyType::$field")));
    }

    /**
     * @dataProvider dataValueFromName
     */
    public function testValueFromName($field, $name)
    {
        $this->assertEquals(constant("PHPCR\PropertyType::$field"), \PHPCR\PropertyType::valueFromName($name));
    }
}
