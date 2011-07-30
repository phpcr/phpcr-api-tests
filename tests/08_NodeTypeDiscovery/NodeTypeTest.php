<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
 * Test the NoteType §8
 *
 * Requires that NodeTypeManager->getNodeType works correctly
 */
class NodeTypeDiscovery_8_NodeTypeTest extends phpcr_suite_baseCase
{
    private static $nodeType;

    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$nodeType = self::$staticSharedFixture['session']->getWorkspace()->getNodeTypeManager()->getNodeType('nt:file');
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

    public function getSubtypes()
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
        isNodeType
    */

}
