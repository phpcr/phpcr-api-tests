<?php
require_once(dirname(__FILE__) . '/../../inc/baseSuite.php');
require_once(dirname(__FILE__) . '/ReadTest/SessionReadMethods.php');
require_once(dirname(__FILE__) . '/ReadTest/WorkspaceReadMethods.php');
require_once(dirname(__FILE__) . '/ReadTest/NodeReadMethods.php');
require_once(dirname(__FILE__) . '/ReadTest/PropertyReadMethods.php');
require_once(dirname(__FILE__) . '/ReadTest/PropertyTypes.php');
require_once(dirname(__FILE__) . '/ReadTest/Value.php');
require_once(dirname(__FILE__) . '/ReadTest/BinaryReadMethods.php');

class jackalope_tests_read_ReadTest extends jackalope_baseSuite {
    public static function suite() {
        $suite = new jackalope_tests_read_ReadTest('Read: Read');
        $suite->addTestSuite('jackalope_tests_read_ReadTest_SessionReadMethods');
        $suite->addTestSuite('jackalope_tests_read_ReadTest_WorkspaceReadMethods');
        $suite->addTestSuite('jackalope_tests_read_ReadTest_NodeReadMethods');
        $suite->addTestSuite('jackalope_tests_read_ReadTest_PropertyReadMethods');
        $suite->addTestSuite('jackalope_tests_read_ReadTest_PropertyTypes');
        $suite->addTestSuite('jackalope_tests_read_ReadTest_Value');
        $suite->addTestSuite('jackalope_tests_read_ReadTest_BinaryReadMethods');
        return $suite;
    }
}
