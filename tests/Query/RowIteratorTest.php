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

use OutOfBoundsException;
use PHPCR\NodeInterface;
use PHPCR\Query\RowInterface;

/**
 * $ 6.11.1 Row View - Iterator part.
 */
class RowIteratorTest extends QueryBaseCase
{
    public $rowIterator;

    public function setUp(): void
    {
        parent::setUp();

        $this->rowIterator = $this->query->execute()->getRows();
        $this->assertCount(5, $this->rowIterator);
    }

    public function testIterator()
    {
        $count = 0;

        foreach ($this->rowIterator as $key => $row) {
            $this->assertInstanceOf(RowInterface::class, $row); // Test if the return element is an istance of row
            $this->assertInstanceOf(NodeInterface::class, $row->getNode()); //Test if we can get the node of a certain row
            $this->assertCount(3, $row->getValues()); // test if we can get all the values of a row

            foreach ($row as $key => $value) { // Test if we can iterate over the columns inside a row
                $count++;
            }
        }

        $this->assertEquals(15, $count);
    }

    public function testSeekable()
    {
        $position = 2;

        $rows = [];

        foreach ($this->rowIterator as $row) {
            $rows[] = $row;
        }

        $this->rowIterator->seek($position);

        $this->assertEquals($rows[$position], $this->rowIterator->current());
    }

    public function testSeekableOutOfBounds()
    {
        $this->expectException(OutOfBoundsException::class);

        $position = -1;

        $this->rowIterator->seek($position);
    }

    public function testCountable()
    {
        $rows = [];

        foreach ($this->rowIterator as $row) {
            $rows[] = $row;
        }

        $this->assertCount($this->rowIterator->count(), $rows);
    }
}
