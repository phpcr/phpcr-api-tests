<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

// 6.2.4 Property Read Methods

class jackalope_tests_read_ReadTest_PropertyTypes extends jackalope_baseCase
{

    protected static $types = array(
        'UNDEFINED',
        'STRING',
        'BINARY',
        'LONG',
        'DOUBLE',
        'DATE',
        'BOOLEAN',
        'NAME',
        'PATH',
        'REFERENCE',
        'WEAKREFERENCE',
        'URI',
        'DECIMAL',
    );
    protected static $typeNames = array(
        'undefined',
        'String',
        'Binary',
        'Long',
        'Double',
        'Date',
        'Boolean',
        'Name',
        'Path',
        'Reference',
        'WeakReference',
        'URI',
        'Decimal',
    );

    static public function dataNameFromValue()
    {
        $data = array();
        for ($x = 0; $x < count(self::$types); $x++) {
            $data[] = array($x, self::$typeNames[$x]);
        }
        return $data;
    }

    /**
     * @dataProvider dataNameFromValue
     */
    public function testNameFromValue($type, $name)
    {
        $this->assertEquals($name, PHPCR_PropertyType::nameFromValue($type));
    }

    public function testValueFromName($x)
    {
        $this->markTestIncomplete("What is this supposed to do?");

        for ($x = 0; $x < count($this->types); $x++) {
            $this->assertEquals($x, PHPCR_PropertyType::valueFromName($this->types[$x]));
        }
    }
}
