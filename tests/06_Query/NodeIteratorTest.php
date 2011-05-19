<?php

require_once('QueryBaseCase.php');

//6.6.8 Query API
class Query_6_NodeIteratorTest extends QueryBaseCase
{
    public $nodeIterator;

    public function setUp()
    {
        parent::setUp();

        $this->nodeIterator = $this->query->execute()->getNodes();
    }

    public function testIterator()
    {
        $count = 0;

        foreach ($this->nodeIterator as $node) {
            $this->assertInstanceOf('PHPCR\NodeInterface', $node); // Test if the return element is an istance of row
            $count++;
        }

        $this->assertEquals(4, $count, 'wrong number of elements in iterator');
    }

    public function testSeekable()
    {
        $seekNodeName = '/tests_general_base/index.txt/jcr:content';

        $nodes = array();
        foreach ($this->nodeIterator as $nodeName => $node) {
            $nodes[$nodeName] = $node;
        }
        $this->nodeIterator->seek($seekNodeName);
        $this->assertEquals($nodes[$seekNodeName], $this->nodeIterator->current());
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testSeekableOutOfBounds()
    {
        $seekNodeName = 'foobar';

        $this->nodeIterator->seek($seekNodeName);
    }

    public function testCountable()
    {
        $nodes = array();
        foreach ($this->nodeIterator as $node) {
            $nodes[] = $node;
        }

        $this->assertEquals(count($nodes), $this->nodeIterator->count());
    }
}
