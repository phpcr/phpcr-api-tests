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
    public function testDeleteVersion()
    {
        $nodePath = '/tests_version_base/versioned';
        $this->sharedFixture['session']->getNode($nodePath); // just to make sure this does not confuse anything

        $version = $this->vm->checkpoint($nodePath);
        $this->vm->checkpoint($nodePath); // create another version, the last version can not be removed
        $history = $this->vm->getVersionHistory($nodePath);

        // The version exists before removal
        $versionPath = $version->getPath();
        $versionName = $version->getName();

        $history->getAllVersions(); // load all versions so they land in cache
        $frozen = $history->getVersion($versionName)->getFrozenNode(); // also have the frozen node in cache
        $frozenPath = $frozen->getPath();

        $this->assertTrue($this->sharedFixture['session']->itemExists($versionPath));
        $this->assertTrue($this->versionExists($history, $versionName));

        // Remove the version
        $history->removeVersion($versionName);

        // The version is gone after removal
        $this->assertFalse($this->sharedFixture['session']->itemExists($versionPath));
        $this->assertFalse($this->sharedFixture['session']->itemExists($frozenPath));

        $this->assertFalse($this->versionExists($history, $versionName));
    }

    /**
     * Check the last version cannot be removed
     *
     * @expectedException PHPCR\ReferentialIntegrityException
     */
    public function testDeleteLatestVersion()
    {
        $version = $this->vm->checkpoint('/tests_version_base/versioned');
        $history = $this->vm->getVersionHistory('/tests_version_base/versioned');
        $history->removeVersion($version->getName());
    }

     /**
     * Try removing an unexisting version
     *
     * @expectedException PHPCR\Version\VersionException
     */
    public function testDeleteUnexistingVersion()
    {
        $version = $this->vm->checkpoint('/tests_version_base/versioned');
        $history = $this->vm->getVersionHistory('/tests_version_base/versioned');
        $history->removeVersion('unexisting');
    }

    /**
     * Check $version->remove() does not work
     *
     * @expectedException PHPCR\RepositoryException
     */
    public function testNodeRemoveOnVersion()
    {
        $version = $this->vm->checkpoint('/tests_version_base/versioned');
        $version->remove();
    }

    /**
     * Check if a version node with the given name exists in the version history
     * @param $history The version history node
     * @param $versionName The name of the version to search for
     * @return bool
     */
    protected function versionExists($history, $versionName)
    {
        foreach ($history->getAllVersions() as $version) {
            if ($version->getName() === $versionName) {
                return true;
            }
        }

        return false;
    }

}
