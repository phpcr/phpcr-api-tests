<?php

namespace Jackalope\Tests\QOM;

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');
require_once('Sql2TestQueries.php');


use Jackalope\Query\QOM;
use Jackalope\Query\QOM\Converter\Sql2Scanner;
use Jackalope\Query\QOM\Converter\Sql2ToQomQueryConverter;

class Sql2ParserTest extends \phpcr_suite_baseCase
{
    protected $queries;

    public function setUp() {
        $this->queries = Sql2TestQueries::getQueries();
    }

    public function testColumnsAndSelector()
    {
        $sql2 = $this->queries['6.7.39.Colum.Mixed'];

        $parser = new Sql2ToQomQueryConverter();
        $query = $parser->parse($sql2);

        $this->assertInstanceOf('\Jackalope\Query\QOM\QueryObjectModel', $query);
        $this->assertNull($query->getConstraint());
        $this->assertEmpty($query->getOrderings());
        $this->assertInstanceOf('\PHPCR\Query\QOM\SourceInterface', $query->getSource());
        $this->assertEquals('[nt:unstructured]', $query->getSource()->getNodeTypeName());
        
        $cols = $query->getColumns();
        $this->assertTrue(is_array($cols));
        $this->assertEquals(3, count($cols));

        $this->assertEquals('prop1', $cols[0]->getPropertyName());
        $this->assertNull($cols[0]->getselectorName());
        $this->assertNull($cols[0]->getColumnName());

        $this->assertEquals('prop2', $cols[1]->getPropertyName());
        $this->assertNull($cols[1]->getselectorName());
        $this->assertEquals('col2', $cols[1]->getColumnName());

        $this->assertEquals('prop3', $cols[2]->getPropertyName());
        $this->assertEquals('sel3', $cols[2]->getSelectorName());
        $this->assertEquals('col3', $cols[2]->getColumnName());
    }
}
