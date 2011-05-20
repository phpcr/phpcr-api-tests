<?php
require_once('QueryBaseCase.php');

/**
 * $ 6.11 QueryResult - Test the query result object
 */
class Query_6_QueryResultsTest extends QueryBaseCase
{
    public static $expect = array("jcr:createdBy","jcr:created","jcr:primaryType","jcr:path","jcr:score");

    public function setUp()
    {
        parent::setUp();

        $this->qr = $this->query->execute();
        //sanity check
        $this->assertInstanceOf('PHPCR\Query\QueryResultInterface', $this->qr);
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
        $columnNames = $this->qr->getColumnNames();
        $columnNamesExpected = array('nt:unstructured.jcr:primaryType', 'jcr:path', 'jcr:score');

       $this->assertEquals($columnNamesExpected, $columnNames);
    }

    public function testGetNodes()
    {
        $nodes = $this->qr->getNodes();
        $count = 0;

        foreach ($nodes as $node) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $node);
            $count++;
        }
        $this->assertEquals(4, $count);
    }

    public function testGetSelectorNames()
    {
        $selectorNames = $this->qr->getSelectorNames();
        $selectorNamesExpected = array('nt:unstructured');

        $this->assertEquals($selectorNamesExpected, $selectorNames);
    }

    public function testIterateOverQueryResult()
    {
        $count = 0;

        foreach ($this->qr as $key => $row) {
            $this->assertInstanceOf('PHPCR\Query\RowInterface', $row); // Test if the return element is an instance of row

            foreach ($row as $columnName => $value) { // Test if we can iterate over the columns inside a row
                $count++;
            }
        }
        $this->assertEquals(12, $count);
    }

    public function testReadPropertyContentFromResults()
    {
        $nodes = $this->qr->getNodes();
        $seekNodeName = '/tests_general_base/numberPropertyNode/jcr:content';
        $nodes->seek($seekNodeName);
        $node = $nodes->current();

        $this->assertType('PHPCR\NodeInterface', $node);

        $prop = $node->getProperty('foo');
        $this->assertType('PHPCR\PropertyInterface', $prop);
        $this->assertEquals($prop->getName(), 'foo');
        $this->assertEquals($prop->getString(), 'bar');

        $prop = $node->getProperty('specialChars');
        $this->assertType('PHPCR\PropertyInterface', $prop);
        $this->assertEquals($prop->getName(), 'specialChars');
        $this->assertEquals($prop->getString(), 'üöäøéáñâêèàçæëìíîïþ');
    }
}
