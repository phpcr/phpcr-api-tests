<?php
namespace PHPCR\Tests\Transactions;


use PHPCR\RepositoryInterface;
use \PHPCR\Transaction;

/**
 * Covering jcr-283 spec $10.4
 */
class TransactionMethodsTest extends \PHPCR\Test\BaseCase
{

    public static function setupBeforeClass($fixtures = '21_Transactions/transactions')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        $this->renewSession();
        parent::setUp();
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node, "Something went wrong with fixture loading");
    }

    public function testGetTransactionManager()
    {
        $utx = $this->session->getWorkspace()->getTransactionManager();

        $this->assertInstanceOf('\PHPCR\Transaction\UserTransactionInterface', $utx);
    }

    public function testTransactionCommit()
    {
        $utx = $this->session->getWorkspace()->getTransactionManager();

        $utx->begin();
        $child = $this->node->addNode('insideTransaction');

        $this->assertEquals($this->node->getPath() . '/insideTransaction', $child->getPath());

        $this->session->save();

        $sessionbeforesave = self::$loader->getSession();
        $this->assertFalse($sessionbeforesave->nodeExists($child->getPath()));

        $utx->commit();

        //do not refresh session, as this functionality could be broken... create a new session
        $sessionaftersave = self::$loader->getSession();
        $this->assertTrue($sessionaftersave->nodeExists($child->getPath()));
    }

    public function testTransactionRollback()
    {
        $copy = $this->node->addNode('copyTransaction');
        $copiedNodePath = $this->node->getPath()."/copyTransactionCopy";
        $this->session->save();

        $utx = $this->session->getWorkspace()->getTransactionManager();

        $child = $this->node->addNode('insideTransaction');
        $utx->begin();
        //workspace operation
        $this->session->getWorkspace()->copy($copy->getPath(),$copiedNodePath);
        $this->session->save();
        $this->assertFalse($child->isNew());
        $utx->rollback();

        $this->assertTrue($this->node->hasNode('insideTransaction'));

        $sessionafterrollback = self::$loader->getSession();
        $this->assertFalse($sessionafterrollback->nodeExists($child->getPath()));
        $this->assertFalse($sessionafterrollback->nodeExists($copiedNodePath));

        // semantics of rollback is that the local session state does not roll back
        // this must work
        $this->session->save();

        $sessionaftersave = self::$loader->getSession();
        $this->assertFalse($sessionaftersave->nodeExists($child->getPath()));
        $this->assertFalse($sessionaftersave->nodeExists($copiedNodePath));
    }

    public function testInTransaction()
    {
        $utx= $this->session->getWorkspace()->getTransactionManager();

        $this->assertFalse($utx->inTransaction());
        $utx->begin();
        $this->node->addNode('insideTransaction0');
        $this->session->save();
        $this->assertTrue($utx->inTransaction());
        $utx->commit();
        $this->assertFalse($utx->inTransaction());

        $utx->begin();
        $this->node->addNode('insideTransaction1');
        $this->session->save();
        $this->assertTrue($utx->inTransaction());
        $utx->rollback();
        $this->assertFalse($utx->inTransaction());
    }

    /**
     * Testing interaction of transactions and versioning
     *
     * @expectedException \PHPCR\InvalidItemStateException
     */
    public function testIllegalCheckin()
    {
        $this->skipIfNotSupported(RepositoryInterface::OPTION_VERSIONING_SUPPORTED);

        $vm = $this->session->getWorkspace()->getVersionManager();

        $utx= $this->session->getWorkspace()->getTransactionManager();
        $vm->checkout($this->node->getPath());
        $this->node->setProperty('foo', 'bar2');

        $utx->begin();
        $this->session->save();

        $vm->checkin($this->node->getPath());
    }

    public function testTransactionTimeout()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

}
