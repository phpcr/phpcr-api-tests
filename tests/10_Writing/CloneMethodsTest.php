<?php
namespace PHPCR\Tests\Writing;

use PHPCR\WorkspaceInterface;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Covering jcr-283 spec $10.8
 */
class CloneMethodsTest extends \PHPCR\Test\BaseCase
{
    /** @var WorkspaceInterface */
    protected $srcWs;

    /** @var string */
    protected $srcWsName;

    /** @var WorkspaceInterface */
    protected static $destWs;

    /** @var string */
    protected static $destWsName;

    static public function setupBeforeClass($fixtures = '10_Writing/clone')
    {
        parent::setupBeforeClass($fixtures);

        self::$destWs = self::$staticSharedFixture['additionalSession']->getWorkspace();
        self::$destWsName = self::$destWs->getName();

        $destSession = self::$destWs->getSession();
        $rootNode = $destSession->getRootNode();
        $node = $rootNode->addNode('tests_write_manipulation_clone');
        $node->addNode('testWorkspaceClone');
        $destSession->save();
    }

    protected function setUp()
    {
        $this->renewSession(); // get rid of cache from previous tests
        parent::setUp();

        $this->srcWs = $this->sharedFixture['session']->getWorkspace();
        $this->srcWsName = $this->sharedFixture['session']->getWorkspace()->getName();
    }

    static public function tearDownAfterClass()
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
        $dstChildNode = $srcNode . '/cloneChild';
        $destSession = self::$destWs->getSession();

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('Jackalope\Node', $clonedNode);
        $this->assertCount(4, $clonedNode->getProperties());
        $this->checkNodeProperty($clonedNode, 'jcr:uuid', '842e61c0-09ab-42a9-87c0-308ccc90e6f6');
        $this->checkNodeProperty($clonedNode, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($clonedNode, 'jcr:mixinTypes', array('mix:referenceable'));
        $this->checkNodeProperty($clonedNode, 'foo', 'bar_1');

        $this->assertTrue($destSession->nodeExists($dstChildNode));
        $cloneChild = $destSession->getNode($dstChildNode);
        $this->assertInstanceOf('Jackalope\Node', $cloneChild);
        $this->assertCount(4, $cloneChild->getProperties());
        $this->checkNodeProperty($cloneChild, 'jcr:uuid', '9da62173-d674-4413-87a4-8f538e033021');
        $this->checkNodeProperty($cloneChild, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($cloneChild, 'jcr:mixinTypes', array('mix:referenceable'));
        $this->checkNodeProperty($cloneChild, 'fooChild', 'barChild');
    }

    /**
     * Clone a referenceable node, then clone again with 'remove existing' feature.
     */
    public function testCloneReferenceableRemoveExisting()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceableRemoveExisting';
        $dstNode = $srcNode;
        $destSession = self::$destWs->getSession();

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('Jackalope\Node', $clonedNode);
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
        $this->assertInstanceOf('Jackalope\Node', $clonedReplacedNode);
        $this->assertCount(5, $clonedReplacedNode->getProperties());
        $this->checkNodeProperty($clonedReplacedNode, 'jcr:uuid', '091d157f-dfaf-42eb-aedd-88183ff8fa3d');
        $this->checkNodeProperty($clonedReplacedNode, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($clonedReplacedNode, 'jcr:mixinTypes', array('mix:referenceable'));
        $this->checkNodeProperty($clonedReplacedNode, 'foo', 'bar-updated');
        $this->checkNodeProperty($clonedReplacedNode, 'newProperty', 'hello');
    }

    /**
     * @expectedException   \PHPCR\ItemExistsException
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
        $this->assertInstanceOf('Jackalope\Node', $clonedNode);
        $this->assertCount(3, $clonedNode->getProperties());

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);
    }

    /**
     * @expectedException   \PHPCR\ItemExistsException
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
        $this->assertInstanceOf('Jackalope\Node', $clonedNode);
        $this->assertCount(3, $clonedNode->getProperties());

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $secondDstNode, false);
    }

    /**
     * @expectedException   \PHPCR\NoSuchWorkspaceException
     */
    public function testCloneNoSuchWorkspace()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceable';
        $dstNode = $srcNode;

        self::$destWs->cloneFrom('thisWorkspaceDoesNotExist', $srcNode, $dstNode, true);
    }

    /**
     * @expectedException   \PHPCR\RepositoryException
     */
    public function testCloneRelativePaths()
    {
        $srcNode = 'tests_write_manipulation_clone/testWorkspaceClone/referenceable';
        $dstNode = $srcNode;

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException   \PHPCR\RepositoryException
     */
    public function testCloneInvalidDstPath()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceable';
        $dstNode = '/InvalidDstPath/foo/bar[x]';

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
     */
    public function testCloneProperty()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceable/jcr:uuid';
        $dstNode = $srcNode;

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
     */
    public function testCloneSrcNotFound()
    {
        $srcNode = '/there-is-no-node-here';
        $dstNode = $srcNode;

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
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
        $this->assertInstanceOf('Jackalope\Node', $clonedNode);
        $this->assertCount(2, $clonedNode->getProperties());
        $this->checkNodeProperty($clonedNode, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($clonedNode, 'foo', 'bar_3');
    }

    /**
     * Clone a non-referenceable node, then clone again with 'remove existing' feature.
     */
    public function testCloneRemoveExistingNonReferenceable()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/nonReferenceableRemoveExisting';
        $dstNode = $srcNode;
        $destSession = self::$destWs->getSession();

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('Jackalope\Node', $clonedNode);
        $this->assertCount(2, $clonedNode->getProperties());
        $this->checkNodeProperty($clonedNode, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($clonedNode, 'foo', 'bar_4');

        // Update the source node after cloning it
        $node = $this->srcWs->getSession()->getNode($srcNode);
        $node->setProperty('foo', 'bar-updated');
        $node->setProperty('newProperty', 'hello');
        $this->srcWs->getSession()->save();

        // Clone the updated source node
        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);

        $this->renewDestinationSession();

        // Check the first cloned node again; it should not have changed
        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('Jackalope\Node', $clonedNode);
        $this->assertCount(2, $clonedNode->getProperties());
        $this->checkNodeProperty($clonedNode, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($clonedNode, 'foo', 'bar_4');

        // Second cloned node created with [2] appended to name
        $replacedDstNode = $srcNode . '[2]';
        $clonedReplacedNode = self::$destWs->getSession()->getNode($replacedDstNode);
        $this->assertInstanceOf('Jackalope\Node', $clonedReplacedNode);
        $this->assertCount(3, $clonedReplacedNode->getProperties());
        $this->checkNodeProperty($clonedReplacedNode, 'jcr:primaryType', 'nt:unstructured');
        $this->checkNodeProperty($clonedReplacedNode, 'foo', 'bar-updated');
        $this->checkNodeProperty($clonedReplacedNode, 'newProperty', 'hello');
    }

    /**
     * @expectedException   \PHPCR\ItemExistsException
     */
    public function testCloneNonReferenceableNoRemoveExisting()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/nonReferenceableNoRemoveExisting';
        $dstNode = $srcNode;
        $destSession = self::$destWs->getSession();

        try {
            self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }

        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('Jackalope\Node', $clonedNode);
        $this->assertCount(1, $clonedNode->getProperties());

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);
    }

    private function renewDestinationSession()
    {
        $destSession = self::$loader->getRepository()->login(self::$loader->getCredentials(), self::$destWsName);
        self::$destWs = $destSession->getWorkspace();
    }

    private function checkNodeProperty($node, $property, $value)
    {
        $this->assertTrue($node->hasProperty($property));
        $this->assertEquals($value, $node->getProperty($property)->getValue());
    }
}
