<?php
require_once(dirname(__FILE__) . '/../../inc/baseSuite.php');
require_once(dirname(__FILE__) . '/ExportTest/ExportRepositoryContent.php'); //6.5
require_once(dirname(__FILE__) . '/ExportTest/ImportRepositoryContent.php'); //6.5

class jackalope_tests_read_ExportTest extends jackalope_baseSuite {
    protected $path = 'read/export';

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
        $suite = new jackalope_tests_read_ExportTest('Read: Export');
        $suite->addTestSuite('jackalope_tests_read_ExportTest_ExportRepositoryContent');
        $suite->addTestSuite('jackalope_tests_read_ExportTest_ImportRepositoryContent');
        return $suite;
    }

}
