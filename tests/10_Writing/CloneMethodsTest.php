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

    /** @var WorkspaceInterface */
    protected static $destWs;

    static public function setupBeforeClass($fixtures = '10_Writing/copy')
    {
        parent::setupBeforeClass($fixtures);

        $destWorkspaceName = 'testClone' . time();
        $workspace = self::$staticSharedFixture['session']->getWorkspace();
        $workspace->createWorkspace($destWorkspaceName);

        $destSession = self::$loader->getRepository()->login(self::$loader->getCredentials(), $destWorkspaceName);
        self::$destWs = $destSession->getWorkspace();

        $rootNode = $destSession->getRootNode();
        $node = $rootNode->addNode('tests_write_manipulation_copy');
        $node->addNode('testWorkspaceCopy');
        $destSession->save();
    }

    protected function setUp()
    {
        $this->renewSession(); // get rid of cache from previous tests
        parent::setUp();

        if (self::$destWs->getSession()->nodeExists('/foo')) {
            self::$destWs->getSession()->removeItem('/foo');
        }

        $this->srcWs = $this->sharedFixture['session']->getWorkspace();
    }

    static public function tearDownAfterClass()
    {
        self::$destWs = null;
        parent::tearDownAfterClass();
    }

    public function testCloneFrom()
    {
        $srcNode = '/tests_write_manipulation_copy/testWorkspaceCopy/srcNode';
        $dstNode = '/tests_write_manipulation_copy/testWorkspaceCopy/dstNode';
        $srcWorkspaceName = $this->sharedFixture['session']->getWorkspace()->getName();

        self::$destWs->cloneFrom($srcWorkspaceName, $srcNode, $dstNode, true);

        $destSession = self::$destWs->getSession();
        $clonedNode = $destSession->getNode($dstNode);
        $this->assertInstanceOf('Jackalope\Node', $clonedNode);

        $srcProperties = $this->srcWs->getSession()->getNode($srcNode)->getProperties();
        foreach ($srcProperties as $srcName => $srcValue) {
            $this->assertTrue($clonedNode->hasProperty($srcName));
            $this->assertEquals($srcValue->getValue(), $clonedNode->getProperty($srcName)->getValue());
        }
    }

    /**
     * @expectedException   \PHPCR\NoSuchWorkspaceException
     */
    public function testCloneNoSuchWorkspace()
    {
        $srcNode = '/tests_write_manipulation_copy/testWorkspaceCopy/srcNode';
        $dstNode = $srcNode;
        $srcWorkspaceName = 'thisWorkspaceDoesNotExist';

        self::$destWs->cloneFrom($srcWorkspaceName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException   \PHPCR\RepositoryException
     */
    public function testCloneRelativePaths()
    {
        $srcNode = 'tests_write_manipulation_copy/testWorkspaceCopy/srcNode';
        $dstNode = $srcNode;
        $srcWorkspaceName = $this->sharedFixture['session']->getWorkspace()->getName();

        self::$destWs->cloneFrom($srcWorkspaceName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException   \PHPCR\RepositoryException
     */
    public function testCloneInvalidDstPath()
    {
        $srcNode = '/tests_write_manipulation_copy/testWorkspaceCopy/srcNode';
        $dstNode = '/InvalidDstPath/foo/bar[x]';
        $srcWorkspaceName = $this->sharedFixture['session']->getWorkspace()->getName();

        self::$destWs->cloneFrom($srcWorkspaceName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
     */
    public function testCloneProperty()
    {
        $srcNode = '/tests_write_manipulation_copy/testWorkspaceCopy/srcNode/jcr:uuid';
        $dstNode = '/tests_write_manipulation_copy/testWorkspaceCopy/dstNode/jcr:uuid';
        $srcWorkspaceName = $this->sharedFixture['session']->getWorkspace()->getName();

        self::$destWs->cloneFrom($srcWorkspaceName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
     */
    public function testCloneSrcNotFound()
    {
        $srcNode = '/there-is-no-node-here';
        $dstNode = $srcNode;
        $srcWorkspaceName = $this->sharedFixture['session']->getWorkspace()->getName();

        self::$destWs->cloneFrom($srcWorkspaceName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
     */
    public function testCloneDstParentNotFound()
    {
        $srcNode = '/tests_write_manipulation_copy/testWorkspaceCopy/srcNode';
        $dstNode = '/there-is-no-node-here/foo';
        $srcWorkspaceName = $this->sharedFixture['session']->getWorkspace()->getName();

        self::$destWs->cloneFrom($srcWorkspaceName, $srcNode, $dstNode, true);
    }
}
