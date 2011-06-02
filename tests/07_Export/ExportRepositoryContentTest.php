<?php
require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

//7 Export Repository Content
class Export_7_ExportRepositoryContentTest extends phpcr_suite_baseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('07_Export/systemview');
    }

    public function testExportSystemView()
    {
        $stream = fopen('php://memory', 'rwb+');
        $this->sharedFixture['session']->exportSystemView('/tests_general_base', $stream, false, false);
        rewind($stream);
        $xml = stream_get_contents($stream);
        $this->assertXmlStringEqualsXmlString(file_get_contents(__DIR__.'/../../fixtures/07_Export/systemview.xml'), $xml, true);
    }

    public function testExportDocumentView()
    {
        $stream = fopen('php://memory', 'rwb+');
        $this->sharedFixture['session']->exportDocumentView('/tests_general_base', $stream, false, false);
        rewind($stream);
        $xml = stream_get_contents($stream);
//var_dump($xml);
        $this->assertXmlStringEqualsXmlString(file_get_contents(__DIR__.'/../../fixtures/07_Export/documentview.xml'), $xml, true);
    }

    public function testExportSystemViewSax()
    {
        $this->markTestSkipped('TODO: SAX ContentHandler');
        $xmlwriter = new XMLWriter();
        $this->assertTrue($xmlwriter->openMemory());
        $this->sharedFixture['session']->exportSystemView('/', $xmlwriter, false, false);
        echo $xmlwriter->outputMemory(true);
    }
    public function testExportDocumentViewSax()
    {
        $this->markTestSkipped('TODO: SAX ContentHandler');
        $xmlwriter = new XMLWriter();
        $this->assertTrue($xmlwriter->openMemory());
        $this->sharedFixture['session']->exportDocumentView('/', $xmlwriter, false, false);
        echo $xmlwriter->outputMemory(true);

    }
}
