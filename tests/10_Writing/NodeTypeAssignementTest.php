<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
 * Test adding mixin to nodes.
 */
class Writing_10_NodeTypeAssignementTest extends phpcr_suite_baseCase
{
    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('10_Writing/nodetype');
    }

    public function setUp()
    {
        $this->renewSession();
        parent::setUp();

        //all tests in this suite have a node in the fixtures, but without the dataset in the name
        $name = $this->getName();
        if (false !== $pos = strpos($this->getName(), ' ')) {
            $name = substr($name, 0, $pos);
        }
        $this->node = $this->rootNode->getNode("tests_write_nodetype/$name");
    }

    /**
     * the mix: types are predefined types.
     *
     * we only use those that do not depend on optional features.
     */
    public static $mixins = array(
            "mix:etag", "mix:language", "mix:lastModified", "mix:mimeType",
            "mix:referenceable", "mix:shareable", "mix:title"
            );

    public static function mixinTypes() {
        $ret = array();
        foreach (self::$mixins as $mixin) {
            $ret[] = array($mixin);
        }
        return $ret;
    }

    /**
     * @dataProvider mixinTypes
     */
    public function testAddMixinOnNewNode($mixin)
    {
        $newNode = $this->node->addNode('parent-'.strtr($mixin, ':', '-'), 'nt:unstructured');
        $newNode->addMixin($mixin);
        $path = $newNode->getPath();
        $session = $this->saveAndRenewSession();
        $savedNode = $session->getNode($path);
        $resultTypes = array();
        foreach ($savedNode->getMixinNodeTypes() as $type) {
            $resultTypes[] = $type->getName();
        }
        $this->assertContains($mixin, $resultTypes, "Node mixins should contain $mixin");
    }

    /**
     * @dataProvider mixinTypes
     */
    public function testAddMixinOnExistingNode($mixin)
    {
        $node = $this->node->getNode(strtr($mixin, ':', '-'));
        $path = $node->getPath();
        $node->addMixin($mixin);
        $session = $this->saveAndRenewSession();
        $savedNode = $session->getNode($path);
        $resultTypes = array();
        foreach ($savedNode->getMixinNodeTypes() as $type) {
            $resultTypes[] = $type->getName();
        }
        $this->assertContains($mixin, $resultTypes, "Node mixins should contain $mixin");
    }

    /**
     * adding an already existing mixin should not set the node into the modified state
     * adding a mixin to a node that already has a mixin in the permanent storage should work too
     *
     * @group abc
     */
    public function testAddMixinTwice()
    {
        $this->assertFalse($this->node->isModified());
        $this->assertTrue($this->node->isNodeType('mix:referenceable'), 'error with the fixtures, the node should have this mixin type');
        $this->node->addMixin('mix:referenceable'); // this mixin is already in the fixtures
        $this->assertFalse($this->node->isModified());

        $this->markTestIncomplete('TODO: fix adding mixin when there is already one existing');

        $this->node->addMixin('mix:mimeType');
        $this->assertTrue($this->node->isModified());
        $this->sharedFixture['session']->save();
        $this->assertFalse($this->node->isModified());
        $this->node->addMixin('mix:mimeType');
        $this->assertFalse($this->node->isModified());
    }

    /**
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testAddMixinPrimaryType()
    {
        $this->node->addMixin('nt:unstructured');
        $this->saveAndRenewSession();
    }

    /**
     * Test that assigning an unexisting mixin type to a node will fail
     * @expectedException \PHPCR\NodeType\NoSuchNodeTypeException
     */
    public function testAddMixinNonexisting()
    {
        $this->node->addMixin('mix:nonexisting');
        $this->saveAndRenewSession();
    }
}
