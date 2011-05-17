<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
 * Covering jcr-2.8.3 spec $19
 *
 * (only a few tests, lots is tested by unit tests)
 */
class NodeTypeManagement_19_MoveMethodsTest extends jackalope_baseCase
{

    protected function setUp()
    {
        $this->renewSession();
        parent::setUp();
    }


    /**
     * registerNodeTypesCnd is implementation specific.
     * tests that test that method should only be executed when testing jackalope
     */
    protected function checkJackalope()
    {
        if (! $this->sharedFixture['session']->getWorkspace() instanceof \Jackalope\Workspace) {
            $this->markTestSkipped('This is a test for jackalope specific functionality');
        }
    }

    /**
     * @covers Jackalope\NodeTypeManager::registerNodeTypesCnd
     */
    public function testRegisterNodeTypesCnd()
    {
        $this->checkJackalope();
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $ntm = $workspace->getNodeTypeManager();

        $types = $ntm->registerNodeTypesCnd($this->cnd, true);
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

    /**
     * @covers Jackalope\NodeTypeManager::registerNodeTypesCnd
     * @expectedException \PHPCR\NodeType\NodeTypeExistsException
     */
    public function testRegisterNodeTypesCndNoUpdate()
    {
        $this->checkJackalope();
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $ntm = $workspace->getNodeTypeManager();
        $types = $ntm->registerNodeTypesCnd($this->cnd, false);
        $types = $ntm->registerNodeTypesCnd($this->cnd, false);
    }

    private $cnd = "
        <'phpcr'='http://www.doctrine-project.org/phpcr-odm'>
         [phpcr:managed]
          mixin
          - phpcr:alias (string)
          [phpcr:test]
          mixin
          - phpcr:prop (string)
          ";

}
