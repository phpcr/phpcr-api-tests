<?php
namespace PHPCR\Tests\NodeTypeManagement;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Covering jcr-2.8.3 spec $19
 */
class ManipulationTest extends \PHPCR\Test\BaseCase
{

    protected function setUp()
    {
        $this->renewSession(); // reset session
        parent::setUp();
    }

    public function testRegisterNodeTypesCnd()
    {
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $ntm = $workspace->getNodeTypeManager();

        $types = $ntm->registerNodeTypesCnd($this->cnd, true);
        $this->assertEquals(2, count($types), 'Wrong number of nodes registered');
        list($name, $type) = each($types);
        $this->assertEquals('phpcr:apitest', $name);
        $this->assertInstanceOf('PHPCR\NodeType\NodeTypeDefinitionInterface', $type);
        list($name, $type) = each($types);
        $this->assertEquals('phpcr:test', $name);
        $this->assertInstanceOf('PHPCR\NodeType\NodeTypeDefinitionInterface', $type);
        $props = $type->getDeclaredPropertyDefinitions();
        $this->assertEquals(1, count($props), 'Wrong number of properties in phpcr:test');
        $this->assertEquals('phpcr:prop', $props[0]->getName());
    }

    /**
     * @depends testRegisterNodeTypesCnd
     */
    public function testValidateCustomNodeType()
    {
        $node = $this->rootNode->getNode('tests_general_base');
        try {
            // the node is of type nt:folder - it can only have allowed properties
            $node->setProperty('phpcr:prop', 'test');
            $this->fail('This node should not accept the property');
        } catch (\PHPCR\NodeType\ConstraintViolationException $e) {
            // expected
        }
        $node->addMixin('phpcr:test');
        $node->setProperty('phpcr:prop', 'test');
    }

    /**
     * @expectedException \PHPCR\NodeType\NodeTypeExistsException
     */
    public function testRegisterNodeTypesCndNoUpdate()
    {
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $ntm = $workspace->getNodeTypeManager();
        $types = $ntm->registerNodeTypesCnd($this->cnd, false);
        $types = $ntm->registerNodeTypesCnd($this->cnd, false);
    }

    public function testPrimaryItem()
    {
        // Create the node type
        $session = $this->sharedFixture['session'];
        $ntm = $session->getWorkspace()->getNodeTypeManager();
        $ntm->registerNodeTypesCnd($this->primary_item_cnd, true);

        // Create a node of that type
        $root = $session->getRootNode();

        if ($root->hasNode('test_node')) {
            $node = $root->getNode('test_node');
            $node->remove();
            $session->save();
        }

        $node = $root->addNode('test_node', 'phpcr:primary_item_test');
        $node->setProperty("phpcr:content", 'test');
        $session->save();

        // Check the primary item of the new node
        $primary = $node->getPrimaryItem();
        $this->assertInstanceOf('PHPCR\ItemInterface', $node);
        $this->assertEquals('phpcr:content', $primary->getName());
    }

    private $cnd = "
        <'phpcr'='http://www.doctrine-project.org/projects/phpcr_odm'>
         [phpcr:apitest]
          mixin
          - phpcr:class (string)
          [phpcr:test]
          mixin
          - phpcr:prop (string)
          ";

    private $primary_item_cnd = "
        <'phpcr'='http://www.doctrine-project.org/projects/phpcr_odm'>
        [phpcr:primary_item_test]
        - phpcr:content (string)
        primary
        ";
}
