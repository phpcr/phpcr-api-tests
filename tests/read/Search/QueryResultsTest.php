<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.6.8 Query API
class Read_Search_QueryResultsTest extends jackalope_baseCase
{
    public static $expect = array("jcr:createdBy","jcr:created","jcr:primaryType","jcr:path","jcr:score");
    public $query;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
    }

    public function setUp()
    {
        parent::setUp();

        $this->query = $this->sharedFixture['qm']->createQuery("SELECT * FROM [nt:unstructured]", \PHPCR\Query\QueryInterface::JCR_SQL2);
        $this->qr = $this->query->execute();
        //sanity check
        $this->assertType('PHPCR\Query\QueryResultInterface', $this->qr);
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
       $this->assertEquals(3, count($this->qr->getColumnNames()));
    }

    public function testGetRows()
    {
        $count = 0;

        foreach ($this->qr->getRows() as $key => $row) {
            $this->assertType('Jackalope\Query\Row', $row); // Test if the return element is an istance of row
            $this->assertType('Jackalope\Node', $row->getNode()); //Test if we can get the node of a certain row
            $this->assertEquals(3, count($row->getValues())); // test if we can get all the values of a row

            foreach ($row as $key => $value) { // Test if we can iterate over the columns inside a row
                $count++;
            }
        }
        $this->assertEquals(9, $count);
    }

    public function testGetNodes()
    {
        $nodes = $this->qr->getNodes();
        $count = 0;

        foreach ($nodes as $node) {
            // $this->assertType('Jackalope\Node', $node);
            $this->assertType('PHPCR\NodeInterface', $node);
            $count++;
        }
        $this->assertEquals(3, $count);
    }

    public function testGetSelectorNamesEmpty()
    {
        $ret = $this->qr->getSelectorNames();
        $this->assertType('array', $ret);
        $this->assertLessThan(1, count($ret));
    }
    public function testGetSelectorNames()
    {
        $this->markTestSkipped(); //TODO: how to have selector names in result?
    }

    public function testIterateOverQueryResult()
    {
        $count = 0;

        foreach ($this->qr as $key => $row) {
            $this->assertType('Jackalope\Query\Row', $row); // Test if the return element is an istance of row
            $this->assertType('Jackalope\Node', $row->getNode()); //Test if we can get the node of a certain row
            $this->assertEquals(3, count($row->getValues())); // test if we can get all the values of a row

            foreach ($row as $key => $value) { // Test if we can iterate over the columns inside a row
                $count++;
            }
        }
        $this->assertEquals(9, $count);
    }
}
