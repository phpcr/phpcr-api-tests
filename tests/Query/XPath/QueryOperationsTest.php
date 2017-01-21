<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Query\XPath;

use PHPCR\Query\QueryInterface;
use PHPCR\Query\QueryResultInterface;

/**
 * Run non-trivial queries to try out where, the join features and such.
 */
class QueryOperationsTest extends QueryBaseCase
{
    public function testQueryField()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery(
            '/jcr:root/tests_general_base//element(*,nt:unstructured)[@foo = "bar"]/@foo',
            QueryInterface::XPATH
        );

        $this->assertInstanceOf(QueryInterface::class, $query);
        $result = $query->execute();
        $this->assertInstanceOf(QueryResultInterface::class, $result);
        $vals = [];

        foreach ($result->getNodes() as $node) {
            $vals[] = $node->getPropertyValue('foo');
        }
        $this->assertEquals(['bar'], $vals);

        $vals = [];

        foreach ($result->getRows() as $row) {
            $vals[] = $row->getValue('foo');
        }

        $this->assertEquals(['bar'], $vals);
    }

    public function testQueryFieldSomenull()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery(
            '/jcr:root/tests_general_base//element(*,nt:unstructured)/@foo',
            QueryInterface::XPATH
        );

        $this->assertInstanceOf(QueryInterface::class, $query);
        $result = $query->execute();
        $this->assertInstanceOf(QueryResultInterface::class, $result);
        $vals = [];

        foreach ($result->getNodes() as $node) {
            $vals[] = ($node->hasProperty('foo') ? $node->getPropertyValue('foo') : null);
        }

        $this->assertContains('bar', $vals);
        $this->assertCount(10, $vals);

        $vals = [];

        foreach ($result->getRows() as $row) {
            $vals[] = $row->getValue('foo');
        }

        $this->assertContains('bar', $vals);
        $this->assertCount(10, $vals);
    }

    public function testQueryOrder()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery(
            '/jcr:root/tests_general_base//element(*, nt:unstructured)/@zeronumber order by @zeronumber',
            QueryInterface::XPATH
        );

        $this->assertInstanceOf(QueryInterface::class, $query);
        $result = $query->execute();
        $this->assertInstanceOf(QueryResultInterface::class, $result);

        $vals = [];

        foreach ($result->getRows() as $row) {
            $vals[] = $row->getValue('zeronumber');
        }

        // rows that do not have that field are null. empty is before fields with values
        $this->assertEquals([null, null, null, null, null, null, null, null, null, 0], $vals);
    }
}
