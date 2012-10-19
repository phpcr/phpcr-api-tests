<?php
namespace PHPCR\Tests\Query\QOM;

require_once(__DIR__ . '/../../../inc/BaseCase.php');
require_once('Sql2TestQueries.php');
require_once('QomTestQueries.php');

use PHPCR\Util\QOM\Sql2Scanner;
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

        $factory = $this->sharedFixture['session']->getWorkspace()->getQueryManager()->getQOMFactory();
        $this->sql2Queries = Sql2TestQueries::getQueries();
        $this->qomQueries = QomTestQueries::getQueries($factory);

        try {
            $this->parser = new Sql2ToQomQueryConverter($factory);
        } catch(\PHPCR\UnsupportedRepositoryException $e) {
            $this->markTestSkipped('Repository does not support the QOM factory');
        }
    }

    public function testColumnsAndSelector()
    {
        $sql2 = $this->sql2Queries['6.7.39.Colum.Mixed'];
        $query = $this->parser->parse($sql2);

        $this->assertInstanceOf('\PHPCR\Query\QOM\QueryObjectModelInterface', $query);
        $this->assertNull($query->getConstraint());
        $this->assertEmpty($query->getOrderings());
        $this->assertInstanceOf('\PHPCR\Query\QOM\SourceInterface', $query->getSource());
        $this->assertEquals('nt:unstructured', $query->getSource()->getNodeTypeName());

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
}
