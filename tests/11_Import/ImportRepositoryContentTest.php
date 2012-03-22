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
        $session = $this->sharedFixture['session'];
        $session->importXML('/', __DIR__.'/../../fixtures/general/base.xml', 0);

        $this->assertTrue($session->nodeExists('/tests_general_base'));
        $this->assertTrue($session->propertyExists('/tests_general_base/idExample/jcr:content/weakreference_target/jcr:uuid'));
        $uuid = $session->getProperty('/tests_general_base/idExample/jcr:content/weakreference_target/jcr:uuid');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $uuid);
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $uuid->getString());

        $ref = $session->getProperty('/tests_general_base/idExample/jcr:content/weakreference_source1/ref1');
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $ref->getType());
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $ref->getString());
    }

    public function testImportXMLDocument()
    {
        $this->sharedFixture['session']->importXML('/', __DIR__.'/../../fixtures/07_Export/documentview.xml', 0);
    }
}
