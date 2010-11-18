<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.5 Export Repository Content
class Read_Export_ExportRepositoryContentTest extends jackalope_baseCase {

    static public function setupBeforeClass() {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('read/export/base.xml');
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
