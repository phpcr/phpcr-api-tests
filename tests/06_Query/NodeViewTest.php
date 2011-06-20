<?php

require_once('QueryBaseCase.php');

/**
 * test the query result node view $ 6.11.2
 */
class Query_6_NodeViewTest extends QueryBaseCase
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
            $this->assertInstanceOf('PHPCR\NodeInterface', $node); // Test if the return element is an istance of node
            $count++;
        }

        $this->assertEquals(8, $count, 'wrong number of elements in iterator');
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
