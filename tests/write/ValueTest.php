<?php
require_once(dirname(__FILE__) . '/../../inc/baseSuite.php');
require_once(dirname(__FILE__) . '/ValueTest/SetValueMethods.php');
require_once(dirname(__FILE__) . '/ValueTest/ValueFactory.php');

class jackalope_tests_write_ValueTest extends jackalope_baseSuite {

    protected $path = 'write/value';

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
        $suite = new jackalope_tests_write_ValueTest('Write: Value');
        $suite->addTestSuite('jackalope_tests_write_SetTest_SetValueMethods');
        $suite->addTestSuite('jackalope_tests_write_ValueTest_ValueFactory');
        return $suite;
    }

}



