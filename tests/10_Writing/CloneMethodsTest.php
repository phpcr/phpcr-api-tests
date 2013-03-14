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

    public function testCloneReferenceable()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceable';
        $dstNode = $srcNode;

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);

        $destSession = self::$destWs->getSession();
        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('Jackalope\Node', $clonedNode);

        $this->assertCount(4, $clonedNode->getProperties());
        $this->assertTrue($clonedNode->hasProperty('jcr:uuid'));
        $this->assertTrue($clonedNode->hasProperty('jcr:primaryType'));
        $this->assertTrue($clonedNode->hasProperty('jcr:mixinTypes'));
        $this->assertTrue($clonedNode->hasProperty('foo'));

        $srcProperties = $this->srcWs->getSession()->getNode($srcNode)->getProperties();
        foreach ($srcProperties as $srcName => $srcValue) {
            $this->assertEquals($srcValue->getValue(), $clonedNode->getProperty($srcName)->getValue());
        }
    }

    public function testCloneRemoveExistingReferenceable()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceableRemoveExisting';
        $dstNode = $srcNode;

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);

        $destSession = self::$destWs->getSession();
        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('Jackalope\Node', $clonedNode);
        $this->assertCount(4, $clonedNode->getProperties());
        $this->assertEquals('bar', $clonedNode->getProperty('foo')->getValue());

        $node = $this->srcWs->getSession()->getNode($srcNode);
        $node->setProperty('foo', 'bar-updated');
        $node->setProperty('newProperty', 'hello');
        $this->srcWs->getSession()->save();

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, true);

        $this->renewDestinationSession();

        $clonedReplacedNode = self::$destWs->getSession()->getNode($dstNode);
        $this->assertInstanceOf('Jackalope\Node', $clonedReplacedNode);
        $this->assertCount(5, $clonedReplacedNode->getProperties());
        $this->assertTrue($clonedReplacedNode->hasProperty('foo'));
        $this->assertEquals('bar-updated', $clonedReplacedNode->getProperty('foo')->getValue());
        $this->assertTrue($clonedReplacedNode->hasProperty('newProperty'));
        $this->assertEquals('hello', $clonedReplacedNode->getProperty('newProperty')->getValue());
    }

    /**
     * @expectedException   \PHPCR\ItemExistsException
     */
    public function testCloneNoRemoveExisting()
    {
        $srcNode = '/tests_write_manipulation_clone/testWorkspaceClone/referenceableNoRemoveExisting';
        $dstNode = $srcNode;

        try {
            self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }

        self::$destWs->cloneFrom($this->srcWsName, $srcNode, $dstNode, false);
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

    private function renewDestinationSession()
    {
        $destSession = self::$loader->getRepository()->login(self::$loader->getCredentials(), self::$destWsName);
        self::$destWs = $destSession->getWorkspace();
    }
}
