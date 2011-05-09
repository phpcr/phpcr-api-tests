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

    /**
     * Test we can assign any of the existing mixin types to a node.
     */
    public function testAddMixin()
    {
        $valid_mixins = array(
            "mix:created", "mix:etag", "mix:language", "mix:lastModified", "mix:lifecycle",
            "mix:lockable", "mix:mimeType", "mix:referenceable", "mix:shareable", 
            "mix:simpleVersionable", "mix:title", "mix:versionable", "rep:AccessControllable",
            "rep:Impersonatable", "rep:RetentionManageable", "rep:VersionReference");

        foreach($valid_mixins as $mixin) {
            $this->testNode->addMixin($mixin);
            $this->assertTrue($this->testNode->isNodeType($mixin));
        }
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
