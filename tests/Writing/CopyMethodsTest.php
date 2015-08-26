<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Writing;

use PHPCR\WorkspaceInterface;

/**
 * Covering jcr-283 spec $10.7.
 */
class CopyMethodsTest extends \PHPCR\Test\BaseCase
{
    /** @var WorkspaceInterface */
    protected $ws;

    public static function setupBeforeClass($fixtures = '10_Writing/copy')
    {
        parent::setupBeforeClass($fixtures);
    }

    protected function setUp()
    {
        $this->renewSession(); // get rid of cache from previous tests
        parent::setUp();

        $this->ws = $this->session->getWorkspace();
    }

    public function testWorkspaceCopy()
    {
        $src = '/tests_write_manipulation_copy/testWorkspaceCopy/srcNode';
        $dst = '/tests_write_manipulation_copy/testWorkspaceCopy/dstNode/srcNode';

        $this->ws->copy($src, $dst);

        $snode = $this->session->getNode($src);
        $dnode = $this->session->getNode($dst);
        $this->assertNotEquals($snode->getIdentifier(), $dnode->getIdentifier());

        $schild = $this->session->getNode("$src/srcFile");
        $dchild = $this->session->getNode("$dst/srcFile");
        $this->assertNotEquals($schild->getIdentifier(), $dchild->getIdentifier());

        // do not save, workspace should do directly
        $this->renewSession();

        $this->assertTrue($this->session->nodeExists($dst));

        $snode = $this->session->getNode($src);
        $dnode = $this->session->getNode($dst);
        $this->assertNotEquals($snode->getIdentifier(), $dnode->getIdentifier(), 'UUID was not changed');

        $schild = $this->session->getNode("$src/srcFile");
        $dchild = $this->session->getNode("$dst/srcFile");
        $this->assertNotEquals($schild->getIdentifier(), $dchild->getIdentifier());

        $this->assertTrue($this->session->nodeExists($dst.'/srcFile/jcr:content'), 'Did not copy the whole subgraph');

        $sfile = $this->session->getNode("$src/srcFile/jcr:content");
        $dfile = $this->session->getNode("$dst/srcFile/jcr:content");
        $this->assertEquals($sfile->getPropertyValue('jcr:data'), $dfile->getPropertyValue('jcr:data'));

        $dfile->setProperty('jcr:data', 'changed content');
        $this->assertNotEquals($sfile->getPropertyValue('jcr:data'), $dfile->getPropertyValue('jcr:data'));
        $this->session->save();

        $this->saveAndRenewSession();

        $sfile = $this->session->getNode("$src/srcFile/jcr:content");
        $dfile = $this->session->getNode("$dst/srcFile/jcr:content");
        $this->assertNotEquals($sfile->getPropertyValue('jcr:data'), $dfile->getPropertyValue('jcr:data'));
    }

    public function testWorkspaceCopyReference()
    {
        $src = '/tests_write_manipulation_copy/testWorkspaceCopy/referencedNodeSet';
        $dst = '/tests_write_manipulation_copy/testWorkspaceCopy/dstNode/copiedReferencedSet';

        $this->ws->copy($src, $dst);

        $snode = $this->session->getNode($src);
        $dnode = $this->session->getNode($dst);

        $this->assertNotEquals($snode->getIdentifier(), $dnode->getIdentifier());

        $homeNode = $dnode->getNode('home');
        $block1Ref = $homeNode->getProperty('block_1_ref')->getValue()->getIdentifier();
        $block2Ref = $homeNode->getProperty('block_2_ref')->getValue()->getIdentifier();
        $block3Ref = $homeNode->getProperty('block_2_ref')->getValue()->getIdentifier();

        $externalRef = $homeNode->getProperty('external_reference')->getValue()->getIdentifier();

        $this->assertEquals('842e61c0-09ab-42a9-87c0-308ccc90e6f6', $externalRef);

        $block1 = $homeNode->getNode('block_1');
        $this->assertEquals($block1->getIdentifier(), $block1Ref);

        $block2 = $block1->getNode('block_2');
        $this->assertEquals($block2->getIdentifier(), $block2Ref);

        // weak reference
        $block3 = $block1->getNode('block_3');
        $this->assertEquals($block2->getIdentifier(), $block2Ref);
    }

    public function testWorkspaceCopyOther()
    {
        self::$staticSharedFixture['ie']->import('general/additionalWorkspace', 'additionalWorkspace');
        $src = '/tests_additional_workspace/testWorkspaceCopyOther/node';
        $dst = '/tests_write_manipulation_copy/testWorkspaceCopyOther/foobar';

        $this->ws->copy($src, $dst, self::$loader->getOtherWorkspaceName());
        $node = $this->session->getNode($dst);
        $this->assertTrue($node->hasProperty('x'));
        $this->assertEquals('y', $node->getPropertyValue('x'));
    }

    /**
     * @expectedException \PHPCR\NoSuchWorkspaceException
     */
    public function testCopyNoSuchWorkspace()
    {
        $src = '/somewhere/foo';
        $dst = '/here/foo';
        $this->ws->copy($src, $dst, 'inexistentworkspace');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testCopyRelativePaths()
    {
        $this->ws->copy('foo/moo', 'bar/mar');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testCopyInvalidDstPath()
    {
        $src = '/tests_write_manipulation_copy/testCopyInvalidDstPath/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyInvalidDstPath/dstNode/srcNode[3]';
        $this->ws->copy($src, $dst);
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testCopyProperty()
    {
        $src = '/tests_write_manipulation_copy/testCopyProperty/srcFile/jcr:content/someProperty';
        $dst = '/tests_write_manipulation_copy/testCopyProperty/dstFile/jcr:content/someProperty';
        $this->ws->copy($src, $dst);
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testCopySrcNotFound()
    {
        $src = '/tests_write_manipulation_copy/testCopySrcNotFound/notFound';
        $dst = '/tests_write_manipulation_copy/testCopySrcNotFound/dstNode/notFound';
        $this->ws->copy($src, $dst);
    }

    /**
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testCopyDstParentNotFound()
    {
        $src = '/tests_write_manipulation_copy/testCopyDstParentNotFound/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyDstParentNotFound/dstNode/notFound/srcNode';
        $this->ws->copy($src, $dst);
    }

    /**
     * Verifies that there is no update-on-copy if the target node already exists.
     *
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testCopyNoUpdateOnCopy()
    {
        $src = '/tests_write_manipulation_copy/testCopyNoUpdateOnCopy/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyNoUpdateOnCopy/dstNode/srcNode';

        $this->ws->copy($src, $dst);
    }

    /**
     * When a node is copied, any nodes to which it refers should show the copied node in its list of references.
     */
    public function testCopyUpdateReferencesSingleValue()
    {
        $src = '/tests_write_manipulation_copy/testCopyUpdateReferrersSingleValue/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyUpdateReferrersSingleValue/dstNode';
        $ref = '/tests_write_manipulation_copy/testCopyUpdateReferrersSingleValue/referencedNode';

        $node = $this->session->getNode($ref);
        $references = $node->getReferences();
        $this->assertCount(1, $references);

        $this->ws->copy($src, $dst);

        $references = $node->getReferences();
        $this->assertCount(2, $references);

        $this->session->refresh(true);

        $node = $this->session->getNode($ref);
        $references = $node->getReferences();

        $this->assertCount(2, $references);
    }

    /**
     * Copied nodes which reference other nodes should be shown in the referrers list of references
     * Multi-value.
     */
    public function testCopyUpdateReferencesMultiValue()
    {
        $src = '/tests_write_manipulation_copy/testCopyUpdateReferrersMultiValue/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyUpdateReferrersMultiValue/dstNode';
        $ref1 = '/tests_write_manipulation_copy/testCopyUpdateReferrersMultiValue/referencedNode1';
        $ref2 = '/tests_write_manipulation_copy/testCopyUpdateReferrersMultiValue/referencedNode2';

        $this->ws->copy($src, $dst);
        $this->session->refresh(true);

        $node = $this->session->getNode($ref1);
        $references = $node->getReferences();

        $this->assertCount(2, $references);
    }

    /**
     * Verifies that transport::copy actually copies binary data of children nodes
     */
    public function testCopyChildrenBinaryData()
    {
        $src = '/tests_write_manipulation_copy/testCopyChildrenBinaryData/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyChildrenBinaryData/dstNode';


        $this->ws->copy($src, $dst);
        $this->session->refresh(true);
        $srcChild = $this->session->getNode($src.'/data.bin');
        $dstChild = $this->session->getNode($dst.'/data.bin');

        $srcProp = $srcChild->getProperty('jcr:data');
        $dstProp = $dstChild->getProperty('jcr:data');

        $this->assertThat(true,
            $this->logicalAnd(
                $this->equalTo(\PHPCR\PropertyType::BINARY, $srcProp->getType()),
                $this->equalTo(\PHPCR\PropertyType::BINARY, $dstProp->getType())
            )
        );

        $srcBin = $srcProp->getBinary();
        $dstBin = $dstProp->getBinary();

        if (!is_resource($srcBin) || !is_resource($dstBin)) {
            $this->fail();
        }

        $this->assertEquals(stream_get_contents($srcBin), stream_get_contents($dstBin));

    }
}
