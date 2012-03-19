<?php
namespace PHPCR\Tests\Import;

require_once(__DIR__ . '/../../inc/BaseCase.php');

//6.5 Import Repository Content
class ImportRepositoryContentTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = '11_Import/empty')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function testImportXMLSystem()
    {
        $this->sharedFixture['session']->importXML('/', __DIR__.'/../../fixtures/general/base.xml', 0);

    }

    public function testImportXMLDocument()
    {
        $this->sharedFixture['session']->importXML('/', __DIR__.'/../../fixtures/07_Export/documentview.xml', 0);
    }
}
