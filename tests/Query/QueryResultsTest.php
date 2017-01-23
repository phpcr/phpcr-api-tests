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
use PHPCR\PropertyInterface;
use PHPCR\Query\QueryInterface;
use PHPCR\Query\QueryResultInterface;
use PHPCR\Query\RowInterface;

/**
 * $ 6.11 QueryResult - Test the query result object.
 */
class QueryResultsTest extends QueryBaseCase
{
    /** @var QueryResultInterface */
    protected $qr;

    public static $expect = [
        'jcr:createdBy',
        'jcr:created',
        'jcr:primaryType',
        'jcr:path',
        'jcr:score'
    ];

    public function setUp()
    {
        parent::setUp();

        $this->qr = $this->query->execute();
        // Sanity check
        $this->assertInstanceOf(QueryResultInterface::class, $this->qr);
    }

    public function testBindValue()
    {
        $this->markTestSkipped(); //TODO: test with a SQL2 query
    }

    public function testGetBindVariableNames()
    {
        $this->markTestSkipped(); //TODO: test with a SQL2 query
    }

    public function testGetBindVariableNamesEmpty()
    {
        $this->markTestSkipped(); //TODO: test with a SQL2 query
    }

    public function testGetColumnNames()
    {
        $columnNames = $this->qr->getColumnNames();
        sort($columnNames); //order is not determined
        $columnNamesExpected = ['nt:folder.jcr:created', 'nt:folder.jcr:createdBy', 'nt:folder.jcr:primaryType'];

        $this->assertEquals($columnNamesExpected, $columnNames);
    }

    public function testGetAliasColumnNames()
    {
        $query = $this->sharedFixture['qm']->createQuery('
            SELECT [jcr:mimeType] AS bar, stringToCompare as foo, [nt:unstructured].longNumberToCompare, ding
            FROM [nt:unstructured]
            WHERE stringToCompare IS NOT NULL
            ',
            QueryInterface::JCR_SQL2
        );
        $qr = $query->execute();

        $columnNames = $qr->getColumnNames();
        sort($columnNames); //order is not determined
        $columnNamesExpected = ['bar', 'ding', 'foo', 'nt:unstructured.longNumberToCompare'];
        $this->assertEquals($columnNamesExpected, $columnNames);

        foreach ($qr->getRows() as $row) {
            $this->assertNotNull($row->getValue('bar'));
            $this->assertNotNull($row->getValue('foo'));
            $this->assertNotNull($row->getValue('longNumberToCompare'));
            $this->assertEquals('', $row->getValue('ding'));
        }
    }

    public function testGetNodes()
    {
        $nodes = $this->qr->getNodes();
        $count = 0;

        foreach ($nodes as $node) {
            $this->assertInstanceOf(NodeInterface::class, $node);
            $count++;
        }
        $this->assertEquals(5, $count);
    }

    public function testGetNodesAtOnce()
    {
        // This test gets the nodes in one burst (parallel) instead serial like testGetNodes()
        $nodeIterator = $this->qr->getNodes();
        $keys = [];
        $this->markTestSkipped('TODO: this is not part of the api, update test when we decided what should happen');
        $nodes = $nodeIterator->getNodes();
        foreach ($nodes as $path => $node) {
            $this->assertInstanceOf(NodeInterface::class, $node);
            $this->assertEquals($path, $node->getPath());

            $keys[] = $path;
        }
        $this->assertCount(8, $keys);

        $this->assertContains('/tests_general_base/idExample/jcr:content/Test escaping_x0020bla <>\'" node', $keys);
    }

    public function testGetSelectorNames()
    {
        $selectorNames = $this->qr->getSelectorNames();
        $selectorNamesExpected = ['nt:folder'];

        $this->assertEquals($selectorNamesExpected, $selectorNames);
    }

    public function testIterateOverQueryResult()
    {
        $count = 0;

        foreach ($this->qr as $key => $row) {
            $this->assertInstanceOf(RowInterface::class, $row); // Test if the return element is an instance of row

            foreach ($row as $columnName => $value) { // Test if we can iterate over the columns inside a row
                $count++;
            }
        }
        $this->assertEquals(15, $count);
    }

    public function testReadPropertyContentFromResults()
    {
        $seekName = '/tests_general_base/multiValueProperty';

        $this->assertTrue(count($this->qr->getNodes()) > 0);
        foreach ($this->qr->getNodes() as $path => $node) {
            if ($seekName == $path) {
                break;
            }
        }

        $this->assertInstanceOf(NodeInterface::class, $node);

        $prop = $node->getProperty('jcr:uuid');
        $this->assertInstanceOf(PropertyInterface::class, $prop);
        $this->assertEquals('jcr:uuid', $prop->getName());
        $this->assertEquals('14e18ef3-be20-4985-bee9-7bb4763b31de', $prop->getString());
    }

    public function testCompareNumberFields()
    {
        $query = $this->sharedFixture['qm']->createQuery('
            SELECT data.longNumberToCompare
            FROM [nt:unstructured] AS data
            WHERE data.longNumberToCompare > 2
              AND ISDESCENDANTNODE([/tests_general_base])
            ',
            QueryInterface::JCR_SQL2
        );
        $result = $query->execute();

        $rows = [];

        foreach ($result->getRows() as $row) {
            $rows[] = $row;
        }

        $this->assertCount(1, $rows);
        $this->assertEquals(10, $rows[0]->getValue('data.longNumberToCompare'));
    }

    public function testCompareNumberFieldsMulti()
    {
        $query = $this->sharedFixture['qm']->createQuery('
            SELECT data.longNumberToCompareMulti
            FROM [nt:unstructured] AS data
            WHERE data.longNumberToCompareMulti = 2
              AND ISDESCENDANTNODE([/tests_general_base])
            ',
            QueryInterface::JCR_SQL2
        );
        $result = $query->execute();

        $rows = [];

        foreach ($result->getRows() as $row) {
            $rows[] = $row;
        }

        $this->assertCount(1, $rows);
        
        // returning a string value is, perhaps suprisingly, the correct behavior.
        $this->assertEquals('4 2 8', $rows[0]->getValue('data.longNumberToCompareMulti'));
    }


    public function testCompareStringFields()
    {
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT data.stringToCompare FROM [nt:unstructured] AS data WHERE data.stringToCompare > "10"',
            QueryInterface::JCR_SQL2
        );
        $result = $query->execute();

        $rows = [];

        foreach ($result->getRows() as $row) {
            $rows[] = $row;
        }

        $this->assertCount(1, $rows);
        $this->assertEquals(2, $rows[0]->getValue('data.stringToCompare'));
    }

    public function testBooleanField()
    {
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT data.thisIsNo FROM [nt:unstructured] as data WHERE data.thisIsNo = false',
             QueryInterface::JCR_SQL2
        );
        $result = $query->execute();

        $rows = [];

        foreach ($result->getRows() as $row) {
            $rows[] = $row;
        }

        $this->assertCount(1, $rows);
        $this->assertEquals(false, $rows[0]->getValue('data.thisIsNo'));

        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT data.thisIsYes FROM [nt:unstructured] as data WHERE data.thisIsYes = true',
             QueryInterface::JCR_SQL2
        );
        $result = $query->execute();

        $rows = [];

        foreach ($result->getRows() as $row) {
            $rows[] = $row;
        }

        $this->assertCount(1, $rows);
        $this->assertEquals(true, $rows[0]->getValue('data.thisIsYes'));
    }
}
