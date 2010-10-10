<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.5 Export Repository Content
class jackalope_tests_read_ExportTest_ExportRepositoryContent extends jackalope_baseCase {

    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('read/export/base.xml');
        self::$staticSharedFixture['session'] = getJCRSession(self::$staticSharedFixture['config']);
    }

    public function testExportSystemView() {
        $xmlwriter = new XMLWriter();
        $this->assertTrue($xmlwriter->openMemory());
        $this->sharedFixture['session']->exportSystemView('/', $xmlwriter, false, false);
        echo $xmlwriter->outputMemory(true);
    }
    public function testExportDocumentView() {
        $xmlwriter = new XMLWriter();
        $this->assertTrue($xmlwriter->openMemory());
        $this->sharedFixture['session']->exportDocumentView('/', $xmlwriter, false, false);
        echo $xmlwriter->outputMemory(true);

    }
}
