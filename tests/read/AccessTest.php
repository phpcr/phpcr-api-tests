<?php
require_once(dirname(__FILE__) . '/../../inc/baseSuite.php');
require_once(dirname(__FILE__) . '/AccessTest/Credentials.php');
require_once(dirname(__FILE__) . '/AccessTest/Repository.php');
require_once(dirname(__FILE__) . '/AccessTest/RepositoryDescriptors.php');

class jackalope_tests_read_AccessTest extends jackalope_baseSuite {
    
    public static function suite() {
        $suite = new jackalope_tests_read_AccessTest('Read: Accessing the Repository');
        $suite->addTestSuite('jackalope_tests_read_AccessTest_Credentials');
        $suite->addTestSuite('jackalope_tests_read_AccessTest_Repository');
        $suite->addTestSuite('jackalope_tests_read_AccessTest_RepositoryDescriptors');
        return $suite;
    }
}
