<?php
namespace PHPCR\Tests\Import;

use PHPCR\ImportUUIDBehaviorInterface;


//6.5 Import Repository Content
class ImportRepositoryContentTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = null)
    {
        parent::setupBeforeClass($fixtures);
    }

    /**
     * Import to empty repository with various data
     */
    public function testImportXMLSystemSession()
    {
        self::$staticSharedFixture['ie']->import('11_Import/empty');
        $session = $this->renewSession();
        $this->doTestImportXMLSystem($session, $session);
    }
    /**
     * Import to empty repository with various data
     */
    public function testImportXMLSystemWorkspace()
    {
        self::$staticSharedFixture['ie']->import('11_Import/empty');
        $session = $this->renewSession();
        $this->doTestImportXMLSystem($session->getWorkspace(), $session);
    }

    private function doTestImportXMLSystem($connect, $session)
    {
        $connect->importXML('/', __DIR__.'/../../fixtures/general/base.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_THROW);

        $this->assertTrue($session->nodeExists('/tests_general_base'));
        $this->assertTrue($session->propertyExists('/tests_general_base/idExample/jcr:content/weakreference_target/jcr:uuid'));
        $uuid = $session->getProperty('/tests_general_base/idExample/jcr:content/weakreference_target/jcr:uuid');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $uuid);
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $uuid->getString());

        $ref = $session->getProperty('/tests_general_base/idExample/jcr:content/weakreference_source1/ref1');
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $ref->getType());
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $ref->getString());

        $session = $this->saveAndRenewSession();

        $this->assertTrue($session->nodeExists('/tests_general_base'));
        $this->assertTrue($session->propertyExists('/tests_general_base/idExample/jcr:content/weakreference_target/jcr:uuid'));
        $uuid = $session->getProperty('/tests_general_base/idExample/jcr:content/weakreference_target/jcr:uuid');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $uuid);
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $uuid->getString());

        $ref = $session->getProperty('/tests_general_base/idExample/jcr:content/weakreference_source1/ref1');
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $ref->getType());
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $ref->getString());
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testImportXMLSystemPathNotFoundSession()
    {
        $this->session->importXML('/inexistent-path', __DIR__.'/../../fixtures/general/base.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_THROW);
    }
    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testImportXMLSystemPathNotFoundWorkspace()
    {
        $this->session->getWorkspace()->importXML('/inexistent-path', __DIR__.'/../../fixtures/general/base.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_THROW);
    }

    /**
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testImportXMLSystemIdCollisionSession()
    {
        self::$staticSharedFixture['ie']->import('11_Import/idnode');
        $session = $this->renewSession();
        $session->importXML('/', __DIR__.'/../../fixtures/general/base.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_THROW);
    }

    /**
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testImportXMLSystemIdCollisionWorkspace()
    {
        self::$staticSharedFixture['ie']->import('11_Import/idnode');
        $session = $this->renewSession();
        $session->getWorkspace()->importXML('/', __DIR__.'/../../fixtures/general/base.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_THROW);
    }

    /**
     * try to replace the path to which we are importing atm
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testImportXMLUuidRemoveParentSession()
    {
        self::$staticSharedFixture['ie']->import('11_Import/idnode');
        $session = $this->renewSession();
        $session->importXML('/container/idExample', __DIR__.'/../../fixtures/general/base.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_REMOVE_EXISTING);
    }

    /**
     * try to replace the path to which we are importing atm
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testImportXMLUuidRemoveParentWorkspace()
    {
        self::$staticSharedFixture['ie']->import('11_Import/idnode');
        $session = $this->renewSession();
        $session->getWorkspace()->importXML('/container/idExample', __DIR__.'/../../fixtures/general/base.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_REMOVE_EXISTING);
    }

    public function testImportXMLUuidNewSession()
    {
        self::$staticSharedFixture['ie']->import('11_Import/idnode');
        $session = $this->renewSession();
        $this->doTestImportXMLUuidNew($session, $session);
    }
    public function testImportXMLUuidNewWorkspace()
    {
        self::$staticSharedFixture['ie']->import('11_Import/idnode');
        $session = $this->renewSession();
        $this->doTestImportXMLUuidNew($session->getWorkspace(), $session);
    }
    private function doTestImportXMLUuidNew($connect, $session)
    {
        $connect->importXML('/', __DIR__.'/../../fixtures/general/base.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_CREATE_NEW);

        // existing node did not change its uuid
        $this->assertTrue($session->nodeExists('/container/idExample'));
        $idExample = $session->getNode('/container/idExample');
        $this->assertEquals('842e61c0-09ab-42a9-87c0-308ccc90e6f4', $idExample->getIdentifier());

        $this->assertTrue($session->nodeExists('/tests_general_base'));

        $this->assertTrue($session->propertyExists('/tests_general_base/idExample/jcr:content/weakreference_source1/ref1'));
        $ref = $session->getProperty('/tests_general_base/idExample/jcr:content/weakreference_source1/ref1');
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $ref->getType());
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $ref->getString());

        $session = $this->saveAndRenewSession();

        // existing node did not change its uuid
        $this->assertTrue($session->nodeExists('/container/idExample'));
        $idExample = $session->getNode('/container/idExample');
        $this->assertEquals('842e61c0-09ab-42a9-87c0-308ccc90e6f4', $idExample->getIdentifier());

        $this->assertTrue($session->nodeExists('/tests_general_base'));

        // imported node got a new uuid
        $this->assertTrue($session->nodeExists('/tests_general_base/idExample'));
        $this->assertTrue($session->propertyExists('/tests_general_base/idExample/jcr:uuid'));
        $newId = $session->getNode('/tests_general_base/idExample');
        $this->assertNotEquals('842e61c0-09ab-42a9-87c0-308ccc90e6f4', $newId->getIdentifier());

        // reference from input points to existing node
        $this->assertTrue($session->propertyExists('/tests_general_base/numberPropertyNode/jcr:content/ref'));
        $ref = $session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/ref');
        $this->assertEquals('842e61c0-09ab-42a9-87c0-308ccc90e6f4', $ref->getString());

        // get the uuid of an imported node that had no collision
        $this->assertTrue($session->propertyExists('/tests_general_base/idExample/jcr:content/weakreference_target/jcr:uuid'));
        $uuid = $session->getProperty('/tests_general_base/idExample/jcr:content/weakreference_target/jcr:uuid');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $uuid);
        $target = $uuid->getString();

        // the reference must still point to that node. the uuid might has changed (implementation detail if non-collision uuid change or not)
        $ref = $session->getProperty('/tests_general_base/idExample/jcr:content/weakreference_source1/ref1');
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $ref->getType());
        $this->assertEquals($target, $ref->getString());
    }

    public function testImportXMLUuidRemoveExistingSession()
    {
        self::$staticSharedFixture['ie']->import('11_Import/idnode');
        $session = $this->renewSession();
        $this->doTestImportXMLUuidRemoveExisting($session, $session);
    }
    public function testImportXMLUuidRemoveExistingWorkspace()
    {
        self::$staticSharedFixture['ie']->import('11_Import/idnode');
        $session = $this->renewSession();
        $this->doTestImportXMLUuidRemoveExisting($session->getWorkspace(), $session);
    }

    private function doTestImportXMLUuidRemoveExisting($connect, $session)
    {
        $connect->importXML('/', __DIR__.'/../../fixtures/general/base.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_REMOVE_EXISTING);

        // existing node removed
        $this->assertFalse($session->nodeExists('/container/idExample'));

        // the rest is the same as with empty repo
        $this->assertTrue($session->nodeExists('/tests_general_base'));

        $idExample = $session->getNode('/tests_general_base/idExample');
        $this->assertEquals('842e61c0-09ab-42a9-87c0-308ccc90e6f4', $idExample->getIdentifier());

        $this->assertTrue($session->propertyExists('/tests_general_base/idExample/jcr:content/weakreference_target/jcr:uuid'));
        $uuid = $session->getProperty('/tests_general_base/idExample/jcr:content/weakreference_target/jcr:uuid');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $uuid);
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $uuid->getString());

        $ref = $session->getProperty('/tests_general_base/idExample/jcr:content/weakreference_source1/ref1');
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $ref->getType());
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $ref->getString());

        $session = $this->saveAndRenewSession();

        // existing node removed
        $this->assertFalse($session->nodeExists('/container/idExample'));

        // the rest is the same as with empty repo
        $this->assertTrue($session->nodeExists('/tests_general_base'));
        $this->assertTrue($session->propertyExists('/tests_general_base/idExample/jcr:content/weakreference_target/jcr:uuid'));
        $uuid = $session->getProperty('/tests_general_base/idExample/jcr:content/weakreference_target/jcr:uuid');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $uuid);
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $uuid->getString());

        $ref = $session->getProperty('/tests_general_base/idExample/jcr:content/weakreference_source1/ref1');
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $ref->getType());
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $ref->getString());
    }

    public function testImportXMLUuidReplaceExistingSession()
    {
        self::$staticSharedFixture['ie']->import('11_Import/idnode');
        $session = $this->renewSession();
        $this->doTestImportXMLUuidReplaceExisting($session, $session);
    }
    public function testImportXMLUuidReplaceExistingWorkspace()
    {
        self::$staticSharedFixture['ie']->import('11_Import/idnode');
        $session = $this->renewSession();
        $this->doTestImportXMLUuidReplaceExisting($session->getWorkspace(), $session);
    }
    private function doTestImportXMLUuidReplaceExisting($connect, $session)
    {
        $session->importXML('/', __DIR__.'/../../fixtures/general/base.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_REPLACE_EXISTING);

        // existing node replaced
        $this->assertTrue($session->nodeExists('/container/idExample'));
        $idExample = $session->getNode('/container/idExample');
        $this->assertEquals('nt:file', $idExample->getPrimaryNodeType()->getName());
        $this->assertEquals('842e61c0-09ab-42a9-87c0-308ccc90e6f4', $idExample->getIdentifier());
        $this->assertTrue($session->nodeExists('/container/idExample/jcr:content'));

        $this->assertFalse($session->nodeExists('/tests_general_base/idExample'));

        // the rest is the same as with empty repo
        $this->assertTrue($session->nodeExists('/tests_general_base'));
        $this->assertTrue($session->nodeExists('/tests_general_base/test:namespacedNode'));

        $ref = $session->getProperty('/container/idExample/jcr:content/weakreference_source1/ref1');
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $ref->getType());
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $ref->getString());

        $session = $this->saveAndRenewSession();

        // existing node replaced
        $this->assertTrue($session->nodeExists('/container/idExample'));
        $idExample = $session->getNode('/container/idExample');
        $this->assertEquals('nt:file', $idExample->getPrimaryNodeType()->getName());
        $this->assertEquals('842e61c0-09ab-42a9-87c0-308ccc90e6f4', $idExample->getIdentifier());
        $this->assertTrue($session->nodeExists('/container/idExample/jcr:content'));

        $this->assertFalse($session->nodeExists('/tests_general_base/idExample'));

        // the rest is the same as with empty repo
        $this->assertTrue($session->nodeExists('/tests_general_base'));
        $this->assertTrue($session->nodeExists('/tests_general_base/test:namespacedNode'));

        $ref = $session->getProperty('/container/idExample/jcr:content/weakreference_source1/ref1');
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $ref->getType());
        $this->assertEquals('13543fc6-1abf-4708-bfcc-e49511754b40', $ref->getString());
    }

    public function testImportXMLUuidReplaceRoot()
    {
        self::$staticSharedFixture['ie']->import('general/base');
        $session = $this->renewSession();
        $session->getRootNode()->addMixin('mix:referenceable');
        $session = $this->saveAndRenewSession();
        $id = $session->getRootNode()->getIdentifier();
        $this->assertTrue(\PHPCR\Util\UUIDHelper::isUUID($id));
        $filename = tempnam('/tmp', '');
        $file = fopen($filename, 'w+');
        fwrite($file, str_replace('XXX_ROOT_ID_XXX', $id, file_get_contents(__DIR__.'/../../fixtures/11_Import/rootnode.xml')));
        fclose($file);
        $session->importXML('/', $filename, ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_REPLACE_EXISTING);

        $this->assertFalse($session->nodeExists('/jcr:root'));
        $this->assertTrue($session->propertyExists('/test'));
        $prop = $session->getRootNode()->getProperty('test');
        $this->assertEquals('A test string', $prop->getString());
        $this->assertTrue($session->nodeExists('/testChild'));

        $session = $this->saveAndRenewSession();

        $this->assertFalse($session->nodeExists('/jcr:root'));
        $this->assertTrue($session->propertyExists('/test'));
        $prop = $session->getRootNode()->getProperty('test');
        $this->assertEquals('A test string', $prop->getString());
        $this->assertTrue($session->nodeExists('/testChild'));
    }

    /**
     * Provoke an io error
     *
     * @expectedException \RuntimeException
     */
    public function testImportXMLNoFile()
    {
        $this->session->importXML('/', 'nonexisting.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_THROW);
    }

/*
    TODO: can we make XMLReader throw exception instead of whatever it does now?
    public function testImportXMLNoXml()
    {
        $this->session->importXML('/', __FILE__, ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_THROW);
    }
*/

    public function testImportXMLDocument()
    {
        // TODO: have a node that tests unescaping in the documentview.xml and check

        self::$staticSharedFixture['ie']->import('11_Import/idnode');
        $session = $this->renewSession();
        $session->importXML('/', __DIR__.'/../../fixtures/11_Import/documentview.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_CREATE_NEW);

        // existing node did not change its uuid
        $this->assertTrue($session->nodeExists('/container/idExample'));
        $idExample = $session->getNode('/container/idExample');
        $this->assertEquals('842e61c0-09ab-42a9-87c0-308ccc90e6f4', $idExample->getIdentifier());

        $this->assertTrue($session->nodeExists('/tests_import'));

        $this->assertTrue($session->nodeExists('/tests_import/idExample'));
        $id = $session->getNode('/tests_import/idExample');
        $this->assertTrue($id->isNodeType('mix:referenceable'));

        $this->assertTrue($session->propertyExists('/tests_import/idExample/jcr:content/weakreference_source1/ref1'));
        $ref = $session->getProperty('/tests_import/idExample/jcr:content/weakreference_source1/ref1');
        $this->assertTrue(\PHPCR\Util\UUIDHelper::isUUID($ref->getString()));

        $session = $this->saveAndRenewSession();

        // existing node did not change its uuid
        $this->assertTrue($session->nodeExists('/container/idExample'));
        $idExample = $session->getNode('/container/idExample');
        $this->assertEquals('842e61c0-09ab-42a9-87c0-308ccc90e6f4', $idExample->getIdentifier());

        $this->assertTrue($session->nodeExists('/tests_import'));

        $this->assertTrue($session->nodeExists('/tests_import/idExample'));
        $id = $session->getNode('/tests_import/idExample');
        $this->assertTrue($id->isNodeType('mix:referenceable'));

        $this->assertTrue($session->propertyExists('/tests_import/idExample/jcr:content/weakreference_source1/ref1'));
        $ref = $session->getProperty('/tests_import/idExample/jcr:content/weakreference_source1/ref1');
        $this->assertTrue(\PHPCR\Util\UUIDHelper::isUUID($ref->getString()));
    }

    public function testImportXMLDocumentSimple()
    {
        self::$staticSharedFixture['ie']->import('11_Import/empty');
        $session = $this->renewSession();

        $session->importXML('/', __DIR__.'/../../fixtures/11_Import/simple.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_THROW);

        $this->assertTrue($session->nodeExists('/data/node'));
        $this->assertTrue($session->nodeExists('/data/sibling/child1'));
        $this->assertEquals('Test', $session->getProperty('/data/sibling/title')->getValue());

        $session = $this->saveAndRenewSession();

        $this->assertTrue($session->nodeExists('/data/node'));
        $this->assertTrue($session->nodeExists('/data/sibling/child1'));
        $this->assertEquals('Test', $session->getProperty('/data/sibling/title')->getValue());
    }
}
