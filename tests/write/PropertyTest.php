<?php
require_once(dirname(__FILE__) . '/../../inc/baseSuite.php');
require_once(dirname(__FILE__) . '/PropertyTest/SetPropertyMethods.php');
require_once(dirname(__FILE__) . '/PropertyTest/SetPropertyTypes.php');

class jackalope_tests_write_ValueTest extends jackalope_baseSuite {
    public static function suite() {
        $suite = new jackalope_tests_write_ValueTest('Write: Property');
        $suite->addTestSuite('jackalope_tests_write_PropertyTest_SetPropertyMethods');
        $suite->addTestSuite('jackalope_tests_write_PropertyTest_SetPropertyTypes');
        return $suite;
    }

}



