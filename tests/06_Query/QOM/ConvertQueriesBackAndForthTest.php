<?php
namespace PHPCR\Tests\Query\QOM;

require_once(__DIR__ . '/../../../inc/BaseCase.php');
require_once('Sql2TestQueries.php');
require_once('QomTestQueries.php');

use PHPCR\Util\QOM\Sql2Generator;
use PHPCR\Util\QOM\Sql2ToQomQueryConverter;
use PHPCR\Util\QOM\QomToSql2QueryConverter;

class ConvertQueriesBackAndForthTest extends \PHPCR\Test\BaseCase
{
    /**
     * @var string[]
     */
    protected $sql2Queries;

    /**
     * @var \PHPCR\Query\QOM\QueryObjectModelInterface[]
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

    public function setUp()
    {
        parent::setUp();

        $factory = $this->sharedFixture['session']->getWorkspace()->getQueryManager()->getQOMFactory();
        $this->sql2Queries = Sql2TestQueries::getQueries();
        $this->qomQueries = QomTestQueries::getQueries($factory);
        $this->qomParser = new QomToSql2QueryConverter(new Sql2Generator());

        try {
            $this->sql2Parser = new Sql2ToQomQueryConverter($factory);
        } catch(\PHPCR\UnsupportedRepositoryOperationException $e) {
            $this->markTestSkipped('Repository does not support the QOM factory');
        }
    }

    public function testBackAndForth()
    {
        foreach ($this->qomQueries as $name => $originalQomQuery) {
            $originalSql2Query = $this->sql2Queries[$name];
            if (is_array($originalSql2Query)) {
                $passed = false;
                foreach ($originalSql2Query as $query) {
                    $qom = $this->sql2Parser->parse($query);
                    if ($originalQomQuery->getStatement() == $qom->getStatement()) {
                        $sql2 = $this->qomParser->convert($qom);
                        if ($sql2 == $query) {
                            $passed = true;
                            break;
                        }
                    }
                }
                $this->assertTrue($passed, "QOM-->SQL2->QOM: Original query variation = ".$query);
            } else {
                $qom = $this->sql2Parser->parse($originalSql2Query);
                $this->assertEquals($originalQomQuery, $qom, "QOM-->SQL2: Original query = $originalSql2Query");
                $sql2 = $this->qomParser->convert($qom);
                $this->assertEquals($originalSql2Query, $sql2, "SQL2-->QOM: Original query = $originalSql2Query");
            }
        }
    }
}
