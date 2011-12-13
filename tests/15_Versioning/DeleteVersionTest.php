<?php
namespace PHPCR\Tests\Versioning;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
* Testing whether deleting versions works
*
* Covering jcr-2.8.3 spec $15.1
*/
class DeleteVersionTest extends \PHPCR\Test\BaseCase
{
    static public function setupBeforeClass($fixtures = '15_Versioning/base')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        parent::setUp();
        $this->vm = $this->sharedFixture['session']->getWorkspace()->getVersionManager();
    }

    /**
     * Create two versions then delete the first version
     *
     * Note that you can not use $version->remove() although version is a node.
     */
    public function testDeleteversion()
    {
        $version = $this->vm->checkpoint("/tests_version_base/versioned");
        $this->vm->checkpoint("/tests_version_base/versioned"); // create another version, the last version can not be removed
        $history = $this->vm->getVersionHistory("/tests_version_base/versioned");

        // The version exists before removal
        $this->assertTrue($this->sharedFixture['session']->itemExists($version->getPath()));

        $history->removeVersion($version->getName());

        // The version is gone after removal
        // TODO: the JCR spec says at §15.8: "This change is a workspace-write; there is no need to call save."
        //       so why does this test fail if the session is not saved and renewed?
        $this->saveAndRenewSession();
        $this->assertFalse($this->sharedFixture['session']->itemExists($version->getPath()));
    }

    /**
     * Check the last version cannot be removed
     *
     * @expectedException PHPCR\ReferentialIntegrityException
     */
    public function testDeleteLastVersion()
    {
        $version = $this->vm->checkpoint("/tests_version_base/versioned");
        $history = $this->vm->getVersionHistory("/tests_version_base/versioned");
        $history->removeVersion($version->getName());
    }
     /**
     * Try removing an unexisting version
     *
     * @expectedException PHPCR\Version\VersionException
     */
    public function testDeleteUnexistingVersion()
    {
        $version = $this->vm->checkpoint("/tests_version_base/versioned");
        $history = $this->vm->getVersionHistory("/tests_version_base/versioned");
        $history->removeVersion('unexisting');
    }
}
