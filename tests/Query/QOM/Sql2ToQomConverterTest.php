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

use PHPCR\Query\QOM\ComparisonInterface;
use PHPCR\Query\QOM\LiteralInterface;
use PHPCR\Query\QOM\PropertyValueInterface;
use PHPCR\Query\QOM\QueryObjectModelInterface;
use PHPCR\Query\QOM\SelectorInterface;
use PHPCR\UnsupportedRepositoryOperationException;
use PHPCR\Util\QOM\Sql2ToQomQueryConverter;

class Sql2ToQomConverterTest extends \PHPCR\Test\BaseCase
{
    protected $sql2Queries;

    protected $qomQueries;

    /** @var Sql2ToQomQueryConverter */
    protected $parser;

    public function setUp()
    {
        parent::setUp();

        $factory = $this->session->getWorkspace()->getQueryManager()->getQOMFactory();
        $this->sql2Queries = Sql2TestQueries::getQueries();
        $this->qomQueries = QomTestQueries::getQueries($factory);

        try {
            $this->parser = new Sql2ToQomQueryConverter($factory);
        } catch (UnsupportedRepositoryOperationException $e) {
            $this->markTestSkipped('Repository does not support the QOM factory');
        }
    }

    public function testColumnsAndSelector()
    {
        $sql2 = reset($this->sql2Queries['6.7.39.Colum.Mixed']);
        $query = $this->parser->parse($sql2);

        $this->assertInstanceOf(QueryObjectModelInterface::class, $query);
        $this->assertNull($query->getConstraint());
        $this->assertEmpty($query->getOrderings());
        $this->assertInstanceOf(SelectorInterface::class, $query->getSource());
        $this->assertEquals('nt:unstructured', $query->getSource()->getNodeTypeName());

        $cols = $query->getColumns();
        $this->assertTrue(is_array($cols));
        $this->assertCount(2, $cols);

        $this->assertEquals('u', $cols[0]->getselectorName());
        $this->assertEquals('prop1', $cols[0]->getPropertyName());
        $this->assertEquals('col1', $cols[0]->getColumnName());
        $this->assertEquals('u', $cols[1]->getselectorName());
        $this->assertEquals('prop2', $cols[1]->getPropertyName());
        $this->assertEquals('prop2', $cols[1]->getColumnName());
    }

    public function testQueries()
    {
        foreach ($this->qomQueries as $name => $query) {
            $sql2 = $this->sql2Queries[$name];
            if (is_array($sql2)) {
                foreach ($sql2 as $sql2Variation) {
                    $qom = $this->parser->parse($sql2Variation);
                    $this->assertEquals($query, $qom, "Original query = $sql2Variation");
                }
            } else {
                $qom = $this->parser->parse($sql2);
                $this->assertEquals($query, $qom, "Original query = $sql2");
            }
        }
    }

    public function getSQL2WithWhitespace()
    {
        return [
            ['SELECT * FROM [nt:file] WHERE prop1 = "Foo bar"', 'Foo bar'],
            ['SELECT * FROM [nt:file] WHERE prop1 = "Foo  bar"', 'Foo  bar'],
            ['SELECT * FROM [nt:file] WHERE prop1 = "Foo\tbar"', 'Foo\tbar'],
            ['SELECT * FROM [nt:file] WHERE prop1 = "Foo\n\tbar"', 'Foo\n\tbar'],
            ['SELECT * FROM [nt:file] WHERE prop1 = "Foo \t bar"', 'Foo \t bar'],
            ['SELECT * FROM [nt:file] WHERE prop1 = "Foo \t \n bar"', 'Foo \t \n bar'],
        ];
    }

    /**
     * @dataProvider getSQL2WithWhitespace
     */
    public function testPropertyComparisonWithWhitespace($sql2, $literal)
    {
        $qom = $this->parser->parse($sql2);

        $this->assertInstanceOf(ComparisonInterface::class, $qom->getConstraint());
        $this->assertInstanceOf(PropertyValueInterface::class, $qom->getConstraint()->getOperand1());
        $this->assertInstanceOf(LiteralInterface::class, $qom->getConstraint()->getOperand2());

        $this->assertEquals('prop1', $qom->getConstraint()->getOperand1()->getPropertyName());
        $this->assertEquals($literal, $qom->getConstraint()->getOperand2()->getLiteralValue());
    }
}
