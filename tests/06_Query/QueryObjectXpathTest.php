<?php
require_once('QueryBaseCase.php');

/**
 * test the javax.jcr.Query interface
 *
 * setLimit, setOffset, bindValue, getBindVariableNames
 */
class Query_6_QueryObjectXpathTest extends QueryBaseCase
{
    public function setUp()
    {
        //$this->markTestSkipped('TODO: should we add support for xpath again?');
        parent::setUp();
    }
    public function testExecute()
    {
        $query = $this->sharedFixture['qm']->createQuery('//idExample[jcr:mimeType="text/plain"]', 'xpath');
        $qr = $query->execute();
        $this->assertType('PHPCR\Query\QueryResultInterface', $qr);
        //content of result is tested in QueryResults
    }

    /**
     * @expectedException PHPCR\Query\InvalidQueryException
     *
     * the doc claims there would just be a PHPCR\RepositoryException
     * it makes sense that there is a InvalidQueryException
     */
    public function testExecuteInvalid()
    {
        $query = $this->sharedFixture['qm']->createQuery('this is no xpath statement', 'xpath');
        $qr = $query->execute();
    }

    public function testGetStatement()
    {
        $qstr = '//idExample[jcr:mimeType="text/plain"]';
        $query = $this->sharedFixture['qm']->createQuery($qstr, 'xpath');
        $this->assertEquals($qstr, $query->getStatement());
    }
    public function testGetLanguage()
    {
        $qstr = '//idExample[jcr:mimeType="text/plain"]';
        $query = $this->sharedFixture['qm']->createQuery($qstr, 'xpath');
        $this->assertEquals('xpath', $query->getLanguage());
    }
    /**
     * a transient query has no stored query path
     * @expectedException PHPCR\ItemNotFoundException
     */
    public function testGetStoredQueryPathItemNotFound()
    {
        $qstr = '//idExample[jcr:mimeType="text/plain"]';
        $query = $this->sharedFixture['qm']->createQuery($qstr, 'xpath');
        $query->getStoredQueryPath();
    }
    /* this is level 2 only */
    /*
    public function testStoreAsNode()
    {
        $qstr = '//idExample[jcr:mimeType="text/plain"]';
        $query = $this->sharedFixture['qm']->createQuery($qstr, 'xpath');
        $query->storeAsNode('/queryNode');
        $this->sharedFixture['session']->save();
    }
    */
    /*
    +diverse exceptions
    */

    /** changes repository state */
    public function testGetStoredQueryPath()
    {
        $this->sharedFixture['ie']->import('general/query');
        try {
            $qnode = $this->sharedFixture['session']->getRootNode()->getNode('queryNode');
            $this->assertType('PHPCR\NodeInterface', $qnode);

            $query = $this->sharedFixture['qm']->getQuery($qnode);
            $this->assertType('PHPCR\Query\QueryInterface', $query);
            //same as QueryManager::testGetQuery

            $p = $query->getStoredQueryPath();
            $this->assertEquals('/queryNode', $p);
        } catch(exception $e) {
            //FIXME: finally?
            $this->sharedFixture['ie']->import('general/base');
            throw $e;
        }
        $this->sharedFixture['ie']->import('read/search/base');
    }

}
