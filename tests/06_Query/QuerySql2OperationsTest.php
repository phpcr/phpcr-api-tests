<?php
namespace PHPCR\Tests\Query;

use PHPCR\Query\QueryInterface;

require_once('QueryBaseCase.php');

/**
 * Run non-trivial queries to try out where, the join features and such
 */
class QuerySql2OperationsTest extends QueryBaseCase
{
    public function testQueryField()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery('
            SELECT foo
            FROM [nt:unstructured]
            WHERE foo = "bar"
              AND (ISSAMENODE([/tests_general_base]) OR ISDESCENDANTNODE([/tests_general_base]))
            ',
            QueryInterface::JCR_SQL2
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
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery('
            SELECT foo
            FROM [nt:unstructured]
            WHERE ISDESCENDANTNODE([/tests_general_base])
            ',
            QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach ($result->getNodes() as $node) {
            $vals[] = ($node->hasProperty('foo') ? $node->getPropertyValue('foo') : null);
        }
        $this->assertContains('bar', $vals);
        $this->assertEquals(9, count($vals));

        $vals = array();
        foreach ($result->getRows() as $row) {
            $vals[] = $row->getValue('foo');
        }
        $this->assertContains('bar', $vals);
        $this->assertEquals(9, count($vals));
    }

    public function testQueryFieldSelector()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery('
            SELECT data.foo
            FROM [nt:unstructured] AS data
            WHERE data.foo = "bar"
              AND ISDESCENDANTNODE([/tests_general_base])
            ',
            QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach ($result->getRows() as $row) {
            $vals[] = $row->getValue('data.foo');
        }
        $this->assertEquals(array('bar'), $vals);
    }

    public function testQueryJoin()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery('
            SELECT content.longNumber
            FROM [nt:file] AS file
              INNER JOIN [nt:unstructured] AS content
                ON ISDESCENDANTNODE(content, file)
            WHERE content.longNumber = 999
              AND ISDESCENDANTNODE(file, [/tests_general_base])
            ',
            QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();

        foreach($result->getRows() as $row) {
            $vals[] = $row->getValue('content.longNumber');
        }
        $this->assertEquals(array(999), $vals);
    }

    public function testQueryLeftJoin()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT file.[jcr:name], target.longNumberToCompare
             FROM [nt:file] AS file
               LEFT OUTER JOIN [nt:unstructured] AS target
               ON ISDESCENDANTNODE(target, file)
             ',
            QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach ($result->getRows() as $row) {
            $vals[$row->getValue('file.jcr:name')] = $row->getValue('target.longNumberToCompare');
        }

        // We get 9 results (idExample comes back multiple times because of the join)
        $this->assertCount(9, $result->getRows());
        $this->assertEquals(array(
            'index.txt'                     => null,
            'idExample'                     => null,
            'numberPropertyNode'            => null,
            'NumberPropertyNodeToCompare1'  => 2,
            'NumberPropertyNodeToCompare2'  => 10,
        ), $vals);
    }

    public function testQueryRightJoin()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT file.[jcr:name], target.stringToCompare
             FROM [nt:unstructured] AS target
               RIGHT OUTER JOIN [nt:file] AS file
               ON ISDESCENDANTNODE(target, file)
             ',
            QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach ($result->getRows() as $row) {
            $vals[$row->getValue('file.jcr:name')] = $row->getValue('target.stringToCompare');
        }

        // We get 9 results (idExample comes back multiple times because of the join)
        $this->assertCount(9, $result->getRows());
        $this->assertEquals(array(
            'index.txt'                     => null,
            'idExample'                     => null,
            'numberPropertyNode'            => null,
            'NumberPropertyNodeToCompare1'  => '2',
            'NumberPropertyNodeToCompare2'  => '10',
        ), $vals);
    }

    public function testQueryJoinReference()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery(
            'SELECT source.ref1, target.[jcr:uuid]
             FROM [nt:unstructured] AS source
               INNER JOIN [nt:unstructured] AS target
               ON source.ref1 = target.[jcr:uuid]
             WHERE ISCHILDNODE(source, "/tests_general_base/idExample/jcr:content")
             ',
            QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach ($result->getRows() as $row) {
            $vals[$row->getValue('source.ref1')] = $row->getValue('target.jcr:uuid');
        }
        $this->assertEquals(array('13543fc6-1abf-4708-bfcc-e49511754b40' => '13543fc6-1abf-4708-bfcc-e49511754b40'), $vals);
    }

    public function testQueryOrder()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery('
            SELECT data.zeronumber
            FROM [nt:unstructured] AS data
            WHERE ISDESCENDANTNODE([/tests_general_base])
            ORDER BY data.zeronumber
            ',
            QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);
        $vals = array();
        foreach ($result->getRows() as $row) {
            $vals[] = $row->getValue('data.zeronumber');
        }
        // rows that do not have that field are null. empty is before fields with values
        $this->assertEquals(array(null, null, null, null, null, null, null, null, 0), $vals);
    }

    public function testQueryMultiValuedProperty()
    {
        /** @var $query QueryInterface */
        $query = $this->sharedFixture['qm']->createQuery('
            SELECT data.tags
            FROM [nt:unstructured] AS data
            WHERE data.tags = "foo"
              AND data.tags = "bar"
              AND ISDESCENDANTNODE([/tests_general_base])
            ',
            QueryInterface::JCR_SQL2
        );

        $this->assertInstanceOf('\PHPCR\Query\QueryInterface', $query);
        $result = $query->execute();
        $this->assertInstanceOf('\PHPCR\Query\QueryResultInterface', $result);

        $rows = $result->getRows();

        $this->assertCount(1, $rows, 'Expected one row with both tags present');
        $this->assertSame('foo bar', $rows->current()->getValue('tags'));
    }

}
