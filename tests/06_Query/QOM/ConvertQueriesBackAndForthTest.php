<?php
namespace PHPCR\Tests\Query\QOM;

require_once(dirname(__FILE__) . '/../../../inc/BaseCase.php');
require_once('Sql2TestQueries.php');
require_once('QomTestQueries.php');

use PHPCR\Util\QOM\Sql2Scanner;
use PHPCR\Util\QOM\Sql2Generator;
use PHPCR\Util\QOM\Sql2ToQomQueryConverter;
use PHPCR\Util\QOM\QomToSql2QueryConverter;

class ConvertQueriesBackAndForthTest extends \PHPCR\Test\BaseCase
{
    protected $sql2Queries;

    protected $qomQueries;

    protected $sql2Parser;

    protected $qomParser;

    public function setUp()
    {
        parent::setUp();

        $factory = $this->sharedFixture['session']->getWorkspace()->getQueryManager()->getQOMFactory();
        $this->sql2Queries = Sql2TestQueries::getQueries();
        $this->qomQueries = QomTestQueries::getQueries($factory);
        $this->qomParser = new QomToSql2QueryConverter(new Sql2Generator());

        try {
            $this->sql2Parser = new Sql2ToQomQueryConverter($factory);
        } catch(\PHPCR\UnsupportedRepositoryException $e) {
            $this->markTestSkipped('Repository does not support the QOM factory');
        }
    }

    public function testBackAndForth()
    {
        foreach ($this->qomQueries as $name => $originalQomQuery) {

            $originalSql2Query = $this->sql2Queries[$name];
            $qom = $this->sql2Parser->parse($originalSql2Query);
            $this->assertEquals($originalQomQuery, $qom, "QOM-->SQL2: Original query = $originalSql2Query");

            $sql2 = $this->qomParser->convert($qom);
            $this->assertEquals($originalSql2Query, $sql2, "SQL2-->QOM: Original query = $originalSql2Query");
        }
    }
}
