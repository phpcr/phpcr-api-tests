<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.5 Export Repository Content
class jackalope_tests_read_ExportTest_ImportRepositoryContent extends jackalope_baseCase {

    static public function setupBeforeClass() {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('read/export/base.xml');
    }

    public function testImportXML() {
        $this->markTestSkipped('TODO: what kind of stream is ok for input?');
        //$this->sharedFixture['session']->importXML('/', input stream, behaviour flags);
    }

    public function testGetImportContentHandler() {
        $this->markTestSkipped('TODO: implement');
    }
}
