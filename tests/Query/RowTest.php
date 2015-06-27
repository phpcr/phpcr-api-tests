<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Query;

/**
 * $ 6.11.1 Table View - Row part.
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
