<?php
namespace PHPCR\Tests\Writing;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Covering jcr-2.8.3 spec $10.6
 */
class MoveMethodsTest extends \PHPCR\Test\BaseCase
{

    static public function setupBeforeClass($fixtures = '10_Writing/move')
    {
        parent::setupBeforeClass($fixtures);
    }

    protected function setUp()
    {
        $this->renewSession();
        parent::setUp();
    }

    public function testSessionMove()
    {
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionMove/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMove/dstNode/srcNode';

        $session->move($src, $dst);

        // Session
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [S]');
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [S]');
        $this->assertTrue($session->nodeExists($dst.'/srcFile/jcr:content'), 'Destination child node not found [S]');

        $dstNode = $session->getNode($dst);
        $this->assertInstanceOf('PHPCR\NodeInterface', $dstNode);

        // Backend
        $session = $this->saveAndRenewSession();
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [B]');
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($session->nodeExists($dst.'/srcFile/jcr:content'), 'Destination child node not found [B]');
    }

    /**
     * Makes sure that UUID is not modified during a move for mix:referencable nodes
     */
    public function testSessionMoveReferencable()
    {
        $session = $this->sharedFixture['session'];
        // has mix:referenceable
        $src = '/tests_write_manipulation_move/testSessionMoveReferencable/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveReferencable/dstNode/srcNode';

        $srcUuid = $session->getNode($src)->getIdentifier();
        $session->move($src, $dst);

        // Session
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [S]');
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [S]');

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
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionMovePathUpdated/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMovePathUpdated/dstNode/srcNode';

        // load node into cache
        $session->getNode($src);

        $session->move($src, $dst);

        $this->assertEquals($dst, $session->getNode($dst)->getPath(), 'Path of locally cached node was not updated');
    }

    /** Verifies the path in a child of a moved node is updated */
    public function testSessionMovePathUpdatedChild()
    {
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionMovePathUpdatedChild/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMovePathUpdatedChild/dstNode/srcNode';

        // load nodes into cache
        $session->getNode($src);
        $session->getNode($src.'/srcChild');

        $session->move($src, $dst);

        $this->assertEquals($dst, $session->getNode($dst)->getPath(), 'Path of locally cached node was not updated');
        $this->assertEquals($dst.'/srcChild', $session->getNode($dst.'/srcChild')->getPath(), 'Path of locally cached child node was not updated');
    }

    /** Verifies a moved node still has the child node */
    public function testSessionMoveHasNode()
    {
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionMoveHasNode/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveHasNode/dstNode/srcNode';

        // load node and child into cache
        $srcNode = $session->getNode($src);
        $srcNode->getNodes();

        $session->move($src, $dst);

        $this->assertTrue($srcNode->hasNode('srcChild'));
    }

    /** Verifies the parent of a moved node no longer has the node as child */
    public function testSessionMoveHasNodeParent()
    {
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionMoveHasNodeParent/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveHasNodeParent/dstNode/srcNode';

        // load node into cache
        $session->getNode($src);

        $session->move($src, $dst);

        $this->assertFalse($this->node->hasNode('srcNode'), 'Parent of node still has a reference to the moved node');
    }

    public function testSessionMoveMoved()
    {
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionMoveMoved/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveMoved/dstNode/srcNode';
        $dst2 = '/tests_write_manipulation_move/testSessionMoveMoved/dstNode2/srcNode';

        $session->move($src, $dst);
        $session->move($dst, $dst2);

        // Session
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [S]');
        $this->assertFalse($session->nodeExists($dst), 'Intermediate source node still exists [S]');
        $this->assertTrue($session->nodeExists($dst2), 'Destination source not found [S]');

        // Backend
        $session = $this->saveAndRenewSession();
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [B]');
        $this->assertFalse($session->nodeExists($dst), 'Intermediate source node still exists [B]');
        $this->assertTrue($session->nodeExists($dst2), 'Destination source not found [B]');
    }

    public function testSessionDeleteMoved()
    {
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionDeleteMoved/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionDeleteMoved/dstNode/srcNode';

        $session->move($src, $dst);
        $this->assertFalse($session->nodeExists($src));
        $this->assertTrue($session->nodeExists($dst));
        $session->removeItem($dst);
        $this->assertFalse($session->nodeExists($src));
        $this->assertFalse($session->nodeExists($dst));

        $session = $this->saveAndRenewSession();

        $this->assertFalse($session->nodeExists($src));
        $this->assertFalse($session->nodeExists($dst));
    }

    public function testSessionMoveAdded()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $session = $this->sharedFixture['session'];

        $this->node->addNode('newNode', 'nt:unstructured');
        $src = '/tests_write_manipulation_move/testSessionMoveAdded/newNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveAdded/dstNode/newNode';

        $session->move($src, $dst);

        // Session
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [S]');
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [S]');

        // Backend
        $session = $this->saveAndRenewSession();
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [B]');
    }

    /**
     * Adds a node, moves its child
     */
    public function testSessionMoveChildAdded()
    {
        //relies on the base class setup trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node);

        $session = $this->sharedFixture['session'];

        $newNode = $this->node->addNode('newNode', 'nt:unstructured');
        $newNode->addNode('newChild', 'nt:unstructured');

        $src = '/tests_write_manipulation_move/testSessionMoveChildAdded/newNode/newChild';
        $dst = '/tests_write_manipulation_move/testSessionMoveChildAdded/dstNode/newChild';

        $session->move($src, $dst);

        // Session
        $this->assertFalse($session->nodeExists($src), 'Source child node still exists [S]');
        $this->assertTrue($session->nodeExists($dst), 'Destination child node not found [S]');

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
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionMoveChildMoved/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveChildMoved/dstNode/srcNode';
        $dst2 = '/tests_write_manipulation_move/testSessionMoveChildMoved/srcFile';

        $session->move($src, $dst);
        $session->move($dst.'/srcFile', $dst2);

        // Session
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [S]');
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [S]');
        $this->assertTrue($session->nodeExists($dst2), 'Destination child node not found [S]');

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
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionMoveProperty/srcNode/prop';
        $dst = '/tests_write_manipulation_move/testSessionMoveProperty/dstNode/prop';
        $session->move($src, $dst);
        $session->save();
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
     */
    public function testSessionMoveToProperty()
    {
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionMoveToProperty/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveToProperty/dstNode/prop/fail';
        $session->move($src, $dst);
        $session->save();
    }

    /**
     * @expectedException   \PHPCR\RepositoryException
     */
    public function testSessionMoveInvalidDstPath()
    {
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionMoveInvalidDstPath/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveInvalidDstPath/dstNode/srcNode[3]';
        $session->move($src, $dst);
        $session->save();
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
     */
    public function testSessionMoveSrcNotFound()
    {
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionMoveSrcNotFound/notFound';
        $dst = '/tests_write_manipulation_move/testSessionMoveSrcNotFound/dstNode/notFound';
        $session->move($src, $dst);
        $session->save();
    }

    /**
     * @expectedException   \PHPCR\PathNotFoundException
     */
    public function testSessionMoveDstNotFound()
    {
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionMoveDstNotFound/srcNode';
        $dst = '/tests_write_manipulation_move/testSessionMoveDstNotFound/notFound/srcNode';
        $session->move($src, $dst);
        $session->save();
    }

    /**
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testSessionMoveDstExists()
    {
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_move/testSessionMoveDstExists/srcNode/srcChild';
        $dst = '/tests_write_manipulation_move/testSessionMoveDstExists/dstNode/srcChild';

        // srcChild already exists at $dst
        $session->move($src, $dst);
        $session->save();
    }

    public function testWorkspaceMove()
    {
        $session = $this->sharedFixture['session'];

        $workspace = $session->getWorkspace();
        $src = '/tests_write_manipulation_move/testWorkspaceMove/srcNode';
        $dst = '/tests_write_manipulation_move/testWorkspaceMove/dstNode/srcNode';

        $srcUuid = $session->getNode($src)->getIdentifier();
        $workspace->move($src, $dst);

        // Session
        $this->assertFalse($this->sharedFixture['session']->nodeExists($src), 'Source node still exists [S]');
        $this->assertTrue($this->sharedFixture['session']->nodeExists($dst), 'Destination node not found [S]');

        // Backend
        $session = $this->saveAndRenewSession();
        $this->assertFalse($session->nodeExists($src), 'Source node still exists [B]');
        $this->assertTrue($session->nodeExists($dst), 'Destination node not found [B]');
    }

    /**
     * Helper method to assert a certain order of the child nodes
     *
     * @param array $names array values are the names in expected order
     * @param \PHPCR\NodeInterface $node the node whos children are to be checked
     */
    private function assertChildOrder($names, $node)
    {
        $children = array();
        foreach ($node as $name => $node) {
            $children[] = $name;
        }
        $this->assertEquals($names, $children);
    }

    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderBeforeUp()
    {
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $this->node->orderBefore('three', 'two');
        $this->assertChildOrder(array('one', 'three', 'two', 'four'), $this->node);

        $this->saveAndRenewSession();

        $this->assertChildOrder(array('one', 'three', 'two', 'four'), $this->node);
    }
    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderBeforeDown()
    {
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $this->node->orderBefore('two', 'four');
        $this->assertChildOrder(array('one', 'three', 'two', 'four'), $this->node);

        $this->saveAndRenewSession();

        $this->assertChildOrder(array('one', 'three', 'two', 'four'), $this->node);
    }
    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderBeforeEnd()
    {
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $this->node->orderBefore('two', null);
        $this->assertChildOrder(array('one', 'three', 'four', 'two'), $this->node);

        $this->saveAndRenewSession();

        $this->assertChildOrder(array('one', 'three', 'four', 'two'), $this->node);
    }
    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderBeforeNoop()
    {
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $this->node->orderBefore('two', 'two');
        $this->assertChildOrder(array('one', 'two', 'three', 'four'), $this->node);

        $this->saveAndRenewSession();

        $this->assertChildOrder(array('one', 'two', 'three', 'four'), $this->node);
    }
    /**
     * \PHPCR\NodeInterface::orderBefore
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testNodeOrderBeforeSrcNotFound()
    {
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $this->node->orderBefore('notexisting', 'one');
    }
    /**
     * \PHPCR\NodeInterface::orderBefore
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testNodeOrderBeforeDestNotFound()
    {
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $this->node->orderBefore('one', 'notexisting');
    }

}


