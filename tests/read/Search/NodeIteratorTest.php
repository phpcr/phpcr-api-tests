<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

//6.6.8 Query API
class Read_Search_NodeIteratorTest extends jackalope_baseCase
{
    public $nodeIterator;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
        self::$staticSharedFixture['ie']->import('read/search/base');
    }

    public function setUp()
    {
        parent::setUp();

        $query = $this->sharedFixture['qm']->createQuery("SELECT * FROM [nt:unstructured]", \PHPCR\Query\QueryInterface::JCR_SQL2);
        $this->nodeIterator = $query->execute()->getNodes();
    }

    public function testIterator()
    {
        $count = 0;

        foreach ($this->nodeIterator as $node)
        {
            $this->assertInstanceOf('PHPCR\NodeInterface', $node); // Test if the return element is an istance of row
            $count++;
        }

        $this->assertEquals(4, $count, 'wrong number of elements in iterator');
    }

    public function testSeekable()
    {
        $seekNodeName = '/tests_read_search_base/index.txt/jcr:content';

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
