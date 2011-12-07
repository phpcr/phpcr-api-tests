<?php
namespace PHPCR\Tests\Query;

require_once('QueryBaseCase.php');

/**
 * $ 6.11.1 Row View - Iterator part
 */
class RowIteratorTest extends QueryBaseCase
{
    public $rowIterator;

    public function setUp()
    {
        parent::setUp();

        $this->rowIterator = $this->query->execute()->getRows();
        $this->assertEquals(5, count($this->rowIterator));
    }

    public function testIterator()
    {
        $count = 0;

        foreach ($this->rowIterator as $key => $row) {
            $this->assertInstanceOf('PHPCR\Query\RowInterface', $row); // Test if the return element is an istance of row
            $this->assertInstanceOf('PHPCR\NodeInterface', $row->getNode()); //Test if we can get the node of a certain row

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
            $this->assertNotEmpty($row->getValue('jcr:primaryType', 'Empty value of jcr:primaryType'));  
            $this->assertNotEmpty($row->getValue('jcr:path', 'Empty value of jcr:path'));  
            $this->assertNotEmpty($row->getValue('jcr:score', 'Empty value of jcr:score'));  

            $nValues = count($row->getValues());

            foreach ($row as $key => $value) { // Test if we can iterate over the columns inside a row
                $count++;
            }
        }

        if ($nValues == 5) {
            $this->assertEquals(25, $count); /* Result contains mixin properties */
        } else {
            $this->assertEquals(15, $count); /* Result contains mandatory primaryType,path and score */
        }
    }

    public function testSeekable()
    {
        $position = 2;

        $rows = array();
        foreach ($this->rowIterator as $row) {
            $rows[] = $row;
        }

        $this->rowIterator->seek($position);

        $this->assertEquals($rows[$position], $this->rowIterator->current());
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testSeekableOutOfBounds()
    {
        $position = -1;

        $this->rowIterator->seek($position);
    }

    public function testCountable()
    {
        $rows = array();
        foreach ($this->rowIterator as $row) {
            $rows[] = $row;
        }

        $this->assertEquals(count($rows), $this->rowIterator->count());
    }
}
