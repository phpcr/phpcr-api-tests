<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
 * Test the NoteType §8
 */
class NodeTypeDiscovery_8_NodeTypeTest extends jackalope_baseCase
{
    private $nodeTypeManager;

    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('general/base');
    }

    public function setUp() {
        parent::setUp();
        $this->nodeTypeManager = $this->sharedFixture['session']->getWorkspace()->getNodeTypeManager();
        //TODO: have type
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

    /**
     */
    public function testGetDeclaredSubtypesNoType()
    {
        $this->nodeTypeManager->getDeclaredSubtypes('no-such-type');
    }

    public function getSubtypes()
    {

    }

}
