<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
 * Testing whether getting predecessor / successor works correctly
 *
 * Covering jcr-283 spec $15.1
 */
class Versioning_15_VersionTest extends phpcr_suite_baseCase
{
    /** the versionmanager instance */
    private $vm;
    /** a versioned node */
    private $version;

    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('15_Versioning/base');

        //have some versions
        try
        {
            $vm = self::$staticSharedFixture['session']->getWorkspace()->getVersionManager();
        } catch (\PHPCR\UnSupportedRepositoryOperationException $e) {
            return;
        }
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
        self::$staticSharedFixture['session'] = getJCRSession(self::$staticSharedFixture['config']); //reset the session
    }

    public function setUp()
    {
        parent::setUp();
        try
        {
            $this->vm = $this->sharedFixture['session']->getWorkspace()->getVersionManager();
        } catch (\PHPCR\UnSupportedRepositoryOperationException $e) {
            $this->markTestSkipped("Versioning not supported: " . $e->getMessage());
        }

        $this->version = $this->vm->getBaseVersion("/tests_version_base/versioned");

        $this->assertInstanceOf('PHPCR\Version\VersionInterface', $this->version);
    }

    //TODO: missing methods

    public function testGetPredecessors() {
        $versions = $this->version->getPredecessors();
        $this->assertEquals(1, count($versions));
        $pred = $versions[0];
        $this->assertInstanceOf('PHPCR\Version\VersionInterface', $pred);
        $versions = $pred->getSuccessors();
        $this->assertEquals(1, count($versions), 'expected a successor of our predecessor');
        $this->assertSame($this->version, $versions[0]);

        //TODO: how to access the data of the older version?
    }

    public function testGetSuccessors() {
        $versions = $this->version->getSuccessors();
        $this->assertEquals(0, count($versions));
    }

}
