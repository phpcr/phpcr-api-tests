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

use PHPCR\ItemExistsException;
use PHPCR\NoSuchWorkspaceException;
use PHPCR\PathNotFoundException;
use PHPCR\PropertyType;
use PHPCR\RepositoryException;
use PHPCR\Test\BaseCase;
use PHPCR\WorkspaceInterface;

/**
 * Covering jcr-283 spec $10.7.
 */
class CopyMethodsTest extends BaseCase
{
    /** @var WorkspaceInterface */
    protected $ws;

    public static function setupBeforeClass($fixtures = '10_Writing/copy'): void
    {
        parent::setupBeforeClass($fixtures);
    }

    protected function setUp(): void
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

    public function testCopyPreserveChildOrder()
    {
        $expected = ['three', 'one', 'two'];
        $src = '/tests_write_manipulation_copy/testCopyPreserveChildOrder/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyPreserveChildOrder/dstNode/srcNode';
        $node = $this->session->getNode($src);
        $node->orderBefore('three', 'one');
        $this->session->save();
        $this->assertEquals($expected, iterator_to_array($node->getNodeNames()));
        $this->ws->copy($src, $dst);
        $node = $this->session->getNode($dst);
        $this->assertEquals($expected, iterator_to_array($node->getNodeNames()));
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

    public function testCopyNoSuchWorkspace()
    {
        $this->expectException(NoSuchWorkspaceException::class);

        $src = '/somewhere/foo';
        $dst = '/here/foo';
        $this->ws->copy($src, $dst, 'inexistentworkspace');
    }

    public function testCopyRelativePaths()
    {
        $this->expectException(RepositoryException::class);

        $this->ws->copy('foo/moo', 'bar/mar');
    }

    public function testCopyInvalidDstPath()
    {
        $this->expectException(RepositoryException::class);

        $src = '/tests_write_manipulation_copy/testCopyInvalidDstPath/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyInvalidDstPath/dstNode/srcNode[3]';
        $this->ws->copy($src, $dst);
    }

    public function testCopyProperty()
    {
        $this->expectException(RepositoryException::class);

        $src = '/tests_write_manipulation_copy/testCopyProperty/srcFile/jcr:content/someProperty';
        $dst = '/tests_write_manipulation_copy/testCopyProperty/dstFile/jcr:content/someProperty';
        $this->ws->copy($src, $dst);
    }

    public function testCopySrcNotFound()
    {
        $this->expectException(PathNotFoundException::class);

        $src = '/tests_write_manipulation_copy/testCopySrcNotFound/notFound';
        $dst = '/tests_write_manipulation_copy/testCopySrcNotFound/dstNode/notFound';
        $this->ws->copy($src, $dst);
    }

    public function testCopyDstParentNotFound()
    {
        $this->expectException(PathNotFoundException::class);

        $src = '/tests_write_manipulation_copy/testCopyDstParentNotFound/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyDstParentNotFound/dstNode/notFound/srcNode';
        $this->ws->copy($src, $dst);
    }

    /**
     * Verifies that there is no update-on-copy if the target node already exists.
     */
    public function testCopyNoUpdateOnCopy()
    {
        $this->expectException(ItemExistsException::class);

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

        //Single value
        $srcProp = $this->session->getNode($src . '/single')->getProperty('jcr:data');
        $dstProp = $this->session->getNode($dst . '/single')->getProperty('jcr:data');

        $this->assertEquals(PropertyType::BINARY, $srcProp->getType());
        $this->assertEquals(PropertyType::BINARY, $dstProp->getType());

        $srcVal = $srcProp->getBinary();
        $dstVal = $dstProp->getBinary();

        $this->assertIsResource($srcVal, 'Failed to get src binary stream');
        $this->assertIsResource($dstVal, 'Failed to get dst binary stream');

        $this->assertEquals(stream_get_contents($srcVal), stream_get_contents($dstVal));
    }

    /**
     * Verifies that transport::copy actually copies binary data of children nodes
     * Multivalue test
     */
    public function testCopyChildrenBinaryDataMultivalue()
    {
        $src = '/tests_write_manipulation_copy/testCopyChildrenBinaryDataMultivalue/srcNode';
        $dst = '/tests_write_manipulation_copy/testCopyChildrenBinaryDataMultivalue/dstNode';

        $this->ws->copy($src, $dst);
        $this->session->refresh(true);

        //Multivalue
        $srcProp = $this->session->getNode($src.'/multiple')->getProperty('jcr:data');
        $dstProp = $this->session->getNode($dst.'/multiple')->getProperty('jcr:data');

        $this->assertEquals(PropertyType::BINARY, $srcProp->getType());
        $this->assertEquals(PropertyType::BINARY, $dstProp->getType());

        $srcVal = $srcProp->getValue();
        $dstVal = $dstProp->getValue();

        $this->assertIsArray($srcVal, 'Failed to get src value');
        $this->assertIsArray($dstVal, 'Failed to get dst value');

        $this->assertIsResource($srcVal[0]);
        $this->assertIsResource($srcVal[1]);
        $this->assertIsResource($dstVal[0]);
        $this->assertIsResource($dstVal[1]);

        $this->assertEquals(stream_get_contents($srcVal[0]), stream_get_contents($dstVal[0]));
        $this->assertEquals(stream_get_contents($srcVal[1]), stream_get_contents($dstVal[1]));
    }
}
