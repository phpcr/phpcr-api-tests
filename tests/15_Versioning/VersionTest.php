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
    /** a versioned child node */
    private $childVersion;

    static public function setupBeforeClass($fixtures = '15_Versioning/base')
    {
        parent::setupBeforeClass($fixtures);

        //have some versions
        $vm = self::$staticSharedFixture['session']->getWorkspace()->getVersionManager();

        $node = self::$staticSharedFixture['session']->getNode('/tests_version_base/versioned');
        $childNode = self::$staticSharedFixture['session']->getNode('/tests_version_base/versioned/version_child');
        self::$staticSharedFixture['session']->save();

        $vm->checkpoint('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar');
        $childNode->setProperty('foo_c', 'bar_c');
        self::$staticSharedFixture['session']->save();

        $vm->checkpoint('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar2');
        $childNode->setProperty('foo_c', 'bar2_c');
        self::$staticSharedFixture['session']->save();

        $vm->checkpoint('/tests_version_base/versioned');
        $node->setProperty('foo', 'bar3');
        $childNode->setProperty('foo_c', 'bar3_c');
        self::$staticSharedFixture['session']->save();

        $vm->checkin('/tests_version_base/versioned');
        self::$staticSharedFixture['session'] = self::$loader->getSession(); //reset the session
    }

    public function setUp()
    {
        parent::setUp();

        $this->vm = $this->sharedFixture['session']->getWorkspace()->getVersionManager();

        $this->version = $this->vm->getBaseVersion('/tests_version_base/versioned');
        $this->childVersion = $this->vm->getBaseVersion('/tests_version_base/versioned/version_child');

        $this->assertInstanceOf('PHPCR\Version\VersionInterface', $this->version);
    }

    public function testGetContainingHistory()
    {
        $this->assertSame($this->vm->getVersionHistory('/tests_version_base/versioned'), $this->version->getContainingHistory());
    }

    public function testGetContainingChildHistory()
    {
        $this->assertSame($this->vm->getVersionHistory('/tests_version_base/versioned/version_child'), $this->childVersion->getContainingHistory());
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
        $frozen2 = reset($predecessors)->getFrozenNode();
        $this->assertTrue($frozen2->hasProperty('foo'));
        $this->assertEquals('bar2', $frozen2->getPropertyValue('foo'));
    }

    public function testGetFrozenChildNode()
    {
        $frozen = $this->childVersion->getFrozenNode();
        $this->assertTrue($frozen->hasProperty('foo_c'));
        $this->assertEquals('bar3_c', $frozen->getPropertyValue('foo_c'));

        $predecessors = $this->childVersion->getPredecessors();
        $frozen2 = reset($predecessors)->getFrozenNode();
        $this->assertTrue($frozen2->hasProperty('foo_c'));
        $this->assertEquals('bar2_c', $frozen2->getPropertyValue('foo_c'));
    }

    /**
     * @expectedException PHPCR\NodeType\ConstraintViolationException
     * @depends testGetFrozenNode
     */
    public function testFrozenNode()
    {
        $frozen = $this->version->getFrozenNode();
        $frozen->setProperty('foo', 'should not work');
        self::$staticSharedFixture['session']->save();
    }

    /**
     * @expectedException PHPCR\NodeType\ConstraintViolationException
     * @depends testGetFrozenNode
     */
    public function testFrozenChildNode()
    {
        $frozen = $this->childVersion->getFrozenNode();
        $frozen->setProperty('foo_c', 'should not work');
        self::$staticSharedFixture['session']->save();
    }

    public function testGetLinearPredecessorSuccessor()
    {
        $pred = $this->version->getLinearPredecessor();
        $this->assertInstanceOf('PHPCR\Version\VersionInterface', $pred);
        $succ = $pred->getLinearSuccessor();
        $this->assertSame($this->version, $succ);
    }

    public function testGetChildLinearPredecessorSuccessor()
    {
        $pred = $this->childVersion->getLinearPredecessor();
        $this->assertInstanceOf('PHPCR\Version\VersionInterface', $pred);
        $succ = $pred->getLinearSuccessor();
        $this->assertSame($this->childVersion, $succ);
    }

    public function testGetLinearPredecessorNull()
    {
        $rootVersion = $this->vm->getVersionHistory('/tests_version_base/versioned')->getRootVersion();
        // base version is at end of chain
        $this->assertNull($rootVersion->getLinearPredecessor());
    }

    public function testGetChildLinearPredecessorNull()
    {
        $rootVersion = $this->vm->getVersionHistory('/tests_version_base/versioned/version_child')->getRootVersion();
        // base version is at end of chain
        $this->assertNull($rootVersion->getLinearPredecessor());
    }

    public function testGetLinearSuccessorNull()
    {
        // base version is at end of chain
        $this->assertNull($this->version->getLinearSuccessor());
    }

    public function testGetChildLinearSuccessorNull()
    {
        // base version is at end of chain
        $this->assertNull($this->childVersion->getLinearSuccessor());
    }

    public function testGetPredecessors()
    {
        $versions = $this->version->getPredecessors();
        $this->assertEquals(1, count($versions));
        $pred = $versions[0];
        $this->assertInstanceOf('PHPCR\Version\VersionInterface', $pred);
        $versions = $pred->getSuccessors();
        $this->assertEquals(1, count($versions), 'expected a successor of our predecessor');
        $this->assertSame($this->version, $versions[0]);
    }

    public function testGetChildPredecessors()
    {
        $versions = $this->childVersion->getPredecessors();
        $this->assertEquals(1, count($versions));
        $pred = $versions[0];
        $this->assertInstanceOf('PHPCR\Version\VersionInterface', $pred);
        $versions = $pred->getSuccessors();
        $this->assertEquals(1, count($versions), 'expected a successor of our predecessor');
        $this->assertSame($this->childVersion, $versions[0]);
    }

    public function testGetSuccessors()
    {
        $versions = $this->version->getSuccessors();
        $this->assertEquals(0, count($versions));
    }

    public function testGetChildSuccessors()
    {
        $versions = $this->childVersion->getSuccessors();
        $this->assertEquals(0, count($versions));
    }

    /**
     * Check $version->remove() is not possible. This must go through VersionHistory::remove
     *
     * @expectedException PHPCR\RepositoryException
     */
    public function testNodeRemoveOnVersion()
    {
        $version = $this->vm->checkpoint('/tests_version_base/versioned');
        $version->remove();
    }
}
