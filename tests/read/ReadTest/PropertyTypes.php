<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

// 6.2.4 Property Read Methods

class jackalope_tests_read_ReadTest_PropertyTypes extends jackalope_baseCase {

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

    static public function dataValueFromName() {
        return self::$names;
    }

    static public function dataValueFromType() {
        return self::$types;
    }

    /**
     * @dataProvider dataValueFromName
     */
    public function testNameFromValue($field, $name) {
        $this->assertEquals($name, PHPCR_PropertyType::nameFromValue(PHPCR_PropertyType::$field));
    }

    /**
     * @dataProvider dataValueFromName
     */
    public function testValueFromName($field, $name) {
        $this->assertEquals(PHPCR_PropertyType::$field, PHPCR_PropertyType::valueFromName($name));
    }

    /**
     * @dataProvider dataValueFromType
     */
    public function testValueFromType($field, $type) {
        $this->assertEquals(PHPCR_PropertyType::$field, PHPCR_PropertyType::valueFromType($type));
    }
}
