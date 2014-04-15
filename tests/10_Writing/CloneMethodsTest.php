<?php
namespace PHPCR\Tests\Writing;

use PHPCR\WorkspaceInterface;
use PHPCR\Test\BaseCase;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Covering jcr-283 spec $10.8
 */
class CloneMethodsTest extends BaseCase
{
    /** @var WorkspaceInterface */
    protected $srcWs;

    /** @var string */
    protected $srcWsName;

    /** @var WorkspaceInterface */
    protected static $destWs;

    /** @var string */
    protected static $destWsName;

    public static function setupBeforeClass($fixtures = '10_Writing/clone')
    {
        parent::setupBeforeClass($fixtures);

        self::$staticSharedFixture['ie']->import('general/additionalWorkspace', 'additionalWorkspace');

        self::$destWs = self::$staticSharedFixture['additionalSession']->getWorkspace();
        self::$destWsName = self::$destWs->getName();

        $destSession = self::$destWs->getSession();
        $rootNode = $destSession->getRootNode();
        $node = $rootNode->addNode('tests_write_manipulation_clone');
        $node->addNode('testWorkspaceClone');
        $node->addNode('testWorkspaceCorrespondingNode');
        $node->addNode('testWorkspaceUpdateNode');
        $destSession->save();
    }

    protected function setUp()
    {
        $this->renewSession(); // get rid of cache from previous tests
        parent::setUp();

        $this->srcWs = $this->session->getWorkspace();
        $this->srcWsName = $this->session->getWorkspace()->getName();
    }

    public static function tearDownAfterClass()
    {
        self::$destWs = null;
        parent::tearDownAfterClass();
    }

    /**
     * Main test for cloning a referenceable node and its child, making sure all properties copied over.
     */
    public function testCloneReferenceableWithChild()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceable';
        $dstNode = $srcNode;
        $dstChildNode = $dstNode . '/cloneChild';
        $destSession = self::$destWs->getSession();

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $clonedNode);
        $this->assertCount(4, $clonedNode->getProperties());
        $this->checkNodeProperty($clonedNode, 'jcr:uuid', '842e61c0-09ab-42a9-87c0-308ccc90e6f6');
        $this->checkNodeProperty($clonedNode, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($clonedNode, 'jcr:mixinTypes', array('mix:referenceable'));
        $this->checkNodeProperty($clonedNode, 'foo', 'bar_1');

        $this->assertTrue($destSession->nodeExists($dstChildNode));
        $cloneChild = $destSession->getNode($dstChildNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $cloneChild);
        $this->assertCount(4, $cloneChild->getProperties());
        $this->checkNodeProperty($cloneChild, 'jcr:uuid', '9da62173-d674-4413-87a4-8f538e033021');
        $this->checkNodeProperty($cloneChild, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($cloneChild, 'jcr:mixinTypes', array('mix:referenceable'));
        $this->checkNodeProperty($cloneChild, 'fooChild', 'barChild');
    }

    /**
     * Clone a referenceable node, then clone again with removeExisting = true
     * This should overwrite the existing, corresponding node (same UUID)
     */
    public function testCloneReferenceableRemoveExisting()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceableRemoveExisting';
        $dstNode = $srcNode;
        $destSession = self::$destWs->getSession();

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $clonedNode);
        $this->assertCount(4, $clonedNode->getProperties());
        $this->checkNodeProperty($clonedNode, 'jcr:uuid', '091d157f-dfaf-42eb-aedd-88183ff8fa3d');
        $this->checkNodeProperty($clonedNode, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($clonedNode, 'jcr:mixinTypes', array('mix:referenceable'));
        $this->checkNodeProperty($clonedNode, 'foo', 'bar_2');

        // Update the source node after cloning it
        $node = $this->srcWs->getSession()->getNode($srcNode);
        $node->setProperty('foo', 'bar-updated');
        $node->setProperty('newProperty', 'hello');
        $this->srcWs->getSession()->save();

        // Clone the updated source node
        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);

        $this->renewDestinationSession();

        $clonedReplacedNode = self::$destWs->getSession()->getNode($dstNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $clonedReplacedNode);
        $this->assertCount(5, $clonedReplacedNode->getProperties());
        $this->checkNodeProperty($clonedReplacedNode, 'jcr:uuid', '091d157f-dfaf-42eb-aedd-88183ff8fa3d');
        $this->checkNodeProperty($clonedReplacedNode, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($clonedReplacedNode, 'jcr:mixinTypes', array('mix:referenceable'));
        $this->checkNodeProperty($clonedReplacedNode, 'foo', 'bar-updated');
        $this->checkNodeProperty($clonedReplacedNode, 'newProperty', 'hello');
    }

    /**
     * Clone a referenceable node, then clone again with removeExisting = false
     * This should cause an exception, even with a corresponding node (same UUID)
     *
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testCloneReferenceableNoRemoveExisting()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceableNoRemoveExisting_1';
        $dstNode = $srcNode;
        $destSession = self::$destWs->getSession();

        try {
            self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $clonedNode);
        $this->assertCount(3, $clonedNode->getProperties());

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);
    }

    /**
     * Clone a referenceable node, then clone again with removeExisting = false.
     * Even though the second clone is to a new location, because a corresponding node (same UUID)
     * already exists in the destination workspace, an exception should still be thrown.
     *
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testCloneNoRemoveExistingNewLocation()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceableNoRemoveExisting_2';
        $dstNode = $srcNode;
        $secondDstNode = '/tests_write_manipulation_clone/testWorkspaceClone/thisShouldStillConflict';;
        $destSession = self::$destWs->getSession();

        try {
            self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $clonedNode);
        $this->assertCount(3, $clonedNode->getProperties());

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $secondDstNode, false);
    }

    /**
     * When removing the existing corresponding target, this must work even
     * when the target is at the same path.
     */
    public function testExistingCorrespondingNodeRemoveExisting()
    {
        $this->skipIfSameNameSiblingsSupported();

        $srcNode = '/tests_write_manipulation_clone/testExistingCorrespondingNodeRemoveExisting/sourceRemoveExistingCorresponding';
        $dstNode = '/tests_additional_workspace/testWorkspaceCloneNonCorresponding/destRemoveExisting';

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);

        $this->renewDestinationSession();

        $clonedReplacedNode = self::$destWs->getSession()->getNode($dstNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $clonedReplacedNode);
        $this->assertCount(3, $clonedReplacedNode->getProperties());
        $this->checkNodeProperty($clonedReplacedNode, 'jcr:uuid', 'f8019868-3533-4519-a077-9c8601950627');
        $this->checkNodeProperty($clonedReplacedNode, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($clonedReplacedNode, 'jcr:mixinTypes', array('mix:referenceable'));
    }

    /**
     * Check that we don't inadvertently create same name siblings (SNS) with removeExisting = true.
     * This can happen when cloning from one workspace to another, when a node already exists at the
     * destination but is not a corresponding node (the nodes have different UUIDs)
     *
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testExistingNonCorrespondingNodeRemoveExisting()
    {
        $this->skipIfSameNameSiblingsSupported();

        $srcNode = '/tests_write_manipulation_clone/testWorkspaceCloneNonCorresponding/sourceRemoveExisting';
        $dstNode = '/tests_additional_workspace/testWorkspaceCloneNonCorresponding/destRemoveExisting';

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);
    }

    /**
     * Check that we don't inadvertently create same name siblings (SNS) with removeExisting = false.
     * This can happen when cloning from one workspace to another, when a node already exists at the
     * destination but is not a corresponding node (the nodes have different UUIDs)
     *
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testExistingNonCorrespondingNodeNoRemoveExisting()
    {
        $this->skipIfSameNameSiblingsSupported();

        $srcNode = '/tests_write_manipulation_clone/testWorkspaceCloneNonCorresponding/sourceNoRemoveExisting';
        $dstNode = '/tests_additional_workspace/testWorkspaceCloneNonCorresponding/destNoRemoveExisting';

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);
    }

    /**
     * Test when source node is non-referenceable but a referenceable node exists at destination path
     *
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testReferenceableDestNodeWithNonReferenceableSourceNode()
    {
        $this->skipIfSameNameSiblingsSupported();

        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/nonReferenceable';
        $dstNode = '/tests_additional_workspace/testWorkspaceCloneReferenceable/destExistingNode';

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException \PHPCR\NoSuchWorkspaceException
     */
    public function testCloneNoSuchWorkspace()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceable';
        $dstNode = $srcNode;

        self::$destWs->cloneFrom('thisWorkspaceDoesNotExist', $srcNode, $dstNode, true);
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testCloneRelativePaths()
    {
        $srcNode = 'tests_write_manipulation_clone/testWorkspaceClone/referenceable';
        $dstNode = $srcNode;

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testCloneInvalidDstPath()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceable';
        $dstNode = '/InvalidDstPath/foo/bar[x]';

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testCloneProperty()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceable/jcr:uuid';
        $dstNode = $srcNode;

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testCloneSrcNotFound()
    {
        $srcNode = '/there-is-no-node-here';
        $dstNode = $srcNode;

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testCloneDstParentNotFound()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceable';
        $dstNode = '/there-is-no-node-here/foo';

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);
    }

    /**
     * Main test for cloning a non-referenceable node
     */
    public function testCloneNonReferenceable()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/nonReferenceable';
        $dstNode = $srcNode;
        $destSession = self::$destWs->getSession();

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $clonedNode);
        $this->assertCount(2, $clonedNode->getProperties());
        $this->checkNodeProperty($clonedNode, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($clonedNode, 'foo', 'bar_3');
    }

    /**
     * Clone a non-referenceable node, then clone again with removeExisting = true
     *
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testCloneRemoveExistingNonReferenceable()
    {
        $this->skipIfSameNameSiblingsSupported();

        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/nonReferenceableRemoveExisting';
        $dstNode = $srcNode;
        $destSession = self::$destWs->getSession();

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $clonedNode);
        $this->assertCount(2, $clonedNode->getProperties());
        $this->checkNodeProperty($clonedNode, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($clonedNode, 'foo', 'bar_4');

        // Clone the updated source node
        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testCloneNonReferenceableNoRemoveExisting()
    {
        $this->skipIfSameNameSiblingsSupported();

        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/nonReferenceableNoRemoveExisting';
        $dstNode = $srcNode;
        $destSession = self::$destWs->getSession();

        try {
            self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $clonedNode);
        $this->assertCount(1, $clonedNode->getProperties());

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);
    }

    public function testGetCorrespondingNode()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceCorrespondingNode/sourceNode';
        $dstNode = '/tests_write_manipulation_clone/testWorkspaceCorrespondingNode/destNode';;
        $destSession = self::$destWs->getSession();

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);

        $node = $this->srcWs->getSession()->getNode($srcNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->checkNodeProperty($node, 'jcr:uuid', 'a64bfa45-d5e1-4bf0-a739-1890da40579d');

        $this->assertEquals($dstNode, $node->getCorrespondingNodePath(self::$destWsName));

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $clonedNode);
        $this->checkNodeProperty($clonedNode, 'jcr:uuid', 'a64bfa45-d5e1-4bf0-a739-1890da40579d');

        $this->assertEquals($srcNode, $clonedNode->getCorrespondingNodePath($this->srcWsName));
    }

    /**
     * @expectedException \PHPCR\NoSuchWorkspaceException
     */
    public function testGetCorrespondingNodeNoSuchWorkspace()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceCorrespondingNode/nodeThatWillNotBeCloned';

        $node = $this->srcWs->getSession()->getNode($srcNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->checkNodeProperty($node, 'jcr:uuid', 'e7c14901-aec8-4e9b-8e76-704197d24794');

        $node->getCorrespondingNodePath('thisWorkspaceDoesNotExist');
    }

    /**
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetCorrespondingNodeItemNotFound()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceCorrespondingNode/nodeThatWillNotBeCloned';

        $node = $this->srcWs->getSession()->getNode($srcNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->checkNodeProperty($node, 'jcr:uuid', 'e7c14901-aec8-4e9b-8e76-704197d24794');

        $node->getCorrespondingNodePath(self::$destWsName);
    }

    /**
     * Main test for cloning and then updating a node and its children.
     * Using two levels of children to make sure copy works recursively (and affected nodes not cached)
     */
    public function testUpdateNodeWithChildren()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceUpdateNode/sourceNode';
        $dstNode = '/tests_write_manipulation_clone/testWorkspaceUpdateNode/destNode';;
        $srcChildNode = $srcNode . '/cloneChild';
        $dstChildNode = $dstNode . '/cloneChild';
        $srcChildOfChildNode = $srcChildNode . '/childOfChild';
        $dstChildOfChildNode = $dstChildNode . '/childOfChild';
        $destSession = self::$destWs->getSession();
        $sourceSession = $this->srcWs->getSession();

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);

        $node = $sourceSession->getNode($srcNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertCount(4, $node->getProperties());
        $this->checkNodeProperty($node, 'jcr:uuid', 'c8996418-3fd9-407c-bfe6-faea6dcfbb40');
        $this->checkNodeProperty($node, 'foo', 'bar_5');
        $node->setProperty('foo', 'foo-updated');
        $node->setProperty('newProperty', 'hello');

        $childNode = $sourceSession->getNode($srcChildNode);
        $childNode->setProperty('fooChild', 'barChild-updated');
        $sourceSession->save();

        $childOfChildNode = $sourceSession->getNode($srcChildOfChildNode);
        $childOfChildNode->setProperty('fooChildOfChild', 'barChildOfChild-updated');
        $sourceSession->save();

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $clonedNode);
        $this->assertCount(4, $clonedNode->getProperties());
        $this->checkNodeProperty($clonedNode, 'jcr:uuid', 'c8996418-3fd9-407c-bfe6-faea6dcfbb40');

        $cloneChild = $destSession->getNode($dstChildNode);
        $this->assertCount(4, $clonedNode->getProperties());
        $this->checkNodeProperty($cloneChild, 'jcr:uuid', 'e7683690-0465-4aa8-87c6-f37a67d08469');

        $cloneChildOfChild = $destSession->getNode($dstChildOfChildNode);
        $this->assertCount(4, $cloneChildOfChild->getProperties());
        $this->checkNodeProperty($cloneChildOfChild, 'jcr:uuid', '7547cb47-3c13-4e23-b6d1-29685a434c88');

        $clonedNode->update($this->srcWsName);

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('PHPCR\NodeInterface', $clonedNode);
        $this->assertCount(5, $clonedNode->getProperties());
        $this->checkNodeProperty($clonedNode, 'jcr:uuid', 'c8996418-3fd9-407c-bfe6-faea6dcfbb40');
        $this->checkNodeProperty($clonedNode, 'foo', 'foo-updated');
        $this->checkNodeProperty($clonedNode, 'newProperty', 'hello');

        $cloneChild = $destSession->getNode($dstChildNode);
        $this->assertCount(4, $cloneChild->getProperties());
        $this->checkNodeProperty($cloneChild, 'jcr:uuid', 'e7683690-0465-4aa8-87c6-f37a67d08469');
        $this->checkNodeProperty($cloneChild, 'fooChild', 'barChild-updated');

        $cloneChildOfChild = $destSession->getNode($dstChildOfChildNode);
        $this->assertCount(4, $cloneChildOfChild->getProperties());
        $this->checkNodeProperty($cloneChildOfChild, 'jcr:uuid', '7547cb47-3c13-4e23-b6d1-29685a434c88');
        $this->checkNodeProperty($cloneChildOfChild, 'fooChildOfChild', 'barChildOfChild-updated');
    }

    /**
     * @expectedException \PHPCR\NoSuchWorkspaceException
     */
    public function testUpdateNoSuchWorkspace()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceUpdateNode/updateNoSuchWorkspace';
        $dstNode = $srcNode;
        $destSession = self::$destWs->getSession();

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);

        $clonedNode = $destSession->getNode($dstNode);
        $this->checkNodeProperty($clonedNode, 'jcr:uuid', '8cd0ab49-1e3d-4b92-bfe4-48bf3e5efdb3');

        $clonedNode->update('non-existent-workspace');
    }

    /**
     * Test that update has no effect if the source node not found
     * (from JCR spec 10.8.3: "If this node does not have a corresponding node in srcWorkspace, then the method has no effect.")
     */
    public function testUpdateSrcNotFound()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceUpdateNode/updateSrcNotFound';
        $dstNode = $srcNode;
        $srcSession = $this->srcWs->getSession();
        $destSession = self::$destWs->getSession();

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertCount(4, $clonedNode->getProperties());
        $this->checkNodeProperty($clonedNode, 'jcr:uuid', '1d392bcb-3e49-4f0e-b0af-7c30ab838122');
        $this->checkNodeProperty($clonedNode, 'foo', 'bar_6');

        // Update but then remove source node
        $node = $srcSession->getNode($srcNode);
        $node->setProperty('foo', 'foo-updated');
        $srcSession->removeItem($srcNode);
        $srcSession->save();

        try {
            $clonedNode->update($this->srcWsName);
        } catch (\Exception $exception) {
            $this->fail("'update' method should not raise an error when source not found, got error: " . $exception->getMessage());
        }

        $destSession->refresh(false);

        // Cloned node should not get any updates that were made to the source node before it was removed
        $clonedNode = $destSession->getNode($dstNode);
        $this->assertCount(4, $clonedNode->getProperties());
        $this->checkNodeProperty($clonedNode, 'jcr:uuid', '1d392bcb-3e49-4f0e-b0af-7c30ab838122');
        $this->checkNodeProperty($clonedNode, 'foo', 'bar_6');
    }

    private function renewDestinationSession()
    {
        $destSession = self::$loader->getRepository()->login(self::$loader->getCredentials(), self::$destWsName);
        self::$destWs = $destSession->getWorkspace();
    }

    private function skipIfSameNameSiblingsSupported()
    {
        if ($this->session->getRepository()->getDescriptor('node.type.management.same.name.siblings.supported')) {
            $this->markTestSkipped('Test does not yet cover repositories that support same name siblings.');
        }
    }
}
