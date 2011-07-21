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
        $this->assertEquals(8, $count);
    }

    public function testGetNodesAtOnce()
    {
        // This test gets the nodes in one burst (parallel) instead serial like testGetNodes()
        $nodeIterator = $this->qr->getNodes();
        $keys = array();
        $this->markTestSkipped('TODO: this is not part of the api, update test when we decided what should happen');
        $nodes = $nodeIterator->getNodes();
        foreach ($nodes as $path => $node) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $node);
            $this->assertEquals($path, $node->getPath());

            $keys[] = $path;
        }
        $this->assertEquals(8, count($keys));

        $this->assertContains('/tests_general_base/idExample/jcr:content/Test escaping_x0020bla <>\'" node', $keys);
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
        $this->assertEquals(24, $count);
    }

    public function testReadPropertyContentFromResults()
    {
        $nodes = $this->qr->getNodes();
        $seekNodeName = '/tests_general_base/numberPropertyNode/jcr:content';
        $nodes->seek($seekNodeName);
        $node = $nodes->current();

        $this->assertInstanceOf('PHPCR\NodeInterface', $node);

        $prop = $node->getProperty('foo');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals('foo', $prop->getName());
        $this->assertEquals('bar', $prop->getString());

        $prop = $node->getProperty('specialChars');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals('specialChars', $prop->getName());
        $this->assertEquals('üöäøéáñâêèàçæëìíîïþ', $prop->getString());
    }
}
