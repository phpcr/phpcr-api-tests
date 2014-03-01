<?php
namespace PHPCR\Tests\Versioning;

use PHPCR\NodeInterface;
use PHPCR\Version\VersionInterface;
use PHPCR\Version\VersionManagerInterface;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Testing whether getting predecessor / successor works correctly
 *
 * Covering jcr-283 spec $15.1
 */
class VersionTest extends \PHPCR\Test\BaseCase
{
    /**
     * @var VersionManagerInterface
     */
    private $vm;

    /**
     * @var VersionInterface
     */
    private $version;

    public static function setupBeforeClass($fixtures = '15_Versioning/base')
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

        $this->vm = $this->session->getWorkspace()->getVersionManager();

        $this->version = $this->vm->getBaseVersion('/tests_version_base/versioned');

        $this->assertInstanceOf('PHPCR\Version\VersionInterface', $this->version);
    }

    public function testGetContainingHistory()
    {
        $this->assertSame($this->vm->getVersionHistory('/tests_version_base/versioned'), $this->version->getContainingHistory());
    }

    public function testGetCreated()
    {
        $date = $this->version->getCreated();
        $diff = time() - $date->getTimestamp();
        $this->assertTrue($diff < 60, 'creation date of the version we created in setupBeforeClass should be within the last few seconds');
    }

    public function testGetFrozenNode()
    {
        $frozen = $this->version->getFrozenNode();
        $this->assertTrue($frozen->hasProperty('foo'));
        $this->assertEquals('bar3', $frozen->getPropertyValue('foo'));

        $predecessors = $this->version->getPredecessors();
        $this->assertInternalType('array', $predecessors);
        $firstVersion = reset($predecessors);
        $this->assertInstanceOf('PHPCR\Version\VersionInterface', $firstVersion);
        $frozen2 = $firstVersion->getFrozenNode();
        $this->assertInstanceOf('PHPCR\NodeInterface', $firstVersion);
        /** @var $frozen2 NodeInterface */
        $this->assertTrue($frozen2->hasProperty('foo'));
        $this->assertEquals('bar2', $frozen2->getPropertyValue('foo'));
    }

    /**
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     * @depends testGetFrozenNode
     */
    public function testFrozenNode()
    {
        $frozen = $this->version->getFrozenNode();
        $frozen->setProperty('foo', 'should not work');
        self::$staticSharedFixture['session']->save();
    }

    public function testGetLinearPredecessorSuccessor()
    {
        $pred = $this->version->getLinearPredecessor();
        $this->assertInstanceOf('PHPCR\Version\VersionInterface', $pred);
        $succ = $pred->getLinearSuccessor();
        $this->assertSame($this->version, $succ);
    }

    public function testGetLinearPredecessorNull()
    {
        $rootVersion = $this->vm->getVersionHistory('/tests_version_base/versioned')->getRootVersion();
        // base version is at end of chain
        $this->assertNull($rootVersion->getLinearPredecessor());
    }

    public function testGetLinearSuccessorNull()
    {
        // base version is at end of chain
        $this->assertNull($this->version->getLinearSuccessor());
    }

    public function testGetPredecessors()
    {
        $versions = $this->version->getPredecessors();
        $this->assertCount(1, $versions);
        $pred = $versions[0];
        $this->assertInstanceOf('PHPCR\Version\VersionInterface', $pred);
        $versions = $pred->getSuccessors();
        $this->assertCount(1, $versions, 'expected a successor of our predecessor');
        $this->assertSame($this->version, $versions[0]);
    }

    public function testGetSuccessors()
    {
        $versions = $this->version->getSuccessors();
        $this->assertCount(0, $versions);
    }

    /**
     * Check $version->remove() is not possible. This must go through VersionHistory::remove
     *
     * @expectedException \PHPCR\RepositoryException
     */
    public function testNodeRemoveOnVersion()
    {
        $version = $this->vm->checkpoint('/tests_version_base/versioned');
        $version->remove();
    }
}
