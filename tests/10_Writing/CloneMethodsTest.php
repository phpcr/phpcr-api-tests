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
    protected $ws;

    /** @var WorkspaceInterface */
    protected static $destWs;

    static public function setupBeforeClass($fixtures = '10_Writing/copy')
    {
        parent::setupBeforeClass($fixtures);

        $destWorkspaceName = 'testClone' . time();
        $workspace = self::$staticSharedFixture['session']->getWorkspace();
        $workspace->createWorkspace($destWorkspaceName);
        $session = $workspace->getSession();

        $rootNode = $session->getRootNode();
        if ($rootNode->hasNode('foo')) {
            $node = $rootNode->getNode('foo');
        } else {
            $node = $rootNode->addNode('foo');
        }
        $node->setProperty('test', 'Hello!');
        $session->save();

        $destSession = self::$loader->getRepository()->login(self::$loader->getCredentials(), $destWorkspaceName);
        self::$destWs = $destSession->getWorkspace();
    }

    protected function setUp()
    {
        $this->renewSession(); // get rid of cache from previous tests
        parent::setUp();

        if (self::$destWs->getSession()->nodeExists('/foo')) {
            self::$destWs->getSession()->removeItem('/foo');
        }

        $this->ws = $this->sharedFixture['session']->getWorkspace();
    }

    static public function tearDownAfterClass()
    {
        self::$destWs = null;
        parent::tearDownAfterClass();
    }

    public function testCloneFrom()
    {
        $srcNode = '/foo';
        $dstNode = $srcNode;
        $srcWorkspaceName = $this->sharedFixture['session']->getWorkspace()->getName();

        $this->assertFalse(self::$destWs->getSession()->nodeExists('/foo'));
        self::$destWs->cloneFrom($srcWorkspaceName, $srcNode, $dstNode, true);

        $this->renewSession();
        $this->assertTrue(self::$destWs->getSession()->nodeExists('/foo'));

        $destSession = self::$destWs->getSession();
        $clonedNode = $destSession->getNode('/foo');
        $this->assertTrue($clonedNode->hasProperty('test'));
        $this->assertEquals('Hello!', $clonedNode->getProperty('test')->getValue());
        $this->assertTrue($clonedNode->hasProperty('jcr:primaryType'));
        $this->assertEquals('nt:unstructured', $clonedNode->getProperty('jcr:primaryType')->getValue());
    }

    /**
     * @expectedException   \PHPCR\NoSuchWorkspaceException
     */
    public function testCloneNoSuchWorkspace()
    {
        $srcNode = '/foo';
        $dstNode = $srcNode;
        $srcWorkspaceName = 'thisWorkspaceDoesNotExist';

        self::$destWs->cloneFrom($srcWorkspaceName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException   \PHPCR\RepositoryException
     */
    public function testCloneRelativePaths()
    {
        $srcNode = 'foo';
        $dstNode = $srcNode;
        $srcWorkspaceName = $this->sharedFixture['session']->getWorkspace()->getName();

        self::$destWs->cloneFrom($srcWorkspaceName, $srcNode, $dstNode, true);
    }
    /**
     * @expectedException   \PHPCR\RepositoryException
     */
    public function testCloneInvalidDstPath()
    {
        $srcNode = '/foo';
        $dstNode = '/InvalidDstPath/foo/bar[x]';
        $srcWorkspaceName = $this->sharedFixture['session']->getWorkspace()->getName();

        self::$destWs->cloneFrom($srcWorkspaceName, $srcNode, $dstNode, true);
    }

    /**
     * @expectedException   \PHPCR\RepositoryException
     */
    public function testCloneProperty()
    {
        $this->markTestIncomplete("This may seem to 'work' but it's actually just a PathNotFoundException; we need to create these properties in the fixtures");

        $srcNode = '/foo/jcr:content/someProperty';
        $dstNode = '/foo/jcr:content/someProperty';
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
        $srcNode = '/foo';
        $dstNode = '/there-is-no-node-here/foo';
        $srcWorkspaceName = $this->sharedFixture['session']->getWorkspace()->getName();

        self::$destWs->cloneFrom($srcWorkspaceName, $srcNode, $dstNode, true);
    }
}
