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
        $history->removeVersion($version->getName());
    }

}
