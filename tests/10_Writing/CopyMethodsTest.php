<?php
namespace PHPCR\Tests\Writing;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

/**
 * Covering jcr-283 spec $10.7
 */
class CopyMethodsTest extends \PHPCR\Test\BaseCase
{
    protected $ws;


    static public function setupBeforeClass($fixtures = '10_Writing/copy')
    {
        parent::setupBeforeClass($fixtures);
    }

    protected function setUp()
    {
        $this->renewSession(); // get rid of cache from previous tests
        parent::setUp();

        $this->ws = $this->sharedFixture['session']->getWorkspace();
    }

    public function testWorkspaceCopy()
    {
        $src = '/tests_write_manipulation_copy/testWorkspaceCopy/srcNode';
        $dst = '/tests_write_manipulation_copy/testWorkspaceCopy/dstNode/srcNode';

        $this->ws->copy($src, $dst);

        // not really required as we haven't read the nodes but...
        $this->renewSession();

        $this->assertTrue($this->sharedFixture['session']->nodeExists($dst));

        $snode = $this->sharedFixture['session']->getNode($src);
        $dnode = $this->sharedFixture['session']->getNode($dst);
        $this->assertNotEquals($snode->getIdentifier(), $dnode->getIdentifier(), 'UUID was not changed');

        $this->assertTrue($this->sharedFixture['session']->nodeExists($dst.'/srcFile/jcr:content'), 'Did not copy the whole subgraph');

    }

    /**
     * @expectedException   \PHPCR\NoSuchWorkspaceException
     */
    public function testCopyNoSuchWorkspace()
    {
        $src = '/somewhere/foo';
        $dst = '/here/foo';
        $this->ws->copy($src, $dst, 'inexistentworkspace');
    }

    public function testWorkspaceCopyBackend()
    {
        $this->markTestIncomplete('TODO: just do');
    }

    /**
     * @expectedException   \PHPCR\RepositoryException
     */
    public function testCopyRelativePaths()
    {
        $this->ws->copy('foo/moo', 'bar/mar');
    }

    /**
     * @expectedException   \PHPCR\RepositoryException
     */
    public function testCopyInvalidDstPath()
    {
        $src = '/tests_write_manipulation_copy/testCopyInvalidDstPath/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyInvalidDstPath/dstNode/srcNode[3]';
        $this->ws->copy($src, $dst);
    }

    /**
     * @expectedException   \PHPCR\RepositoryException
     */
    public function testCopyProperty()
    {
        $src = '/tests_write_manipulation_copy/testCopyProperty/srcFile/jcr:content/someProperty';
        $dst = '/tests_write_manipulation_copy/testCopyProperty/dstFile/jcr:content/someProperty';
        $this->ws->copy($src, $dst);
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
     */
    public function testCopySrcNotFound()
    {
        $src = '/tests_write_manipulation_copy/testCopySrcNotFound/notFound';
        $dst = '/tests_write_manipulation_copy/testCopySrcNotFound/dstNode/notFound';
        $this->ws->copy($src, $dst);
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
     */
    public function testCopyDstParentNotFound()
    {
        $src = '/tests_write_manipulation_copy/testCopyDstParentNotFound/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyDstParentNotFound/dstNode/notFound/srcNode';
        $this->ws->copy($src, $dst);
    }

    /**
     * Verifies that there is no update-on-copy if the target node already exists
     *
     * @expectedException   \PHPCR\ItemExistsException
     */
    public function testCopyNoUpdateOnCopy()
    {
        $src = '/tests_write_manipulation_copy/testCopyNoUpdateOnCopy/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyNoUpdateOnCopy/dstNode/srcNode';

        $this->ws->copy($src, $dst);
    }

    public function testCopyUpdateOnCopy()
    {
        $sess = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_copy/testCopyUpdateOnCopy/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyUpdateOnCopy/dstNode/srcNode';
        $this->ws->copy($src, $dst);

        // make sure child node was copied
        $this->assertTrue($sess->nodeExists($dst.'/srcFile'));
        // make sure things were updated
        $this->assertEquals('123', $sess->getProperty($dst.'/updateFile/jcr:data')->getValue());
    }

}



