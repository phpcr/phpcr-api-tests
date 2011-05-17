<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
 * Test adding mixin to nodes.
 */
class Writing_10_AddMixinTest extends jackalope_baseCase
{
    public function setUp()
    {
        self::$staticSharedFixture['ie']->import('read/read/base'); //TODO: this is quite slow. adjust fixtures to the magic method name system and move this into setupBeforeClass
        $this->renewSession();
        parent::setUp();
        $this->node = $this->rootNode->getNode('tests_read_read_base/numberPropertyNode');
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
        $newNode = $this->rootNode->addNode('parent-'.strtr($mixin, ':', '-'), 'nt:unstructured');
        $newNode->addMixin($mixin);
        $session = $this->saveAndRenewSession();
        $savedNode = $session->getNode($newNode->getPath());
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
        $this->node->addMixin($mixin);
        $session = $this->saveAndRenewSession();
        $savedNode = $session->getNode($this->node->getPath());
        $resultTypes = array();
        foreach ($savedNode->getMixinNodeTypes() as $type) {
            $resultTypes[] = $type->getName();
        }
        //var_dump($resultTypes);die;
        $this->assertContains($mixin, $resultTypes, "Node mixins should contain $mixin");
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
