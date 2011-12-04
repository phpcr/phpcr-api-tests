<?php
namespace PHPCR\Tests\Versioning;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * test some more NodeInterface::isNodeType (read) §8.6 things that can only
 * be done if there is mixin node types that inherit from another mixin.
 *
 * the only case for that in the default types is with mix:versionable
 */
class NodeNodeTypeReadMethodsTest extends \PHPCR\Test\BaseCase
{
    protected $versioned;

    static public function setupBeforeClass($fixtures = '15_Versioning/base')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        parent::setUp();

        $this->versioned = $this->sharedFixture['session']->getNode("/tests_version_base/versioned");
    }

    public function testIsMixin()
    {
        $this->assertTrue($this->versioned->isNodeType('mix:versionable'));
    }

    public function testIsParentMixin()
    {
        $this->assertTrue($this->versioned->isNodeType('mix:referenceable'));
    }

    public function testIsNotMixin()
    {
        $this->assertFalse($this->versioned->isNodeType('mix:language'));
    }

}
