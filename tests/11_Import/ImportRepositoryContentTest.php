<?php
require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

//6.5 Export Repository Content
class Export_11_ImportRepositoryContentTest extends phpcr_suite_baseCase
{
    public function testImportXML()
    {
        $this->markTestSkipped('TODO: what kind of stream is ok for input?');
        //$this->sharedFixture['session']->importXML('/', input stream, behaviour flags);
    }

    public function testGetImportContentHandler()
    {
        $this->markTestSkipped('TODO: implement');
    }
}
