<?php
namespace PHPCR\Tests\Writing;

use PHPCR\WorkspaceInterface;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Covering jcr-283 spec $10.7
 */
class CopyMethodsTest extends \PHPCR\Test\BaseCase
{
    /** @var WorkspaceInterface */
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

        $snode = $this->sharedFixture['session']->getNode($src);
        $dnode = $this->sharedFixture['session']->getNode($dst);
        $this->assertNotEquals($snode->getIdentifier(), $dnode->getIdentifier());

        $schild = $this->sharedFixture['session']->getNode("$src/srcFile");
        $dchild = $this->sharedFixture['session']->getNode("$dst/srcFile");
        $this->assertNotEquals($schild->getIdentifier(), $dchild->getIdentifier());

        // do not save, workspace should do directly
        $this->renewSession();

        $this->assertTrue($this->sharedFixture['session']->nodeExists($dst));

        $snode = $this->sharedFixture['session']->getNode($src);
        $dnode = $this->sharedFixture['session']->getNode($dst);
        $this->assertNotEquals($snode->getIdentifier(), $dnode->getIdentifier(), 'UUID was not changed');

        $schild = $this->sharedFixture['session']->getNode("$src/srcFile");
        $dchild = $this->sharedFixture['session']->getNode("$dst/srcFile");
        $this->assertNotEquals($schild->getIdentifier(), $dchild->getIdentifier());

        $this->assertTrue($this->sharedFixture['session']->nodeExists($dst.'/srcFile/jcr:content'), 'Did not copy the whole subgraph');

        $sfile = $this->sharedFixture['session']->getNode("$src/srcFile/jcr:content");
        $dfile = $this->sharedFixture['session']->getNode("$dst/srcFile/jcr:content");
        $this->assertEquals($sfile->getPropertyValue('jcr:data'), $dfile->getPropertyValue('jcr:data'));

        $dfile->setProperty('jcr:data', 'changed content');
        $this->assertNotEquals($sfile->getPropertyValue('jcr:data'), $dfile->getPropertyValue('jcr:data'));
        $this->sharedFixture['session']->save();

        $this->saveAndRenewSession();

        $sfile = $this->sharedFixture['session']->getNode("$src/srcFile/jcr:content");
        $dfile = $this->sharedFixture['session']->getNode("$dst/srcFile/jcr:content");
        $this->assertNotEquals($sfile->getPropertyValue('jcr:data'), $dfile->getPropertyValue('jcr:data'));
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



