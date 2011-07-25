<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

use \PHPCR\PropertyType as Type;
use \PHPCR\Transaction;

/**
 * Covering jcr-283 spec $10.4
 */
class Transactions_21_TransactionMethodsTest extends phpcr_suite_baseCase
{

    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('21_Transactions/transactions');
    }

    public function setUp()
    {
        $this->renewSession();
        parent::setUp();
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node, "Something went wrong with fixture loading");
    }

    public function testGetTransactionManager()
    {
        $session = self::$staticSharedFixture['session'];
        $utx = $session->getWorkspace()->getTransactionManager();

        $this->assertInstanceOf('\PHPCR\Transaction\UserTransactionInterface', $utx);
    }

    public function testTransactionCommit()
    {
        $session = self::$staticSharedFixture['session'];
        $utx = $session->getWorkspace()->getTransactionManager();

        $utx->begin();
        $child = $this->node->addNode('insideTransaction');

        $this->assertEquals($this->node->getPath() . '/insideTransaction', $child->getPath());

        $session->save();

        $sessionbeforesave = getPHPCRSession(self::$staticSharedFixture['config']);
        $this->assertFalse($sessionbeforesave->nodeExists($child->getPath()));

        $utx->commit();

        //do not refresh session, as this functionality could be broken... create a new session
        $sessionaftersave = getPHPCRSession(self::$staticSharedFixture['config']);
        $this->assertTrue($sessionaftersave->nodeExists($child->getPath()));
    }

    public function testTransactionRollback()
    {
        $session = self::$staticSharedFixture['session'];
        $utx = $session->getWorkspace()->getTransactionManager();

        $utx->begin();
        $child = $this->node->addNode('insideTransaction');
        $session->save();
        $utx->rollback();

        $this->assertTrue($this->node->hasNode('insideTransaction'));

        $sessionaftersave = getPHPCRSession(self::$staticSharedFixture['config']);
        $this->assertFalse($sessionaftersave->nodeExists($child->getPath()));
    }

    public function testInTransaction()
    {
        $session = self::$staticSharedFixture['session'];
        $utx= $session->getWorkspace()->getTransactionManager();

        $this->assertFalse($utx->inTransaction());
        $utx->begin();
        $this->node->addNode('insideTransaction0');
        $session->save();
        $this->assertTrue($utx->inTransaction());
        $utx->commit();
        $this->assertFalse($utx->inTransaction());

        $utx->begin();
        $this->node->addNode('insideTransaction1');
        $session->save();
        $this->assertTrue($utx->inTransaction());
        $utx->rollback();
        $this->assertFalse($utx->inTransaction());
    }

    /**
     * @expectedException PHPCR\InvalidItemStateException
     */
    public function testIllegalCheckin()
    {
        $session = self::$staticSharedFixture['session'];
        try {
            $vm = $session->getWorkspace()->getVersionManager();
        } catch (\PHPCR\UnsupportedRepositoryOperationException $e) {
            $this->markTestSkipped("Versioning not supported: " . $e->getMessage());
        }

        $utx= $session->getWorkspace()->getTransactionManager();
        $vm->checkout($this->node->getPath());
        $this->node->setProperty('foo', 'bar2');

        $utx->begin();
        $session->save();

        $vm->checkin($this->node->getPath());
    }

    public function testTransactionTimeout()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

}
