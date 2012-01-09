<?php
namespace PHPCR\Tests\Locking;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
* Tests for the LockManager
*
* Covering jcr-2.8.3 spec $17.1
*/
class LockManagerTest extends \PHPCR\Test\BaseCase
{
    public function setUp()
    {
        parent::setUp();
        $this->lm = $this->sharedFixture['session']->getWorkspace()->getLockManager();
    }

    // ----- LOCK TESTS -------------------------------------------------------

    /**
     * Try to lock a non-lockable node
     * @expectedException \PHPCR\Lock\LockException
     */
    public function testCannotLockNonLockableNodes()
    {
        $this->recreateTestNode('non-lockable', false);
        $this->lm->lock('/non-lockable', true, true, PHP_INT_MAX, "");
    }

    /**
     * Try to lock an already locked node
     * @expectedException \PHPCR\Lock\LockException
     */
    public function testLockAlreadyLocked()
    {
        $this->recreateTestNode('lockable-node', true);

        // The first lock should work
        try
        {
            $this->lm->lock('/lockable-node', true, true, PHP_INT_MAX, "");
        }
        catch (\PHPCR\Lock\LockException $ex)
        {
            // The lock didn't work, Huston, there is a problem...
            $this->fail('An error occurred while trying to lock a valid node: ' . $ex->getMessage());
        }

        // The second lock should not work
        $this->lm->lock('/lockable-node', true, true, PHP_INT_MAX, "");
    }

    /**
     * Try to deep lock a node which subtree contains a locked node
     * @expectedException \PHPCR\Lock\LockException
     */
    public function testLockDeepOnAlreadyLocked()
    {
        $this->recreateTestNode('lockable-parent', true);
        $this->recreateTestNode('lockable-parent/lockable-child', true);

        // The lock on the child should work
        try
        {
            $this->lm->lock('/lockable-parent/lockable-child', true, true, PHP_INT_MAX, "");
        }
        catch (\PHPCR\Lock\LockException $ex)
        {
            $this->fail('An error occurred while trying to lock a valid node: ' . $ex->getMessage());
        }

        // The *deep* lock on the parent should not work
        $this->lm->lock('/lockable-parent', true, true, PHP_INT_MAX, "");
    }

    /**
     * Try to lock a node with non-saved pending changes.
     * @expectedException \PHPCR\InvalidItemStateException
     */
    public function testLockNonSavedNode()
    {
        $node = $this->recreateTestNode('unsaved', true);
        $node->setProperty('testprop', 'foobar');
        $this->lm->lock('/unsaved', true, true, PHP_INT_MAX, "");
    }

    /**
     * Try to lock an unexisting node
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testLockNonExistingNode()
    {
        $this->lm->lock('/some-unexisting-node', true, true, PHP_INT_MAX, "");
    }

    /**
     * Test a simple lock on a lockable node
     */
    public function testCanLockLockableNodes()
    {
        $this->recreateTestNode('lockable');
        $lock = $this->lm->lock('/lockable', false, true, PHP_INT_MAX, "");
        $this->assertNotNull($lock);
        $this->assertLockEquals($lock, 'admin', false, true);
    }

    /**
     * Check that a deep lock locks the children but still the lock is hold by the parent node
     */
    public function testDeepLock()
    {
        $this->recreateTestNode('deep-lock');
        $this->recreateTestNode('deep-lock/child');
        $this->recreateTestNode('deep-lock/child/subchild');
        $lock = $this->lm->lock('/deep-lock', true, true, PHP_INT_MAX, "");

        $this->assertTrue($this->lm->isLocked('/deep-lock'));
        $this->assertTrue($this->lm->isLocked('/deep-lock/child'));
        $this->assertTrue($this->lm->isLocked('/deep-lock/child/subchild'));

        $this->assertTrue($this->lm->holdsLock('/deep-lock'));
        $this->assertFalse($this->lm->holdsLock('/deep-lock/child'));
        $this->assertFalse($this->lm->holdsLock('/deep-lock/child/subchild'));
    }

    /**
     * Check that a non-deep lock does not lock the children
     */
    public function testNonDeepLock()
    {
        $this->recreateTestNode('non-deep-lock');
        $this->recreateTestNode('non-deep-lock/child');
        $this->recreateTestNode('non-deep-lock/child/subchild');
        $lock = $this->lm->lock('/non-deep-lock', false, true, PHP_INT_MAX, "");

        $this->assertTrue($this->lm->isLocked('/non-deep-lock'));
        $this->assertFalse($this->lm->isLocked('/non-deep-lock/child'));
        $this->assertFalse($this->lm->isLocked('/non-deep-lock/child/subchild'));

        $this->assertTrue($this->lm->holdsLock('/non-deep-lock'));
        $this->assertFalse($this->lm->holdsLock('/non-deep-lock/child'));
        $this->assertFalse($this->lm->holdsLock('/non-deep-lock/child/subchild'));
    }

    // ----- ISLOCKED TESTS ---------------------------------------------------

    /**
     * Check a locked node is locked
     * @depends testCanLockLockableNodes
     */
    public function testIsLockedOnLocked()
    {
        $this->assertTrue($this->lm->isLocked('/lockable'));
    }

    /**
     * Check an unlocked node is not locked
     * @depends testCannotLockNonLockableNodes
     */
    public function testIsLockedOnUnlocked()
    {
        $this->assertFalse($this->lm->isLocked('/non-lockable'));
    }

    // ----- UNLOCK TESTS -----------------------------------------------------

    /**
     * Try to unlock a locked node
     * @depends testIsLockedOnLocked
     */
    public function testUnlockOnLocked()
    {
        $this->assertTrue($this->lm->isLocked('/lockable'));
        $this->lm->unlock('/lockable');
        $this->assertFalse($this->lm->isLocked('/lockable'));
    }

    /**
     * Try to unlock a non-lockable node
     * @depends testIsLockedOnUnlocked
     * @expectedException \PHPCR\Lock\LockException
     */
    public function testUnlockOnNonLocked()
    {
        $this->lm->unlock('/non-lockable');
    }

    /**
     * Try to unlock a unsaved node
     * @expectedException \PHPCR\InvalidItemStateException
     */
    public function testUnlockInvalidState()
    {
        $node = $this->recreateTestNode('locked-unsaved', true);
        $this->lm->lock('/locked-unsaved', true, true, PHP_INT_MAX, "");
        $node->setProperty('testprop', 'foobar');
        $this->lm->unlock('/locked-unsaved');
    }

    /**
     * Try to unlock an unexisting node
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testUnlockUnexistingNode()
    {
        $this->lm->unlock('/some-unexisting-node');
    }

    // ----- HOLDSLOCK TESTS --------------------------------------------------

    /**
     * Try to test the lock on an unexisting node
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testHoldsLockUnexistingNode()
    {
        $this->lm->holdsLock('/some-unexisting-node');
    }

    /**
     * @depends testUnlockOnNonLocked
     */
    public function testHoldsLockOnNonLocked()
    {
        $this->assertFalse($this->lm->holdsLock('/non-lockable'));
    }

    /**
     * @depends testUnlockOnLocked
     */
    public function testHoldsLockOnLocked()
    {
        $lock = $this->lm->lock('/lockable', false, true, PHP_INT_MAX, "");
        $this->assertTrue($this->lm->holdsLock('/lockable'));
    }

    // ----- HELPERS ----------------------------------------------------------

    /**
     * Helper function to simplify the test of valid Lock objects
     * @param \PHPCR\Lock\LockInterface $lock The lock to check
     * @param string $expectedOwner
     * @param boolean $expectedIsDeep
     * @param boolean $expectedIsSessionScoped
     */
    protected function assertLockEquals(\PHPCR\Lock\LockInterface $lock, $expectedOwner, $expectedIsDeep, $expectedIsSessionScoped)
    {
        $this->assertInstanceOf('\PHPCR\Lock\LockInterface', $lock);
        $this->assertEquals($expectedOwner, $lock->getLockOwner());
        $this->assertEquals($expectedIsDeep, $lock->isDeep());
        $this->assertEquals($expectedIsSessionScoped, $lock->isSessionScoped());
    }

    /**
     * Create a test node under the root at the path given in $relPath.
     * If the node already exists, remove it first.
     * If $lockable is true then 'mix:lockable' will be assigned to the node.
     *
     * @param $relPath
     * @param bool $lockable
     */
    protected function recreateTestNode($relPath, $lockable = true)
    {
        $session = $this->sharedFixture['session'];
        $root = $session->getRootNode();

        if ($root->hasNode($relPath)) {
            $node = $root->getNode($relPath);
            $node->remove();
            $session->save();
        }

        $node = $root->addNode($relPath);

        if ($lockable) {
            $node->addMixin('mix:lockable');
        }

        $session->save();

        return $node;
    }

}
