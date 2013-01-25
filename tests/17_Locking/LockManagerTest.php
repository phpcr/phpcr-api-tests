<?php
namespace PHPCR\Tests\Locking;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Tests for the LockManager
 *
 * NOTE: Some of these tests depend on each other. Please see the @ depends
 *  annotations to see how they depend.
 *
 * Covering jcr-2.8.3 spec $17.1
 */
class LockManagerTest extends \PHPCR\Test\BaseCase
{
    /** @var \PHPCR\Lock\LockManagerInterface */
    private $lm;

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
        $this->lm->lock('/non-lockable', true, true, 3, "");
    }

    /**
     * Try to lock an already locked node
     * @expectedException \PHPCR\Lock\LockException
     */
    public function testLockAlreadyLocked()
    {
        $this->recreateTestNode('lockable-node', true);

        // The first lock should work
        try {
            $this->lm->lock('/lockable-node', true, true, 3, "");
        } catch (\PHPCR\Lock\LockException $ex) {
            // The lock didn't work, Huston, there is a problem...
            $this->fail('An error occurred while trying to lock a valid node: ' . $ex->getMessage());
        }

        // The second lock should not work
        $this->lm->lock('/lockable-node', true, true, 3, "");
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
        try {
            $this->lm->lock('/lockable-parent/lockable-child', true, true, 3, "");
        } catch (\PHPCR\Lock\LockException $ex) {
            $this->fail('An error occurred while trying to lock a valid node: ' . $ex->getMessage());
        }

        // The *deep* lock on the parent should not work
        $this->lm->lock('/lockable-parent', true, true, 3, "");
    }

    /**
     * Try to lock a node with non-saved pending changes.
     * @expectedException \PHPCR\InvalidItemStateException
     */
    public function testLockNonSavedNode()
    {
        $node = $this->recreateTestNode('unsaved', true);
        $node->setProperty('testprop', 'foobar');
        $this->lm->lock('/unsaved', true, true, 3, "");
    }

    /**
     * Try to lock an unexisting node
     * @expectedException \PHPCR\PathNotFoundException
     */
    public function testLockNonExistingNode()
    {
        $this->lm->lock('/some-unexisting-node', true, true, 3);
    }

    /**
     * Test a simple lock on a lockable node
     */
    public function testCanLockLockableNodes()
    {
        $node = $this->recreateTestNode('lockable');
        $lock = $this->lm->lock('/lockable', false, true, 3);
        $this->assertNotNull($lock);
        $this->assertLockEquals($lock, $node, 'admin', false, true, 3);
    }

    public function testLockExpire()
    {
        $node = $this->recreateTestNode('lockable-expire');
        $lock = $this->lm->lock('/lockable-expire', false, true, 1);
        $this->assertNotNull($lock);
        $this->assertLockEquals($lock, $node, 'admin', false, true, 1);
        $this->assertTrue($this->lm->isLocked('/lockable-expire'));
        $this->assertTrue($lock->isLive());
        sleep(2);
        $this->assertFalse($this->lm->isLocked('/lockable-expire'));
        $this->assertFalse($lock->isLive());
        $this->assertTrue($lock->getSecondsRemaining() < 0);
    }

    /**
     * @depends testCanLockLockableNodes
     */
    public function testLockReleasedOnLogout()
    {
        $session = self::$loader->getSession();
        $this->recreateTestNode('lockable-logout', true, $session);
        $lm = $session->getWorkspace()->getLockManager();
        $lock = $lm->lock('/lockable-logout', false, true, 3);
        $session->logout();

        $this->assertFalse($this->lm->isLocked('/lockable-logout'), 'logout did not release session lock');
        $this->assertFalse($lock->isLive());
    }

    public function testCanLockLockableNodeInfiniteTimeout()
    {
        $node = $this->recreateTestNode('lockable-infinite');
        $lock = $this->lm->lock('/lockable-infinite', false, true, PHP_INT_MAX);
        $this->assertNotNull($lock);
        $this->assertLockEquals($lock, $node, 'admin', false, true, PHP_INT_MAX);
    }

    /**
     * Check that a deep lock locks the children but still the lock is hold by the parent node
     */
    public function testDeepLock()
    {
        $this->recreateTestNode('deep-lock');
        $this->recreateTestNode('deep-lock/child');
        $this->recreateTestNode('deep-lock/child/subchild');
        $this->lm->lock('/deep-lock', true, true, 3);

        $this->assertDeepLock();
    }

    /**
     * Check deep lock with the LockInfo
     */
    public function testDeepLockInfo()
    {
        $this->recreateTestNode('deep-lock');
        $this->recreateTestNode('deep-lock/child');
        $this->recreateTestNode('deep-lock/child/subchild');
        $lockInfo = $this->lm->createLockInfo();
        $lockInfo->setIsDeep(true);
        $lockInfo->setIsSessionScoped(true);
        $this->lm->lockWithInfo('/deep-lock', $lockInfo);

        $this->assertDeepLock();
    }

    private function assertDeepLock()
    {
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
        $lock = $this->lm->lock('/non-deep-lock', false, true, 3);

        $this->assertTrue($this->lm->isLocked('/non-deep-lock'));
        $this->assertFalse($this->lm->isLocked('/non-deep-lock/child'));
        $this->assertFalse($this->lm->isLocked('/non-deep-lock/child/subchild'));

        $this->assertTrue($this->lm->holdsLock('/non-deep-lock'));
        $this->assertFalse($this->lm->holdsLock('/non-deep-lock/child'));
        $this->assertFalse($this->lm->holdsLock('/non-deep-lock/child/subchild'));
    }

    /**
     * Test a simple lock on a lockable node
     */
    public function testLockOwner()
    {
        $node = $this->recreateTestNode('lockable-owner');
        $lock = $this->lm->lock('/lockable-owner', false, true, 3, 'testownerstring');
        $this->assertNotNull($lock);
        $this->assertLockEquals($lock, $node, 'testownerstring', false, true, 3);
    }

    // ----- ISLOCKED TESTS ---------------------------------------------------

    /**
     * Check a locked node is locked
     * @depends testCanLockLockableNodeInfiniteTimeout
     */
    public function testIsLockedOnLocked()
    {
        $this->assertTrue($this->lm->isLocked('/lockable-infinite'));
    }

    /**
     * Check an unlocked node is not locked
     * @depends testCannotLockNonLockableNodes
     */
    public function testIsLockedOnUnlocked()
    {
        $this->assertFalse($this->lm->isLocked('/non-lockable'));
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
     * @depends testCannotLockNonLockableNodes
     */
    public function testHoldsLockOnNonLocked()
    {
        $this->assertFalse($this->lm->holdsLock('/non-lockable'));
    }

    /**
     * @depends testCanLockLockableNodeInfiniteTimeout
     */
    public function testHoldsLockOnLocked()
    {
        $this->assertTrue($this->lm->holdsLock('/lockable-infinite'));
    }

    // ----- UNLOCK TESTS -----------------------------------------------------

    /**
     * Try to unlock a locked node
     * @depends testCanLockLockableNodeInfiniteTimeout
     */
    public function testUnlockOnLocked()
    {
        $this->assertTrue($this->lm->isLocked('/lockable-infinite'));
        $this->lm->unlock('/lockable-infinite');
        $this->assertFalse($this->lm->isLocked('/lockable-infinite'));
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
        $this->lm->lock('/locked-unsaved', true, true, 3, "");
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


    // ----- HELPERS ----------------------------------------------------------

    /**
     * Helper function to simplify the test of valid Lock objects
     * @param \PHPCR\Lock\LockInterface $lock The lock to check
     * @param NodeInterface the expected node of this lock
     * @param string $expectedOwner
     * @param boolean $expectedIsDeep
     * @param boolean $expectedIsSessionScoped
     * @param int $timeout the expected seconds remaining. One second less remaining is accepted too to permit for one second change
     */
    protected function assertLockEquals($lock, $expectedNode, $expectedOwner, $expectedIsDeep, $expectedIsSessionScoped, $timeout)
    {
        $this->assertInstanceOf('\PHPCR\Lock\LockInterface', $lock);
        $this->assertSame($expectedNode, $lock->getNode());
        $this->assertEquals($expectedOwner, $lock->getLockOwner());
        $this->assertEquals($expectedIsDeep, $lock->isDeep());
        $this->assertEquals($expectedIsSessionScoped, $lock->isSessionScoped());
        if (PHP_INT_MAX == $timeout) {
            $this->assertEquals(PHP_INT_MAX, $lock->getSecondsRemaining(), 'Expected infinite timeout');
        } else {
            $remaining = $lock->getSecondsRemaining();
            $this->assertTrue($timeout == $remaining || $timeout - 1 == $remaining, "Timeout does not match, expected $timeout but got $remaining");
        }
    }

    /**
     * Create a test node under the root at the path given in $relPath.
     * If the node already exists, remove it first.
     * If $lockable is true then 'mix:lockable' will be assigned to the node.
     *
     * @param $relPath
     * @param bool $lockable
     */
    protected function recreateTestNode($relPath, $lockable = true, $session = null)
    {
        if (null == $session) {
            $session = $this->sharedFixture['session'];
        }

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
