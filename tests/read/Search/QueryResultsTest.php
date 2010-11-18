<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.6.8 Query API
class Read_Search_QueryResultsTest extends jackalope_baseCase {
    public static $expect = array("jcr:createdBy","jcr:created","jcr:primaryType","jcr:path","jcr:score");
    public $query;

    public static function setupBeforeClass() {
        parent::setupBeforeClass();
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
    }

    public function setUp() {
        parent::setUp();

        //FIXME: xpath is depricated. should test SQL2 (and QOM?)
        $this->query = $this->sharedFixture['qm']->createQuery('//element(*, nt:folder)', 'xpath');
        $this->qr = $this->query->execute();
        //sanity check
        $this->assertType('PHPCR\Query\QueryResultInterface', $this->qr);
    }

    public function testBindValue() {
        $this->markTestSkipped(); //TODO: test with a SQL2 query
    }
    public function testGetBindVariableNames() {
        $this->markTestSkipped(); //TODO: test with a SQL2 query
    }
    public function testGetBindVariableNamesEmpty() {
        $ret = $this->query->getBindVariableNames();
        $this->assertType('array', $ret);
        $this->assertLessThan(1, count($ret));
    }

    public function testGetColumnNames() {
        $ret = $this->qr->getColumnNames();
        $this->assertType('array', $ret);

        //the fields seem to depend on the node type we filtered for. todo: the field names might be implementation specific

        $this->assertEquals(self::$expect, $ret);
    }

    public function testGetRows() {
        $ret = $this->qr->getRows();

        $this->assertType('PHPCR\Query\RowIteratorInterface', $ret);

        $exptsize = $ret->getSize();
        $num = 0;
        foreach($ret as $row) {
            $num++;
            $this->assertType('PHPCR\Query\RowInterface', $row);
        }

        $this->assertEquals($exptsize, $num);
        //further tests in Row.php
    }
    /**
     * @expectedException OutOfBoundsException
     */
    public function testGetRowsNoSuchElement() {
        $ret = $this->qr->getRows();
        while($row = $ret->nextRow()); //just retrieve until after the last
    }

    public function testGetNodes() {
        $ret = $this->qr->getNodes();

        $this->assertType('PHPCR\NodeIteratorInterface', $ret);
        $exptsize = $ret->getSize();
        $num = 0;
        foreach($ret as $node) {
            $num++;
            $this->assertType('PHPCR\NodeInterface', $node);
        }
        $this->assertEquals($exptsize, $num);
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testGetNodesNoSuchElement() {
        $ret = $this->qr->getNodes();
        while($row = $ret->nextNode()); //just retrieve after the last
    }

    public function testGetSelectorNamesEmpty() {
        $ret = $this->qr->getSelectorNames();
        $this->assertType('array', $ret);
        $this->assertLessThan(1, count($ret));
    }
    public function testGetSelectorNames() {
        $this->markTestSkipped(); //TODO: how to have selector names in result?
    }
}
