<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.6.8 Query API
class jackalope_tests_read_SearchTest_QueryResults extends jackalope_baseCase {
    public static $expect = array("jcr:createdBy","jcr:created","jcr:primaryType","jcr:path","jcr:score");
    public $query;

    public function setUp() {
        //FIXME: xpath is depricated. should test SQL2 (and QOM?)
        $this->query = $this->sharedFixture['qm']->createQuery('//element(*, nt:folder)', 'xpath');
        $this->qr = $this->query->execute();
        //sanity check
        $this->assertTrue(is_object($this->qr));
        $this->assertTrue($this->qr instanceof PHPCR_Query_QueryResultInterface);
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

        $this->assertTrue(is_object($ret));
        $this->assertTrue($ret instanceof PHPCR_Query_RowIteratorInterface);

        $exptsize = $ret->getSize();
        $num = 0;
        foreach($ret as $row) {
            $num++;
            $this->assertTrue($row instanceof PHPCR_Query_RowInterface);
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

        $this->assertTrue(is_object($ret));
        $this->assertTrue($ret instanceof PHPCR_NodeIteratorInterface);
        $exptsize = $ret->getSize();
        $num = 0;
        foreach($ret as $node) {
            $num++;
            $this->assertTrue($node instanceof PHPCR_NodeInterface);
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
