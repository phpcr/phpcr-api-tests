<?php
require_once(dirname(__FILE__) . '/../../inc/baseSuite.php');
require_once(dirname(__FILE__) . '/ManipulationTest/AddMethods.php');
require_once(dirname(__FILE__) . '/ManipulationTest/MoveMethods.php');
require_once(dirname(__FILE__) . '/ManipulationTest/CopyMethods.php');

class jackalope_tests_write_ManipulationTest extends jackalope_baseSuite {

    protected $path = 'write/manipulation';

    public function setUp() {
        parent::setUp();
        $this->sharedFixture['ie']->import('base.xml');
        $this->sharedFixture['session'] = getJCRSession($this->sharedFixture['config']);
    }

    public function tearDown() {
        parent::tearDown();
        $this->sharedFixture['session']->logout();
        $this->sharedFixture = null;
    }

    public static function suite() {
        $suite = new jackalope_tests_write_ManipulationTest('Write: Manipulation');
        $suite->addTestSuite('jackalope_tests_write_ManipulationTest_AddMethods');
        $suite->addTestSuite('jackalope_tests_write_ManipulationTest_MoveMethods');
        $suite->addTestSuite('jackalope_tests_write_ManipulationTest_CopyMethods');
        return $suite;
    }

}


