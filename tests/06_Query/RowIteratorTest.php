<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

//6.6.8 Query API
class Query_RowIteratorTest extends jackalope_baseCase
{
    public $rowIterator;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
    }

    public function setUp()
    {
        parent::setUp();

        $query = $this->sharedFixture['qm']->createQuery("SELECT * FROM [nt:unstructured]", \PHPCR\Query\QueryInterface::JCR_SQL2);
        $this->rowIterator = $query->execute()->getRows();
    }

    public function testIterator()
    {
        $count = 0;

        foreach ($this->rowIterator as $key => $row) {
            $this->assertInstanceOf('Jackalope\Query\Row', $row); // Test if the return element is an istance of row
            $this->assertInstanceOf('Jackalope\Node', $row->getNode()); //Test if we can get the node of a certain row
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
