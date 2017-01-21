<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Query;

use Exception;
use PHPCR\ItemNotFoundException;
use PHPCR\NodeInterface;
use PHPCR\Query\InvalidQueryException;
use PHPCR\Query\QueryInterface;
use PHPCR\Query\QueryResultInterface;

/**
 * test the Query interface. $ 6.9.
 *
 * bindValue, getBindVariableNames
 */
class QueryObjectSql2Test extends QueryBaseCase
{
    protected $simpleQuery = '
            SELECT data.[jcr:mimeType]
            FROM [nt:file] as data
            WHERE data.[jcr:mimeType] = "text/plain"
            ';

    public function testExecute()
    {
        $query = $this->sharedFixture['qm']->createQuery($this->simpleQuery, \PHPCR\Query\QueryInterface::JCR_SQL2);
        $qr = $query->execute();
        $this->assertInstanceOf(QueryResultInterface::class, $qr);
        //content of result is tested in QueryResults
    }

    public function testExecuteLimit()
    {
        $this->query->setLimit(2);
        $qr = $this->query->execute();
        $this->assertInstanceOf(QueryResultInterface::class, $qr);
        $this->assertCount(2, $qr->getRows());
    }

    public function testExecuteOffset()
    {
        $this->query->setOffset(2);
        $qr = $this->query->execute();
        $this->assertInstanceOf(QueryResultInterface::class, $qr);
        $this->assertCount(3, $qr->getRows());
    }

    public function testExecuteLimitAndOffset()
    {
        $this->query->setOffset(2);
        $this->query->setLimit(1);
        $qr = $this->query->execute();
        $this->assertInstanceOf(QueryResultInterface::class, $qr);
        $this->assertCount(1, $qr->getRows());
    }

    /**
     * the doc claims there would just be a PHPCR\RepositoryException
     * it makes sense that there is a InvalidQueryException
     */
    public function testExecuteInvalid()
    {
        $this->expectException(InvalidQueryException::class);

        $query = $this->sharedFixture['qm']->createQuery('this is no sql2 statement', QueryInterface::JCR_SQL2);
        $query->execute();
    }

    public function testGetStatement()
    {
        $query = $this->sharedFixture['qm']->createQuery($this->simpleQuery, QueryInterface::JCR_SQL2);
        $this->assertEquals($this->simpleQuery, $query->getStatement());
    }

    public function testGetLanguage()
    {
        $query = $this->sharedFixture['qm']->createQuery($this->simpleQuery, QueryInterface::JCR_SQL2);
        $this->assertEquals(QueryInterface::JCR_SQL2, $query->getLanguage());
    }

    /**
     * a transient query has no stored query path.
     */
    public function testGetStoredQueryPathItemNotFound()
    {
        $this->expectException(ItemNotFoundException::class);

        $query = $this->sharedFixture['qm']->createQuery($this->simpleQuery, QueryInterface::JCR_SQL2);
        $query->getStoredQueryPath();
    }

    /* this is only with write support only */
    /*
    public function testStoreAsNode()
    {
        $qstr = '//idExample[jcr:mimeType="text/plain"]';
        $query = $this->sharedFixture['qm']->createQuery($qstr, 'xpath');
        $query->storeAsNode('/queryNode');
        $this->session->save();
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
            $qnode = $this->session->getRootNode()->getNode('queryNode');
            $this->assertInstanceOf(NodeInterface::class, $qnode);

            $query = $this->sharedFixture['qm']->getQuery($qnode);
            $this->assertInstanceOf(QueryInterface::class, $query);
            //same as QueryManager::testGetQuery

            $p = $query->getStoredQueryPath();
            $this->assertEquals('/tests_general_query/queryNode', $p);
        } catch (Exception $e) {
            //FIXME: finally?
            $this->sharedFixture['ie']->import('general/base');
            throw $e;
        }
        $this->sharedFixture['ie']->import('read/search/base');
    }
}
