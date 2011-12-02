<?php
namespace PHPCR\Tests\Writing;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Test setting node types on nodes.
 */
class NodeTypeAssignementTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = '10_Writing/nodetype')
    {
        parent::setupBeforeClass($fixtures);
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

    // TODO: a repository MAY also allow changing the primary node type.

    /**
     * the predefined mixin types that do not depend on optional features
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
     */
    public function testAddMixinTwice()
    {
        $this->assertFalse($this->node->isModified());
        $this->assertTrue($this->node->isNodeType('mix:referenceable'), 'error with the fixtures, the node should have this mixin type');
        $this->node->addMixin('mix:referenceable'); // this mixin is already in the fixtures
        $this->assertFalse($this->node->isModified());

        $this->markTestIncomplete('TODO: fix adding mixin when there is already one existing');
        // maybe similar to http://mail-archives.apache.org/mod_mbox/jackrabbit-users/201108.mbox/%3C1314168796503-3764635.post@n4.nabble.com%3E

        $this->node->addMixin('mix:mimeType');
        $this->assertTrue($this->node->isModified());
        $this->sharedFixture['session']->save();
        $this->assertFalse($this->node->isModified());
        $this->node->addMixin('mix:mimeType');
        $this->assertFalse($this->node->isModified());
    }

    /**
     * add a mixin type that extends another type and check if the node
     * is properly reported as implementing the base type too.
     */
    public function testAddMixinExtending()
    {
        if (!self::$staticSharedFixture['session']->getRepository()->getDescriptor('option.versioning.supported')) {
            $this->markTestSkipped('PHPCR repository doesn\'t support versioning');
        }

        $this->node->addMixin('mix:versionable');
        $this->assertTrue($this->node->isNodeType('mix:referenceable'));
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
