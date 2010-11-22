<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * Covering jcr-2.8.3 spec $10.6
 */
class Write_Manipulation_MoveMethodsTest extends jackalope_baseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('write/manipulation/base.xml');
    }

    /**
     * @covers Jackalope_Session::move
     */
    public function testSessionMove()
    {
        $session = $this->sharedFixture['session'];
        // has mix:referenceable
        $src = '/tests_write_manipulation_base/multiValueProperty';
        $dst = '/tests_write_manipulation_base/emptyExample';

        $srcUuid = $session->getNode($src)->getIdentifier();
        $session->move($src, $dst);

        // node was moved
        $movedNode = $session->getNode($dst.'/'.basename($src));
        $this->assertNotNull($movedNode, 'Cannot find moved node');

        // uuid unchanged
        $this->assertEquals($srcUuid, $movedNode->getIdentifier(), 'UUID of referenceable was modified during move');
    }


    /**
     * @covers Jackalope_Session::move
     * @expectedException \PHPCR\ItemExistsException
     */
    public function testSessionMoveDstExists()
    {
        $session = $this->sharedFixture['session'];

        $src = '/tests_write_manipulation_base/index.txt/jcr:content';
        // jcr:content already exists at $dst
        $dst = '/tests_write_manipulation_base/idExample';

        $session->move($src, $dst);
    }

    public function testWorkspaceMove()
    {
        $session = $this->sharedFixture['session'];
        $workspace = $session->getWorkspace();
        $src = '/tests_write_manipulation_base/multiValueProperty';
        $dst = '/tests_write_manipulation_base/emptyExample';

        $srcUuid = $session->getNode($src)->getIdentifier();
        $workspace->move($src, $dst);

        // session was updated as well
        $this->assertNotNull($session->getNode($dst.'/'.basename($src)));

        // uuid unchanged
        $this->assertEquals($srcUuid, $movedNode->getIdentifier(), 'UUID of referenceable was modified during move');

        // TODO workspace-write method, also verify that the move was dispatched to the backend
    }

    /**
     * @covers Node::orderBefore
     */
    public function testNodeOrderBefore()
    {
        $this->markTestSkipped('TODO: implement different use cases. move up, down, same paths, end, inexisting src, inexisting dest');
    }

}


