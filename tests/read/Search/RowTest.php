<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/** test the javax.jcr.Row interface
 *  todo: getNode, getPath, getScore
 */
class Read_Search_RowTest extends jackalope_baseCase
{
    private $row;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
    }

    public function setUp()
    {
        parent::setUp();

        $query = $this->sharedFixture['qm']->createQuery("SELECT * FROM [nt:unstructured]", \PHPCR\Query\QueryInterface::JCR_SQL2);
        $rows = $query->execute()->getRows();

        $rows->rewind();
        $this->row = $rows->current();

        $this->assertInstanceOf('PHPCR\Query\RowInterface', $this->row);
    }
    public function testIterator()
    {
        $count = 0;

        foreach ($this->row as $name => $value)
        {
            $this->assertNotNull($name);
            $this->assertNotNull($value);
            $count++;
        }

        $this->assertEquals(3, $count);
    }

    public function testGetValues()
    {
        $values = $this->row->getValues();

        foreach($values as $value) {
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
