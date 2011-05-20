<?php

require_once('QueryBaseCase.php');

/**
 * $ 6.11.1 Row View - Iterator part
 */
class Query_6_RowIteratorTest extends QueryBaseCase
{
    public $rowIterator;

    public function setUp()
    {
        parent::setUp();

        $this->rowIterator = $this->query->execute()->getRows();
        $this->assertEquals(4, count($this->rowIterator));
    }

    public function testIterator()
    {
        $count = 0;

        foreach ($this->rowIterator as $key => $row) {
            $this->assertInstanceOf('PHPCR\Query\RowInterface', $row); // Test if the return element is an istance of row
            $this->assertInstanceOf('PHPCR\NodeInterface', $row->getNode()); //Test if we can get the node of a certain row
            $this->assertEquals(3, count($row->getValues())); // test if we can get all the values of a row

            foreach ($row as $key => $value) { // Test if we can iterate over the columns inside a row
                $count++;
            }
        }

        $this->assertEquals(12, $count);
    }

    public function testSeekable()
    {
        $position = 1;

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
