<?php
namespace PHPCR\Tests\Versioning;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Testing whether getting predecessor / successor works correctly
 *
 * Covering jcr-283 spec $15.1
 */
class VersionTest extends \PHPCR\Test\BaseCase
{
    /** the versionmanager instance */
    private $vm;
    /** a versioned node */
    private $version;

    static public function setupBeforeClass($fixtures = '15_Versioning/base')
    {
        parent::setupBeforeClass($fixtures);

        //have some versions
        $vm = self::$staticSharedFixture['session']->getWorkspace()->getVersionManager();

        $node = self::$staticSharedFixture['session']->getNode('/tests_version_base/versioned');
        $vm->checkpoint('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar');
        self::$staticSharedFixture['session']->save();
        $vm->checkpoint('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar2');
        self::$staticSharedFixture['session']->save();
        $vm->checkpoint('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar3');
        self::$staticSharedFixture['session']->save();
        $vm->checkin('/tests_version_base/versioned');
        self::$staticSharedFixture['session'] = self::$loader->getSession(); //reset the session
    }

    public function setUp()
    {
        parent::setUp();

        $this->vm = $this->sharedFixture['session']->getWorkspace()->getVersionManager();

        $this->version = $this->vm->getBaseVersion("/tests_version_base/versioned");

        $this->assertInstanceOf('PHPCR\Version\VersionInterface', $this->version);
    }

    //TODO: missing methods

    public function testGetPredecessors()
    {
        $versions = $this->version->getPredecessors();
        $this->assertEquals(1, count($versions));
        $pred = $versions[0];
        $this->assertInstanceOf('PHPCR\Version\VersionInterface', $pred);
        $versions = $pred->getSuccessors();
        $this->assertEquals(1, count($versions), 'expected a successor of our predecessor');
        $this->assertSame($this->version, $versions[0]);

        //TODO: how to access the data of the older version?
    }

    public function testGetSuccessors()
    {
        $versions = $this->version->getSuccessors();
        $this->assertEquals(0, count($versions));
    }

}
