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

        $this->assertNotEmpty($this->row->getValue('jcr:primaryType', 'Empty value of jcr:primaryType'));
        $this->assertNotEmpty($this->row->getValue('jcr:path', 'Empty value of jcr:path'));
        $this->assertNotEmpty($this->row->getValue('jcr:score', 'Empty value of jcr:score'));

        if ($count > 3) {
            $this->assertNotEmpty($this->row->getValue('jcr:created', 'Empty value of jcr:created'));
            $this->assertNotEmpty($this->row->getValue('jcr:createdBy', 'Empty value of jcr:createdBy'));
            $this->assertEquals(5, $count);
        } else {
            $this->assertEquals(3, $count);
        }
    }

    public function testGetValues()
    {
        $values = $this->row->getValues();

        $count = 0;
        foreach ($values as $value) {
            $this->assertNotNull($value);
            $count++;
        }

        $this->assertEquals(3, $count);

        $keys = array_keys($values);
        sort($keys);
        $this->assertEquals(array('nt:folder.jcr:created', 'nt:folder.jcr:createdBy', 'nt:folder.jcr:primaryType'), $keys);

    }

    public function testGetValue()
    {
        $path = $this->row->getValue('jcr:createdBy');
        $this->assertInternalType('string', $path);
        $this->assertEquals('admin', $path);
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
