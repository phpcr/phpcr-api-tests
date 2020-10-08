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
use PHPCR\Query\InvalidQueryException;
use PHPCR\Query\QOM\QueryObjectModelFactoryInterface;
use PHPCR\Query\QueryInterface;
use PHPCR\Query\QueryResultInterface;

/**
 * test the Query Factory integration.
 *
 * setLimit, setOffset, bindValue, getBindVariableNames
 *
 * the details of the QOM model are covered in the QOM subfolder
 */
class QueryObjectQOMTest extends QueryBaseCase
{
    /**
     * @var QueryObjectModelFactoryInterface
     */
    protected $factory;

    /**
     * @var QueryInterface
     */
    protected $query;

    public function setUp(): void
    {
        parent::setUp();

        try {
            $this->factory = $this->sharedFixture['qm']->getQOMFactory();
        } catch (Exception $e) {
            $this->markTestSkipped('Can not get the QOM factory, skipping tests about QOM query. '.$e->getMessage());
        }

        $source = $this->factory->selector('data', 'nt:folder');
        $constraint = $this->factory->orConstraint(
            $this->factory->descendantNode('data', '/tests_general_base'),
            $this->factory->sameNode('data', '/tests_general_base')
        );
        $orderings = [];
        $columns = [];

        $this->query = $this->factory->createQuery($source, $constraint, $orderings, $columns);
    }

    public function testExecute()
    {
        $qr = $this->query->execute();
        $this->assertInstanceOf(QueryResultInterface::class, $qr);
        $this->assertCount(5, $qr->getRows());
        // we assume content is the same as for sql2
    }

    /**
     * the doc claims there would just be a PHPCR\RepositoryException
     * it makes sense that there is a InvalidQueryException
     */
    public function testExecuteInvalid()
    {
        $this->expectException(InvalidQueryException::class);

        $source = $this->factory->selector('data', 'nonodetype');
        $constraint = null;
        $orderings = [];
        $columns = [];

        $query = $this->factory->createQuery($source, $constraint, $orderings, $columns);
        $query->execute();
    }

    public function testGetStatement()
    {
        $this->assertEquals('SELECT * FROM [nt:folder] AS data '.
            'WHERE (ISDESCENDANTNODE(data, [/tests_general_base]) OR ISSAMENODE(data, [/tests_general_base]))', $this->query->getStatement());
    }

    /**
     * Even though the query is defined with QOM, it must return JCR_SQL2 as langugae:
     * http://www.day.com/specs/jcr/2.0/6_Query.html#6.9.3%20Getting%20the%20Language.
     */
    public function testGetLanguage()
    {
        $this->assertEquals(QueryInterface::JCR_SQL2, $this->query->getLanguage());
    }

    /**
     * a transient query has no stored query path.
     */
    public function testGetStoredQueryPathItemNotFound()
    {
        $this->expectException(ItemNotFoundException::class);

        $this->query->getStoredQueryPath();
    }
    /* this is level 2 only */
    /*
    public function testStoreAsNode()
    {
        $qstr = '//idExample[jcr:mimeType="text/plain"]';
        $query = $this->sharedFixture['qm']->createQuery($qstr, 'xpath');
        $query->storeAsNode('/test_query/queryNode');
        $this->session->save();
    }
    */
    /*
        TODO: trigger the possible exceptions
    */

    // stored queries are tested in SQL2 but can be whatever the implementation wants actually
    // so no need to test them here again
}
