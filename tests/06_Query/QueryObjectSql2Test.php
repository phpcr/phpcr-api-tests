<?php
namespace PHPCR\Tests\Query;

require_once('QueryBaseCase.php');

/**
 * test the Query interface. $ 6.9
 *
 * setLimit, setOffset, bindValue, getBindVariableNames
 */
class QueryObjectSql2Test extends QueryBaseCase
{
    protected $simpleQuery = '
            SELECT data.[jcr:mimeType]
            FROM [nt:file] as data
            WHERE data.[jcr:mimeType] = "text/plain"
            ';

    public function setUp()
    {
        parent::setUp();
    }

    public function testExecute()
    {
        $query = $this->sharedFixture['qm']->createQuery($this->simpleQuery, \PHPCR\Query\QueryInterface::JCR_SQL2);
        $qr = $query->execute();
        $this->assertInstanceOf('PHPCR\Query\QueryResultInterface', $qr);
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
        $query = $this->sharedFixture['qm']->createQuery('this is no sql2 statement', \PHPCR\Query\QueryInterface::JCR_SQL2);
        $qr = $query->execute();
    }

    public function testGetStatement()
    {
        $query = $this->sharedFixture['qm']->createQuery($this->simpleQuery, \PHPCR\Query\QueryInterface::JCR_SQL2);
        $this->assertEquals($this->simpleQuery, $query->getStatement());
    }

    public function testGetLanguage()
    {
        $query = $this->sharedFixture['qm']->createQuery($this->simpleQuery, \PHPCR\Query\QueryInterface::JCR_SQL2);
        $this->assertEquals(\PHPCR\Query\QueryInterface::JCR_SQL2, $query->getLanguage());
    }

    /**
     * a transient query has no stored query path
     * @expectedException PHPCR\ItemNotFoundException
     */
    public function testGetStoredQueryPathItemNotFound()
    {
        $query = $this->sharedFixture['qm']->createQuery($this->simpleQuery, \PHPCR\Query\QueryInterface::JCR_SQL2);
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
        TODO: trigger the possible exceptions
    */

    /** changes fixtures */
    public function testGetStoredQueryPath()
    {
        $this->sharedFixture['ie']->import('general/query');
        try {
            $qnode = $this->sharedFixture['session']->getRootNode()->getNode('queryNode');
            $this->assertInstanceOf('PHPCR\NodeInterface', $qnode);

            $query = $this->sharedFixture['qm']->getQuery($qnode);
            $this->assertInstanceOf('PHPCR\Query\QueryInterface', $query);
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
