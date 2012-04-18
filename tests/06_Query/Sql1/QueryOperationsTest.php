<?php
namespace PHPCR\Tests\Query\Sql1;

require_once('QueryBaseCase.php');

/**
 * Run non-trivial queries to try out where, the join features and such
 */
class QueryOperationsTest extends QueryBaseCase
{
    public function testQueryField()
    {
        $query = $this->sharedFixture['qm']->createQuery(
                "SELECT foo FROM nt:unstructured WHERE foo = 'bar'",
            \PHPCR\Query\QueryInterface::SQL
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach ($result->getNodes() as $node) {
            $vals[] = $node->getPropertyValue('foo');
        }
        $this->assertEquals(array('bar'), $vals);

        $vals = array();
        foreach ($result->getRows() as $row) {
            $vals[] = $row->getValue('foo');
        }
        $this->assertEquals(array('bar'), $vals);
    }

    public function testQueryFieldSomenull()
    {
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT foo FROM nt:unstructured',
            \PHPCR\Query\QueryInterface::SQL
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach ($result->getNodes() as $node) {
            $vals[] = ($node->hasProperty('foo') ? $node->getPropertyValue('foo') : null);
        }
        $this->assertContains('bar', $vals);
        $this->assertEquals(8, count($vals));

        $vals = array();
        foreach ($result->getRows() as $row) {
            $vals[] = $row->getValue('foo');
        }
        $this->assertContains('bar', $vals);
        $this->assertEquals(8, count($vals));
    }

    public function testQueryOrder()
    {
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT zeronumber
             FROM nt:unstructured
             ORDER BY zeronumber',
            \PHPCR\Query\QueryInterface::SQL
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach ($result->getRows() as $row) {
            $vals[] = $row->getValue('zeronumber');
        }
        // rows that do not have that field are null. empty is before fields with values
        $this->assertEquals(array(null, null, null, null, null, null, null, 0), $vals);
    }

}
