<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
 * Covering jcr-2.8.3 spec $19
 *
 * (only a few tests, lots is tested by unit tests)
 */
class Write_Manipulation_MoveMethodsTest extends jackalope_baseCase
{

    protected function setUp()
    {
        $this->renewSession();
        parent::setUp();
    }


    /**
     * @covers Jackalope\NodeTypeManager::registerNodeTypesCnd
     */
    public function testRegisterNodeTypesCnd()
    {
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $ntm = $workspace->getNodeTypeManager();

        $cnd = "
        <'phpcr'='http://www.doctrine-project.org/phpcr-odm'>
         [phpcr:managed]
          mixin
          - phpcr:alias (string)
          [phpcr:test]
          mixin
          - phpcr:prop (string)
          ";
        $types = $ntm->registerNodeTypesCnd($cnd, true);
        $this->assertEquals(2, count($types), 'Wrong number of nodes registered');
        list($name, $type) = each($types);
        $this->assertEquals('phpcr:managed', $name);
        $this->assertType('PHPCR\NodeType\NodeTypeDefinitionInterface', $type);
        list($name, $type) = each($types);
        $this->assertEquals('phpcr:test', $name);
        $this->assertType('PHPCR\NodeType\NodeTypeDefinitionInterface', $type);
        $props = $type->getDeclaredPropertyDefinitions();
        $this->assertEquals(1, count($props), 'Wrong number of properties in phpcr:test');
        $this->assertEquals('phpcr:prop', $props[0]->getName());

        /* we could test if all options of cdn are properly translated, but that
         * is jackrabbit code and tested over there.
         * we just read the created nodes from the server. reading everything
         * properly is to be tested in node type read tests.
         */
    }
}
