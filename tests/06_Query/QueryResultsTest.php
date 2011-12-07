<?php
namespace PHPCR\Tests\Query;

require_once('QueryBaseCase.php');

/**
 * $ 6.11 QueryResult - Test the query result object
 */
class QueryResultsTest extends QueryBaseCase
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
        sort($columnNames); //order is not determined
        $columnNamesExpected = array('jcr:path', 'jcr:score', 'nt:folder.jcr:created', 'nt:folder.jcr:createdBy', 'nt:folder.jcr:primaryType');

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
        $this->assertEquals(5, $count);
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
        $selectorNamesExpected = array('nt:folder');

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
        /* Since we query nt:folder, We should expect up to 5 values for 5 columns:
         * 1. jcr:primaryType - mandatory property derived from nt:base
         * 2. jcr:created - autocreated property derived from mix:created via nt:hierarchyNode
         * 3. jcr:createdBy - autocreated property derived from mix:created via nt:hierarchyNode
         * 4. jcr:path - mandatory column in result
         * 5. jcr:score - mandatory column in result
         *
         * It's up to the implementation if mixin properties are returned from query, 
         * so jcr:created and jcr:createdBy are not mandatory columns in result. 
         */
        $columns = $this->qr->getColumnNames();
        if (in_array('nt:folder.jcr:created', $columns)
            && in_array('nt:folder.jcr:createdBy', $columns)) {
            $this->assertEquals(25, $count); /* Result contains mixin properties */
        } else {
            $this->assertEquals(15, $count); /* Result contains mandatory primaryType, path and score */
        }
    }

    public function testReadPropertyContentFromResults()
    {
        $seekName = '/tests_general_base/multiValueProperty';
        foreach ($this->qr->getNodes() as $path => $node) {
            if ($seekName == $path) break;
        }

        $this->assertInstanceOf('PHPCR\NodeInterface', $node);

        $prop = $node->getProperty('jcr:uuid');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals('jcr:uuid', $prop->getName());
        $this->assertEquals('14e18ef3-be20-4985-bee9-7bb4763b31de', $prop->getString());
    }
}
