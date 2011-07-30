<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
 * Test the NoteTypeManager §8
 *
 */
class NodeTypeDiscovery_8_NodeTypeDiscoveryTest extends phpcr_suite_baseCase
{
    private $nodeTypeManager;

    public function setUp() {
        parent::setUp();
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
