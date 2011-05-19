<?php

require_once('QueryBaseCase.php');

/**
 * $ 6.11.1 Table View - Row part
 */
class Query_6_RowTest extends QueryBaseCase
{
    private $row;

    public function setUp()
    {
        parent::setUp();

        $rows = $this->query->execute()->getRows();

        $rows->rewind();
        $this->row = $rows->current();

        $this->assertInstanceOf('PHPCR\Query\RowInterface', $this->row);
    }
    public function testIterator()
    {
        $count = 0;

        foreach ($this->row as $name => $value) {
            $this->assertNotNull($name);
            $this->assertNotNull($value);
            $count++;
        }

        $this->assertEquals(3, $count);
    }

    public function testGetValues()
    {
        $values = $this->row->getValues();

        foreach ($values as $value) {
            $this->assertNotNull($value);
        }
    }

    public function testGetValue()
    {
        $this->assertEquals('/', $this->row->getValue('jcr:path'));
    }

    /**
     * @expectedException \PHPCR\ItemNotFoundException
     */
    public function testGetValueItemNotFound()
    {
        $columnName = 'foobar';

        $this->row->getValue($columnName);
    }

    public function testGetNode()
    {
        $this->assertInstanceOf('Jackalope\Node', $this->row->getNode());
    }

    public function testGetPath()
    {
        $this->assertEquals('/', $this->row->getPath());
    }

    public function testGetScore()
    {
        $this->assertNotNull($this->row->getScore());
    }
}
