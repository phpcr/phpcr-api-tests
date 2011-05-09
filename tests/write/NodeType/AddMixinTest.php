<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * Test adding mixin to nodes.
 */
class Write_NodeType_AddMixinTest extends jackalope_baseCase
{
    protected $testNode;

    protected $testNodeName = '___test__';

    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
    }

    public function setUp()
    {
        parent::setUp();
        $this->renewSession();

        $session = $this->sharedFixture['session'];
        $path = "/" . $this->testNodeName;
        if ($session->itemExists($path)) {
            $this->testNode = $session->getNode($path);
        } else {
            $this->testNode = $session->getRootNode()->addNode($this->testNodeName);
            $session->save();
        }
    }

    public function tearDown()
    {
        $this->saveAndRenewSession();
    }

    public static $mixins = array(
            "mix:created", "mix:etag", "mix:language", "mix:lastModified", "mix:lifecycle",
            "mix:lockable", "mix:mimeType", "mix:referenceable", "mix:shareable",
            "mix:simpleVersionable", "mix:title", "mix:versionable", "rep:AccessControllable",
            "rep:Impersonatable", "rep:RetentionManageable", "rep:VersionReference");

    public static function mixinTypes() {
        $ret = array();
        foreach(self::$mixins as $mixin) {
            $ret[] = array($mixin);
        }
        return $ret;
    }

    /**
     * @dataProvider mixinTypes
     */
    public function testAddMixinOnNewNode($mixin)
    {
        $newNode = $this->testNode->addNode('parent-'.strtr($mixin, ':', '-'), 'nt:unstructured');
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
        $this->testNode->addMixin($mixin);
        $session = $this->saveAndRenewSession();
        $savedNode = $session->getNode($this->testNode->getPath());
        $resultTypes = array();
        foreach ($savedNode->getMixinNodeTypes() as $type) {
            $resultTypes[] = $type->getName();
        }
        $this->assertContains($mixin, $resultTypes, "Node mixins should contain $mixin");
    }

    /**
     * Test that assigning an unexisting mixin type to a node will fail
     * @expectedException \PHPCR\NodeType\NoSuchNodeTypeException
     * @expectedExceptionMessage The mixin type 'mix:unexisting' does not exist
     */
    public function testAddMixinUnexisting()
    {
        $this->testNode->addMixin('mix:unexisting');
        $this->saveAndRenewSession();
    }
}
