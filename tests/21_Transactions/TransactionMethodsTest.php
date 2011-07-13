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
        $utx = $session->getTransactionManager();

        $this->assertInstanceOf('\PHPCR\Transaction\UserTransactionInterface', $utx);
    }

    public function testTransactionCommit()
    {
        $session = self::$staticSharedFixture['session'];
        $utx = $session->getTransactionManager();

        $utx->begin();
        $this->node->addNode('insideTransaction');
        $utx->commit();

        $node = $this->node->getNode('insideTransaction');
        $this->assertEquals('/tests_transactions_base/testTransactionCommit/insideTransaction', $node->getPath());
    }

    public function testTransactionRollback()
    {
        $session = self::$staticSharedFixture['session'];
        $utx= $session->getTransactionManager();

        $utx->begin();
        $this->node->addNode('insideTransaction');
        $session->save();
        $utx->rollback();

        $this->assertFalse($this->node->hasNode('insideTransaction'));
        $this->setExpectedException('\PHPCR\PathNotFoundException');
        $node = $this->node->getNode('insideTransaction');
    }

    public function testInTransaction()
    {
        $session = self::$staticSharedFixture['session'];
        $utx= $session->getTransactionManager();

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

    public function testTransactionTimeout() {
        $this->markTestIncomplete('This test has not been implemented yet.'); 
    }
}
