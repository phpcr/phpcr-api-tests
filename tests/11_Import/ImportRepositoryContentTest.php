<?php
namespace PHPCR\Tests\Import;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

//6.5 Import Repository Content
class ImportRepositoryContentTest extends \PHPCR\Test\BaseCase
{
    public function testImportXML()
    {
        $this->markTestSkipped('TODO: have document and system view to import and validate success. share with export test');
        //$this->sharedFixture['session']->importXML('/', input stream, behaviour flags);
    }
}
