<?php
namespace PHPCR\Tests\Writing;


/**
 * Covering jcr-2.8.3 spec $10.6
 */
class MoveMethodsTest extends \PHPCR\Test\BaseCase
{

    public static function setupBeforeClass($fixtures = '10_Writing/move')
    {
        parent::setupBeforeClass($fixtures);
    }

    protected function setUp()
    {
        $this->renewSession();
        parent::setUp();
    }

    public function testNodeRenameRoot()
    {
        $root = $this->session->getRootNode();
        $newNode = $root->addNode('foobar');
        $newNode->rename('barfoo');
        $this->session->save();
        $this->renewSession();
        $node = $this->session->getNode('/barfoo');
        $this->assertNotNull($node);
    }

    public function testNodeRename()
    {
        $first = $this->node->getNode('firstNode');
        $child = $first->getNode('child');

        $first->rename('otherName');
        $this->assertEquals($this->node->getPath() . '/otherName', $first->getPath());
        $this->assertEquals($first->getPath() . '/child', $child->getPath());

        $session = $this->saveAndRenewSession();

        $this->assertTrue($session->nodeExists('/tests_write_manipulation_move/testNodeRename/otherName'));

        $parent = $session->getNode('/tests_write_manipulation_move/testNodeRename');
        $this->assertEquals(array('otherName', 'secondNode'), (array) $parent->getNodeNames(), 'Rename may not change position of nodes');
    }

    public function testSessionMove()
    {
        $src = '/tests_write_manipulation_move/testSessionMove/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMove/dstNode/srcNode';

        $this->session->move($src, $dst);

        // Session
        $this->assertTrue($this->session->nodeExists($dst), 'Destination node not found [S]');
        $this->assertFalse($this->session->nodeExists($src), 'Source node still exists [S]');
        $this->assertTrue($this->session->nodeExists($dst.'/srcFile/jcr:content'), 'Destination child node not found [S]');

        $dstNode = $this->session->getNode($dst);
        $this->assertInstanceOf('PHPCR\NodeInterface', $dstNode);

        $this->session->save();
        $this->assertTrue($this->session->nodeExists($dst), 'Destination node not found [B]');
        $this->assertFalse($this->session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($this->session->nodeExists($dst.'/srcFile/jcr:content'), 'Destination child node not found [B]');

        // Backend
        $session = $this->renewSession();
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [B]');
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($session->nodeExists($dst.'/srcFile/jcr:content'), 'Destination child node not found [B]');
    }

    /**
     * Try to move nodes:
     *
     * src:     /my/path
     * dst:     /my/new/path
     *
     * where the following node exists in the tree:
     * prob:    /my/pathSomething
     *
     * the moveNodes method should'nt consider the prob node
     */
    public function testSessionMoveSimilarSiblings()
    {
        $src = '/tests_write_manipulation_move/testSessionMoveSimilarSiblings/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveSimilarSiblings/dstNode/srcNode';

        $probSrc = '/tests_write_manipulation_move/testSessionMoveSimilarSiblings/srcNodeSibling';
        $probDst = '/tests_write_manipulation_move/testSessionMoveSimilarSiblings/dstNode/srcNodeSibling';

        $this->session->move($src, $dst);

        // Session
        $this->assertTrue($this->session->nodeExists($dst), 'Destination node not found [S]');
        $this->assertFalse($this->session->nodeExists($src), 'Source node still exists [S]');
        $this->assertTrue($this->session->nodeExists($dst . '/another'), 'Destination child node not found [S]');
        $this->assertFalse($this->session->nodeExists($src . '/another'), 'Source child node still exists [S]');
        $this->assertTrue($this->session->nodeExists($probSrc), 'Sibling nodes should\'nt be moved');
        $this->assertFalse($this->session->nodeExists($probDst), 'Sibling nodes should\'nt be moved');

        $this->session->save();
        $this->assertTrue($this->session->nodeExists($dst), 'Destination node not found [B]');
        $this->assertFalse($this->session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($this->session->nodeExists($dst . '/another'), 'Destination child node not found [B]');
        $this->assertFalse($this->session->nodeExists($src . '/another'), 'Source child node still exists [B]');
        $this->assertTrue($this->session->nodeExists($probSrc), 'Sibling nodes should\'nt be moved');
        $this->assertFalse($this->session->nodeExists($probDst), 'Sibling nodes should\'nt be moved');

        // Backend
        $session = $this->renewSession();
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [B]');
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($session->nodeExists($dst . '/another'), 'Destination child node not found [B]');
        $this->assertFalse($session->nodeExists($src. '/another'), 'Source child node still exists [B]');
        $this->assertTrue($session->nodeExists($probSrc), 'Sibling nodes should\'nt be moved');
        $this->assertFalse($session->nodeExists($probDst), 'Sibling nodes should\'nt be moved');
    }

    /**
     * Try to move nodes that are already held in memory:
     *
     * src:     /my/path
     * dst:     /my/new/path
     *
     * where the following node exists in the tree, and is already in memory:
     * prob:    /my/pathSomething
     *
     * the moveNodes method should'nt consider the prob node
     */
    public function testSessionMoveSimilarSiblingsInMemory()
    {
        $src = '/tests_write_manipulation_move/testSessionMoveSimilarSiblingsInMemory/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveSimilarSiblingsInMemory/dstNode/srcNode';

        $probSrc = '/tests_write_manipulation_move/testSessionMoveSimilarSiblingsInMemory/srcNodeSibling';
        $probDst = '/tests_write_manipulation_move/testSessionMoveSimilarSiblingsInMemory/dstNode/srcNodeSibling';

        $this->session->getNode($src);
        $this->session->getNode($probSrc);

        $this->session->move($src, $dst);

        // Session
        $this->assertTrue($this->session->nodeExists($dst), 'Destination node not found [S]');
        $this->assertFalse($this->session->nodeExists($src), 'Source node still exists [S]');
        $this->assertTrue($this->session->nodeExists($dst . '/another'), 'Destination child node not found [S]');
        $this->assertFalse($this->session->nodeExists($src . '/another'), 'Source child node still exists [S]');
        $this->assertTrue($this->session->nodeExists($probSrc), 'Sibling nodes should\'nt be moved');
        $this->assertFalse($this->session->nodeExists($probDst), 'Sibling nodes should\'nt be moved');

        $this->session->save();
        $this->assertTrue($this->session->nodeExists($dst), 'Destination node not found [B]');
        $this->assertFalse($this->session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($this->session->nodeExists($dst . '/another'), 'Destination child node not found [B]');
        $this->assertFalse($this->session->nodeExists($src . '/another'), 'Source child node still exists [B]');
        $this->assertTrue($this->session->nodeExists($probSrc), 'Sibling nodes should\'nt be moved');
        $this->assertFalse($this->session->nodeExists($probDst), 'Sibling nodes should\'nt be moved');

        // Backend
        $session = $this->renewSession();
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [B]');
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($session->nodeExists($dst . '/another'), 'Destination child node not found [B]');
        $this->assertFalse($session->nodeExists($src. '/another'), 'Source child node still exists [B]');
        $this->assertTrue($session->nodeExists($probSrc), 'Sibling nodes should\'nt be moved');
        $this->assertFalse($session->nodeExists($probDst), 'Sibling nodes should\'nt be moved');
    }

    public function testSessionMoveWhitespace()
    {
        $src = '/tests_write_manipulation_move/testSessionMoveWhitespace/jcr:src Node';
        $dst = '/tests_write_manipulation_move/testSessionMoveWhitespace/dst Node/srcNode';

        $this->session->move($src, $dst);

        // Session
        $this->assertTrue($this->session->nodeExists($dst), 'Destination node not found [S]');
        $this->assertFalse($this->session->nodeExists($src), 'Source node still exists [S]');
        $this->assertTrue($this->session->nodeExists($dst.'/srcFile/jcr:content'), 'Destination child node not found [S]');

        $dstNode = $this->session->getNode($dst);
        $this->assertInstanceOf('PHPCR\NodeInterface', $dstNode);

        $this->session->save();
        $this->assertTrue($this->session->nodeExists($dst), 'Destination node not found [B]');
        $this->assertFalse($this->session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($this->session->nodeExists($dst.'/srcFile/jcr:content'), 'Destination child node not found [B]');

        // Backend
        $session = $this->renewSession();
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [B]');
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($session->nodeExists($dst.'/srcFile/jcr:content'), 'Destination child node not found [B]');
    }

    /**
     * Makes sure that UUID is not modified during a move for mix:referenceable nodes
     */
    public function testSessionMoveReferenceable()
    {
        $dst = $this->node->getPath() . '/dstNode/srcNode';
        $node = $this->node->getNode('srcNode');
        $srcUuid = $node->getIdentifier();
        $src = $node->getPath();
        $this->session->move($src, $dst);

        $this->assertSame($node, $this->session->getNodeByIdentifier($srcUuid));

        // Session
        $this->assertFalse($this->session->nodeExists($src), 'Source node still exists [S]');
        $this->assertTrue($this->session->nodeExists($dst), 'Destination node not found [S]');

        // Backend
        $session = $this->saveAndRenewSession();
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [B]');
        $this->assertEquals($srcUuid, $session->getNode($dst)->getIdentifier(), 'UUID of referenceable was modified during move');
    }

    /**
     * Verifies that locally cached node itself knows about the move and Node::getPath()
     * returns the new path
     */
    public function testSessionMovePathUpdated()
    {
        $src = '/tests_write_manipulation_move/testSessionMovePathUpdated/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMovePathUpdated/dstNode/srcNode';

        // load node into cache
        $node = $this->session->getNode($src);

        $this->session->move($src, $dst);

        $this->assertEquals($dst, $node->getPath(), 'Path of locally cached node was not updated');
        $this->assertEquals($dst, $this->session->getNode($dst)->getPath(), 'Path of locally cached node was not updated');
    }

    /** Verifies the path in a child of a moved node is updated */
    public function testSessionMovePathUpdatedChild()
    {
        $src = '/tests_write_manipulation_move/testSessionMovePathUpdatedChild/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMovePathUpdatedChild/dstNode/srcNode';

        // load nodes
        $parent = $this->session->getNode($src);
        $child = $this->session->getNode($src.'/srcChild');

        $this->session->move($src, $dst);

        $this->assertEquals("$dst/srcChild", $child->getPath());
        $this->assertEquals($dst, $parent->getPath());
        $this->assertSame($parent, $this->session->getNode($dst));
        $this->assertSame($child, $this->session->getNode($dst.'/srcChild'));
    }

    /** Verifies a moved node still has the child node */
    public function testSessionMoveHasNode()
    {
        $src = '/tests_write_manipulation_move/testSessionMoveHasNode/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveHasNode/dstNode/srcNode';

        // load node and child into cache
        $srcNode = $this->session->getNode($src);
        $srcNode->getNodes();

        $this->session->move($src, $dst);

        $this->assertTrue($srcNode->hasNode('srcChild'));
    }

    /** Verifies the parent of a moved node no longer has the node as child */
    public function testSessionMoveHasNodeParent()
    {
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $src = '/tests_write_manipulation_move/testSessionMoveHasNodeParent/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveHasNodeParent/dstNode/srcNode';

        // load node into cache
        $this->session->getNode($src);

        $this->session->move($src, $dst);

        $this->assertFalse($this->node->hasNode('srcNode'), 'Parent of node still has a reference to the moved node');
    }

    public function testSessionMoveMoved()
    {
        $src = '/tests_write_manipulation_move/testSessionMoveMoved/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveMoved/dstNode/srcNode';
        $dst2 = '/tests_write_manipulation_move/testSessionMoveMoved/dstNode2/srcNode';

        $this->session->move($src, $dst);
        $this->session->move($dst, $dst2);

        // Session
        $this->assertFalse($this->session->nodeExists($src), 'Source node still exists [S]');
        $this->assertFalse($this->session->nodeExists($dst), 'Intermediate source node still exists [S]');
        $this->assertTrue($this->session->nodeExists($dst2), 'Destination source not found [S]');

        // Backend
        $session = $this->saveAndRenewSession();
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [B]');
        $this->assertFalse($session->nodeExists($dst), 'Intermediate source node still exists [B]');
        $this->assertTrue($session->nodeExists($dst2), 'Destination source not found [B]');
    }

    public function testSessionMoveReplace()
    {
        $src = $this->node->getPath().'/node';
        $dst = $this->node->getPath().'/moved';
        $src2 = $this->node->getPath().'/replacement';

        $this->node->getNode('node');
        $this->node->getNode('replacement');

        $this->session->move($src, $dst);
        $this->session->move($src2, $src);

        // Session
        $this->assertTrue($this->session->nodeExists($dst));
        $this->assertTrue($this->session->nodeExists("$dst/child"));
        $this->assertTrue($this->session->nodeExists($src));
        $this->assertTrue($this->session->nodeExists("$src/child"));

        // Backend
        $session = $this->saveAndRenewSession();
        $this->assertTrue($session->nodeExists($src));
        $this->assertTrue($session->nodeExists("$src/child"));
        $this->assertTrue($session->nodeExists($dst));
        $this->assertTrue($session->nodeExists("$dst/child"));
    }

    public function testSessionDeleteMoved()
    {
        $src = '/tests_write_manipulation_move/testSessionDeleteMoved/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionDeleteMoved/dstNode/srcNode';

        $this->session->move($src, $dst);
        $this->assertFalse($this->session->nodeExists($src));
        $this->assertTrue($this->session->nodeExists($dst));
        $this->session->removeItem($dst);
        $this->assertFalse($this->session->nodeExists($src));
        $this->assertFalse($this->session->nodeExists($dst));

        $session = $this->saveAndRenewSession();

        $this->assertFalse($session->nodeExists($src));
        $this->assertFalse($session->nodeExists($dst));
    }

    public function testSessionMoveAdded()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $this->node->addNode('newNode', 'nt:unstructured');
        $src = '/tests_write_manipulation_move/testSessionMoveAdded/newNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveAdded/dstNode/newNode';

        $this->session->move($src, $dst);

        // Session
        $this->assertFalse($this->session->nodeExists($src), 'Source node still exists [S]');
        $this->assertTrue($this->session->nodeExists($dst), 'Destination node not found [S]');

        // Backend
        $session = $this->saveAndRenewSession();
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [B]');

        $session = $this->saveAndRenewSession();
        $this->assertFalse($session->nodeExists($src), 'Source child node still exists [B]');
        $this->assertTrue($session->nodeExists($dst), 'Destination child node not found [B]');
    }

    /**
     * Adds a node, moves its child
     */
    public function testSessionMoveChildAdded()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $newNode = $this->node->addNode('newNode', 'nt:unstructured');
        $newNode->addNode('newChild', 'nt:unstructured');

        $src = '/tests_write_manipulation_move/testSessionMoveChildAdded/newNode/newChild';
        $dst = '/tests_write_manipulation_move/testSessionMoveChildAdded/dstNode/newChild';

        $this->session->move($src, $dst);

        // Session
        $this->assertFalse($this->session->nodeExists($src), 'Source child node still exists [S]');
        $this->assertTrue($this->session->nodeExists($dst), 'Destination child node not found [S]');

        // Backend
        $session = $this->saveAndRenewSession();
        $this->assertFalse($session->nodeExists($src), 'Source child node still exists [B]');
        $this->assertTrue($session->nodeExists($dst), 'Destination child node not found [B]');
    }

    /**
     * Moves a node and then moves its child
     */
    public function testSessionMoveChildMoved()
    {
        $src = '/tests_write_manipulation_move/testSessionMoveChildMoved/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveChildMoved/dstNode/srcNode';
        $dst2 = '/tests_write_manipulation_move/testSessionMoveChildMoved/srcFile';

        $this->session->move($src, $dst);
        $this->session->move($dst.'/srcFile', $dst2);

        // Session
        $this->assertFalse($this->session->nodeExists($src), 'Source node still exists [S]');
        $this->assertTrue($this->session->nodeExists($dst), 'Destination node not found [S]');
        $this->assertTrue($this->session->nodeExists($dst2), 'Destination child node not found [S]');

        // Backend
        $session = $this->saveAndRenewSession();
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [B]');
        $this->assertTrue($session->nodeExists($dst2), 'Destination child node not found [B]');
    }

    /**
     * @expectedException   \PHPCR\RepositoryException
     */
    public function testSessionMoveProperty()
    {
        $src = '/tests_write_manipulation_move/testSessionMoveProperty/srcNode/prop';
        $dst = '/tests_write_manipulation_move/testSessionMoveProperty/dstNode/prop';
        $this->session->move($src, $dst);
        $this->session->save();
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
     */
    public function testSessionMoveToProperty()
    {
        $src = '/tests_write_manipulation_move/testSessionMoveToProperty/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveToProperty/dstNode/prop/fail';
        $this->session->move($src, $dst);
        $this->session->save();
    }

    /**
     * @expectedException   \PHPCR\RepositoryException
     */
    public function testSessionMoveInvalidDstPath()
    {
        $src = '/tests_write_manipulation_move/testSessionMoveInvalidDstPath/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveInvalidDstPath/dstNode/srcNode[3]';
        $this->session->move($src, $dst);
        $this->session->save();
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
     */
    public function testSessionMoveSrcNotFound()
    {
        $src = '/tests_write_manipulation_move/testSessionMoveSrcNotFound/notFound';
        $dst = '/tests_write_manipulation_move/testSessionMoveSrcNotFound/dstNode/notFound';
        $this->session->move($src, $dst);
        $this->session->save();
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
     */
    public function testSessionMoveDstNotFound()
    {
        $src = '/tests_write_manipulation_move/testSessionMoveDstNotFound/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveDstNotFound/notFound/srcNode';
        $this->session->move($src, $dst);
        $this->session->save();
    }

    /**
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testSessionMoveDstExists()
    {
        $src = '/tests_write_manipulation_move/testSessionMoveDstExists/srcNode/srcChild';
        $dst = '/tests_write_manipulation_move/testSessionMoveDstExists/dstNode/srcChild';

        // srcChild already exists at $dst
        $this->session->move($src, $dst);
        $this->session->save();
    }

    public function testWorkspaceMove()
    {
        $workspace = $this->session->getWorkspace();
        $src = '/tests_write_manipulation_move/testWorkspaceMove/srcNode';
        $dst = '/tests_write_manipulation_move/testWorkspaceMove/dstNode/srcNode';

        $srcUuid = $this->session->getNode($src)->getIdentifier();
        $workspace->move($src, $dst);

        // Session
        $this->assertFalse($this->session->nodeExists($src), 'Source node still exists [S]');
        $this->assertTrue($this->session->nodeExists($dst), 'Destination node not found [S]');

        // Backend
        $session = $this->saveAndRenewSession();
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [B]');
        $this->assertEquals($srcUuid, $session->getNode($dst)->getIdentifier());
    }
}
