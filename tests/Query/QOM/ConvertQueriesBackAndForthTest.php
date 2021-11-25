<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Query\QOM;

use PHPCR\Query\QOM\QueryObjectModelInterface;
use PHPCR\Test\BaseCase;
use PHPCR\UnsupportedRepositoryOperationException;
use PHPCR\Util\QOM\Sql2Generator;
use PHPCR\Util\QOM\Sql2ToQomQueryConverter;
use PHPCR\Util\QOM\QomToSql2QueryConverter;
use PHPCR\Util\ValueConverter;

class ConvertQueriesBackAndForthTest extends BaseCase
{
    /**
     * @var QueryObjectModelInterface[]
     */
    protected $qomQueries;

    /**
     * @var Sql2ToQomQueryConverter
     */
    protected $sql2Parser;

    /**
     * @var QomToSql2QueryConverter
     */
    protected $qomParser;

    public function setUp(): void
    {
        parent::setUp();

        $factory = $this->session->getWorkspace()->getQueryManager()->getQOMFactory();
        $this->qomQueries = QomTestQueries::getQueries($factory);
        $this->qomParser = new QomToSql2QueryConverter(new Sql2Generator(new ValueConverter()));

        try {
            $this->sql2Parser = new Sql2ToQomQueryConverter($factory);
        } catch (UnsupportedRepositoryOperationException $e) {
            $this->markTestSkipped('Repository does not support the QOM factory');
        }
    }

    public function provideQueries()
    {
        // unfortunately the provider can't create the QOM queries because phpunit calls the data providers before doing setUp/setupBeforeClass
        foreach (Sql2TestQueries::getQueries() as $name => $originalSqlQuery) {
            yield $name => [$name, $originalSqlQuery];
        }
    }

    /**
     * @dataProvider provideQueries
     *
     * @param string          $name
     * @param string|string[] $originalSql2Query
     */
    public function testBackAndForth($name, $originalSql2Query)
    {
        if (!array_key_exists($name, $this->qomQueries)) {
            $this->markTestSkipped('Case '.$name.' needs to be implemented');
        }
        $originalQomQuery = $this->qomQueries[$name];
        if (is_array($originalSql2Query)) {
            $this->assertGreaterThan(0, count($originalSql2Query), 'empty list of queries');
            foreach ($originalSql2Query as $query) {
                $qom = $this->sql2Parser->parse($query);
                if ($originalQomQuery->getStatement() == $qom->getStatement()
                    && $query == $this->qomParser->convert($qom)
                ) {
                    return;
                }
            }
            $this->fail("QOM-->SQL2->QOM: Query variation $name resulted in SQL2 that is not found: ".var_export($originalSql2Query, true));
        }

        $qom = $this->sql2Parser->parse($originalSql2Query);
        $this->assertEquals($originalQomQuery, $qom, "QOM-->SQL2: Original query = $originalSql2Query");
        $sql2 = $this->qomParser->convert($qom);
        $this->assertEquals($originalSql2Query, $sql2, "SQL2-->QOM: Original query = $originalSql2Query");
    }
}
