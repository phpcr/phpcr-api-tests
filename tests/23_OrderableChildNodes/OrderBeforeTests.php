<?php
namespace PHPCR\Tests\OrderableChildNodes;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Covering jcr-2.8.3 spec $23
 */
class OrderBeforeTest extends \PHPCR\Test\BaseCase
{

    static public function setupBeforeClass($fixtures = '23_OrderableChildNodes/orderable')
    {
        parent::setupBeforeClass($fixtures);
    }

    protected function setUp()
    {
        $this->renewSession();
        parent::setUp();
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
        $session = $this->sharedFixture['session'];
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $this->node->orderBefore('three', 'two');
        $this->assertChildOrder(array('one', 'three', 'two', 'four'), $this->node);
        $session->save();
        $this->assertChildOrder(array('one', 'three', 'two', 'four'), $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(array('one', 'three', 'two', 'four'), $node);
    }

    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderBeforeFirst()
    {
        $session = $this->sharedFixture['session'];
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $this->node->orderBefore('three', 'one');
        $this->node->addNode('new');
        $this->node->orderBefore('new', 'three');
        $this->assertChildOrder(array('new', 'three', 'one', 'two', 'four'), $this->node);
        $session->save();
        $this->assertChildOrder(array('new', 'three', 'one', 'two', 'four'), $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(array('new', 'three', 'one', 'two', 'four'), $node);
    }

    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderBeforeDown()
    {
        $session = $this->sharedFixture['session'];
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $this->node->orderBefore('two', 'four');
        $this->assertChildOrder(array('one', 'three', 'two', 'four'), $this->node);

        $session->save();
        $this->assertChildOrder(array('one', 'three', 'two', 'four'), $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(array('one', 'three', 'two', 'four'), $node);
    }

    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderBeforeEnd()
    {
        $session = $this->sharedFixture['session'];
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $this->node->orderBefore('two', null);
        $this->assertChildOrder(array('one', 'three', 'four', 'two'), $this->node);

        $session->save();
        $this->assertChildOrder(array('one', 'three', 'four', 'two'), $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(array('one', 'three', 'four', 'two'), $node);
    }

    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderBeforeNoop()
    {
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $this->node->orderBefore('two', 'three');
        $this->assertChildOrder(array('one', 'two', 'three', 'four'), $this->node);

        $session = $this->saveAndRenewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(array('one', 'two', 'three', 'four'), $node);
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

    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderBeforeSwap()
    {
        $session = $this->sharedFixture['session'];
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $this->node->orderBefore('four', 'two');
        $this->assertChildOrder(array('one', 'four', 'two', 'three', 'five'), $this->node);
        $this->node->orderBefore('two', 'one');
        $this->assertChildOrder(array('two', 'one', 'four', 'three', 'five'), $this->node);

        $session->save();
        $this->assertChildOrder(array('two', 'one', 'four', 'three', 'five'), $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(array('two', 'one', 'four', 'three', 'five'), $node);
    }

    /**
     * Test reordering and adding a node and removing another one
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderBeforeUpAndDelete()
    {
        $session = $this->sharedFixture['session'];
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $this->node->addNode('new1');
        $this->node->addNode('new2');
        $this->node->getNode('three')->remove();
        $this->node->orderBefore('four', 'two');
        $this->node->orderBefore('new1', 'two');
        $this->node->orderBefore('new2', 'two');
        $this->node->getNode('one')->remove();
        $this->assertChildOrder(array('four', 'new1', 'new2', 'two'), $this->node);

        $session->save();
        $this->assertChildOrder(array('four', 'new1', 'new2', 'two'), $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(array('four', 'new1', 'new2', 'two'), $node);
    }

    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderBeforeUpAndRefresh()
    {
        $session = $this->sharedFixture['session'];
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $this->node->orderBefore('three', 'two');
        $this->assertChildOrder(array('one', 'three', 'two'), $this->node);

        $this->assertTrue($session->hasPendingChanges());
        $session->refresh(false);
        $this->assertFalse($this->node->isModified());
        $this->assertFalse($session->hasPendingChanges());

        $this->assertChildOrder(array('one', 'two', 'three'), $this->node);
    }

    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderBeforeUpAndRefreshKeepChanges()
    {
        $session = $this->sharedFixture['session'];
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $othersession = self::$loader->getSession();
        $othernode = $othersession->getNode($path);

        $othernode->addNode('other1');
        $othernode->addNode('other2');
        $othernode->addNode('other-last');
        $othernode->orderBefore('other1', 'one');
        $othernode->orderBefore('other2', 'one');
        $this->assertChildOrder(array('other1', 'other2', 'one', 'two', 'three', 'other-last'), $othernode);
        $othersession->save();
        $this->assertChildOrder(array('other1', 'other2', 'one', 'two', 'three', 'other-last'), $othernode);

        $this->node->addNode('new');
        $this->node->orderBefore('three', 'two');
        $this->node->orderBefore('new', 'three');
        $this->assertChildOrder(array('one', 'new', 'three', 'two'), $this->node);

        $session->refresh(true);

        $this->assertChildOrder(array('other1', 'other2', 'one', 'new', 'three', 'other-last', 'two'), $this->node);
        $session->save();
        $this->assertChildOrder(array('other1', 'other2', 'one', 'new', 'three', 'other-last', 'two'), $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(array('other1', 'other2', 'one', 'new', 'three', 'other-last', 'two'), $node);
    }

    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderBeforeAndDeleteAndRefreshKeepChanges()
    {
        $session = $this->sharedFixture['session'];
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $this->node->addNode('new');
        $this->node->addNode('newfirst');
        $this->node->getNode('three')->remove();
        $this->node->orderBefore('newfirst', 'two');
        $this->node->orderBefore('four', 'two');
        $this->node->getNode('one')->remove();
        $this->assertChildOrder(array('newfirst', 'four', 'two', 'new'), $this->node);

        $session->refresh(true);
        $this->assertChildOrder(array('newfirst', 'four', 'two', 'new'), $this->node);
        $session->save();
        $this->assertChildOrder(array('newfirst', 'four', 'two', 'new'), $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(array('newfirst', 'four', 'two', 'new'), $node);
    }

    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderOnAdd()
    {
        $session = $this->sharedFixture['session'];
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $this->node->addNode('new');
        $this->assertChildOrder(array('one', 'two', 'three', 'new'), $this->node);

        $session->save();
        $this->assertChildOrder(array('one', 'two', 'three', 'new'), $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(array('one', 'two', 'three', 'new'), $node);
    }

    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderOnMultipleAdds()
    {
        $session = $this->sharedFixture['session'];
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $this->node->addNode('new1');
        $this->node->addNode('new2');
        $this->assertChildOrder(array('one', 'two', 'three', 'new1', 'new2'), $this->node);

        $session->save();
        $this->assertChildOrder(array('one', 'two', 'three', 'new1', 'new2'), $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(array('one', 'two', 'three', 'new1', 'new2'), $node);
    }

    /**
     * \PHPCR\NodeInterface::orderBefore
     */
    public function testNodeOrderOnMultipleAddsAndDelete()
    {
        $session = $this->sharedFixture['session'];
        $this->assertInstanceOf('\PHPCR\NodeInterface', $this->node);
        $path = $this->node->getPath();

        $this->node->addNode('new1');
        $this->node->addNode('new2');
        $this->node->getNode('two')->remove();

        $this->assertChildOrder(array('one', 'three', 'new1', 'new2'), $this->node);

        $session->save();
        $this->assertChildOrder(array('one', 'three', 'new1', 'new2'), $this->node);

        $session = $this->renewSession();

        $node = $session->getNode($path);
        $this->assertChildOrder(array('one', 'three', 'new1', 'new2'), $node);
    }
}


