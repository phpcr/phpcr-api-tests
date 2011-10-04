<?php
namespace PHPCR\Tests\NodeTypeDiscovery;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

/**
 * Test the NoteType §8
 *
 * Requires that NodeTypeManager->getNodeType works correctly
 */
class NodeTypeTest extends \PHPCR\Test\BaseCase
{
    private static $nodeType;
    private static $mixinType;

    static public function setupBeforeClass($fixtures = false)
    {
        parent::setupBeforeClass($fixtures);
        self::$nodeType = self::$staticSharedFixture['session']->getWorkspace()->getNodeTypeManager()->getNodeType('nt:file');
        self::$mixinType = self::$staticSharedFixture['session']->getWorkspace()->getNodeTypeManager()->getNodeType('mix:versionable');
    }

    public function testGetSupertypes()
    {
        //TODO: work on this type.
        $this->markTestIncomplete('TODO: what to expect?');
    }

    public function testGetSupertypeNames()
    {
        //TODO: work on this type.
        $this->markTestIncomplete('TODO: what to expect?');
    }

    public function testGetDeclaredSupertypes()
    {
        //TODO: work on this type.
        $this->markTestIncomplete('TODO: what to expect?');
    }

    public function testGetSubtypes()
    {
        //TODO: work on this type.
        $this->markTestIncomplete('TODO: what to expect?');
    }

    /* TODO
        canAddChildNode
        canRemoveNode
        canRemoveProperty
        canSetProperty
        getChildNodeDefinitions
        getPropertyDefinitions
    */

    public function testIsPrimaryNodeType()
    {
        $this->assertTrue(self::$nodeType->isNodeType('nt:file'));
        $this->assertTrue(self::$nodeType->isNodeType('nt:hierarchyNode'));
        $this->assertTrue(self::$nodeType->isNodeType('nt:base'));
        $this->assertFalse(self::$nodeType->isNodeType('nt:unstructured'));
    }

    public function testIsMixinNodeType()
    {
        $this->assertTrue(self::$mixinType->isNodeType('mix:versionable'));
        $this->assertTrue(self::$mixinType->isNodeType('mix:referenceable'));
        $this->assertFalse(self::$mixinType->isNodeType('mix:lockable'));
    }
}
