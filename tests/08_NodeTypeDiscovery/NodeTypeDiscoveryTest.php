<?php
namespace PHPCR\Tests\NodeTypeDiscovery;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

/**
 * Test the NoteTypeManager §8
 */
class NodeTypeDiscoveryTest extends \PHPCR\Test\BaseCase
{
    private $nodeTypeManager;

    public function setUp()
    {
        parent::setUp(false);
        $this->nodeTypeManager = $this->sharedFixture['session']->getWorkspace()->getNodeTypeManager();
    }

    public function testGetNodeType()
    {
        $type = $this->nodeTypeManager->getNodeType('nt:folder');
        $this->assertInstanceOf('\PHPCR\NodeType\NodeTypeInterface', $type);
        $this->markTestIncomplete('TODO: what to expect?');
    }

//TODO: mixin type!

    /**
     * @expectedException \PHPCR\NodeType\NoSuchNodeTypeException
     */
    public function testGetNodeTypeNoSuch()
    {
        $this->nodeTypeManager->getNodeType('no-such-type');
    }

    public function hasNodeType()
    {
        $this->assertTrue($this->nodeTypeManager->hasNodeType('nt:file'));
        $this->assertFalse($this->nodeTypeManager->hasNodeType('no-such-type'));
    }

}
