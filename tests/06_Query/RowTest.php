<?php
namespace PHPCR\Tests\Query;

require_once('QueryBaseCase.php');

/**
 * $ 6.11.1 Table View - Row part
 */
class RowTest extends QueryBaseCase
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
        $path = $this->row->getValue('jcr:path');
        $this->assertInternalType('string', $path);
        $this->assertEquals('/tests_general_base', substr($path, 0, 19));
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
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->row->getNode());
    }

    public function testGetPath()
    {
        $this->assertTrue(in_array($this->row->getPath(), $this->resultPaths), 'not one of the expected results');
    }

    public function testGetScore()
    {
        $this->assertInternalType('float', $this->row->getScore());
    }
}
