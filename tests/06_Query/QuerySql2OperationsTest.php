<?php
namespace PHPCR\Tests\Query;

require_once('QueryBaseCase.php');

/**
 * Run non-trivial queries to try out where, the join features and such
 */
class QuerySql2OperationsTest extends QueryBaseCase
{
    public function testQueryField()
    {
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT foo FROM [nt:unstructured] WHERE foo = "bar"',
            \PHPCR\Query\QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach($result->getNodes() as $node) {
            $vals[] = $node->getPropertyValue('foo');
        }
        $this->assertEquals(array('bar'), $vals);

        $vals = array();
        foreach($result->getRows() as $row) {
            $vals[] = $row->getValue('foo');
        }
        $this->assertEquals(array('bar'), $vals);
    }

    public function testQueryFieldSomenull()
    {
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT foo FROM [nt:unstructured]',
            \PHPCR\Query\QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach($result->getNodes() as $node) {
            $vals[] = ($node->hasProperty('foo') ? $node->getPropertyValue('foo') : null);
        }
        $this->assertContains('bar', $vals);
        $this->assertEquals(8, count($vals));

        $vals = array();
        foreach($result->getRows() as $row) {
            $vals[] = $row->getValue('foo');
        }
        $this->assertContains('bar', $vals);
        $this->assertEquals(8, count($vals));
    }

    public function testQueryFieldSelector()
    {
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT data.foo FROM [nt:unstructured] as data WHERE data.foo = "bar"',
            \PHPCR\Query\QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach($result->getRows() as $row) {
            $vals[] = $row->getValue('data.foo');
        }
        $this->assertEquals(array('bar'), $vals);
    }

    public function testQueryJoin()
    {
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT data.zeronumber
             FROM [nt:unstructured] as data
               INNER JOIN [nt:unstructured] as second
               ON data.[jcr:mimeType] = second.[jcr:mimeType]

             WHERE data.zeronumber = 0',
            \PHPCR\Query\QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach($result->getRows() as $row) {
            $vals[] = $row->getValue('data.zeronumber');
        }
        $this->assertEquals(array(0), $vals);
    }

    public function testQueryJoinReference()
    {
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT source.ref1, target.[jcr:uuid]
             FROM [nt:unstructured] as source
               INNER JOIN [nt:unstructured] as target
               ON source.ref1 = target.[jcr:uuid]
             WHERE ISCHILDNODE(source, "/tests_general_base/idExample/jcr:content")
             ',
            \PHPCR\Query\QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach($result->getRows() as $row) {
            $vals[$row->getValue('source.ref1')] = $row->getValue('target.jcr:uuid');
        }
        $this->assertEquals(array('13543fc6-1abf-4708-bfcc-e49511754b40' => '13543fc6-1abf-4708-bfcc-e49511754b40'), $vals);
    }

    public function testQueryOrder()
    {
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT data.zeronumber
             FROM [nt:unstructured] as data
             ORDER BY data.zeronumber',
            \PHPCR\Query\QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach($result->getRows() as $row) {
            $vals[] = $row->getValue('data.zeronumber');
        }
        // rows that do not have that field are null. empty is before fields with values
        $this->assertEquals(array(null, null, null, null, null, null, null, 0), $vals);
    }

}
