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

use PHPCR\NodeInterface;
use PHPCR\Query\InvalidQueryException;
use PHPCR\Query\QOM\QueryObjectModelFactoryInterface;
use PHPCR\Query\QueryInterface;

/**
 * tests for the query manager, $ 6.8.
 *
 * TODO: getQOMFactory
 */
class QueryManagerTest extends QueryBaseCase
{
    public static function setupBeforeClass($fixture = 'general/query'): void
    {
        parent::setupBeforeClass($fixture);
    }

    public function testCreateQuerySql2()
    {
        $ret = $this->sharedFixture['qm']->createQuery("SELECT * FROM [nt:folder] WHERE ISCHILDNODE('/tests_general/base')", \PHPCR\Query\QueryInterface::JCR_SQL2);
        $this->assertInstanceOf(QueryInterface::class, $ret);
    }

    public function testCreateQueryInvalid()
    {
        $this->expectException(InvalidQueryException::class);

        $this->sharedFixture['qm']->createQuery(null, 'some-not-existing-query-language');
    }

    public function testGetQuery()
    {
        $qnode = $this->session->getNode('/tests_general_query/queryNode');
        $this->assertInstanceOf(NodeInterface::class, $qnode);

        $query = $this->sharedFixture['qm']->getQuery($qnode);
        $this->assertInstanceOf(QueryInterface::class, $query);
    }

    public function testGetQueryInvalid()
    {
        $this->expectException(InvalidQueryException::class);

        $this->sharedFixture['qm']->getQuery($this->rootNode);
    }

    public function testGetQOMFactory()
    {
        $factory = $this->sharedFixture['qm']->getQOMFactory();
        $this->assertInstanceOf(QueryObjectModelFactoryInterface::class, $factory);
    }

    public function testGetSupportedQueryLanguages()
    {
        $ret = $this->sharedFixture['qm']->getSupportedQueryLanguages();
        $this->assertIsArray($ret);
        $this->assertContains('JCR-SQL2', $ret);
        $this->assertContains('JCR-JQOM', $ret);
    }
}
