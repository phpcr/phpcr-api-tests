<?php
require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

//7 Export Repository Content
class Export_7_ExportRepositoryContentTest extends jackalope_baseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('read/export/base');
    }

    public function testExportSystemView()
    {
        $xmlwriter = new XMLWriter();
        $this->assertTrue($xmlwriter->openMemory());
        $this->sharedFixture['session']->exportSystemView('/', $xmlwriter, false, false);
        echo $xmlwriter->outputMemory(true);
    }
    public function testExportDocumentView()
    {
        $xmlwriter = new XMLWriter();
        $this->assertTrue($xmlwriter->openMemory());
        $this->sharedFixture['session']->exportDocumentView('/', $xmlwriter, false, false);
        echo $xmlwriter->outputMemory(true);

    }
}
