<?php
namespace PHPCR\Tests\Query;

require_once('QueryBaseCase.php');

/**
 * tests for the query manager, $ 6.8
 *
 * TODO: getQOMFactory
 */
class QueryManagerTest extends QueryBaseCase
{
    public static function setupBeforeClass($fixture = 'general/query')
    {
        parent::setupBeforeClass($fixture);
    }

    public function testCreateQuery()
    {
        $ret = $this->sharedFixture['qm']->createQuery(null, \PHPCR\Query\QueryInterface::JCR_SQL2);
        $this->assertInstanceOf('PHPCR\Query\QueryInterface', $ret);
    }

    /**
     * @expectedException PHPCR\Query\InvalidQueryException
     */
    public function testCreateXpathQuery()
    {
        $this->sharedFixture['qm']->createQuery('/jcr:root', 'xpath');
    }

    public function testGetQuery()
    {
        $qnode = $this->sharedFixture['session']->getRootNode()->getNode('queryNode');
        $this->assertInstanceOf('PHPCR\NodeInterface', $qnode);

        $query = $this->sharedFixture['qm']->getQuery($qnode);
        $this->assertInstanceOf('PHPCR\Query\QueryInterface', $query);
    }

    /**
     * @expectedException PHPCR\Query\InvalidQueryException
     */
    public function testGetQueryInvalid()
    {
        $this->sharedFixture['qm']->getQuery($this->rootNode);
    }

    public function testGetQOMFactory()
    {
        $factory = $this->sharedFixture['qm']->getQOMFactory();
        $this->assertInstanceOf('PHPCR\Query\QOM\QueryObjectModelFactoryInterface', $factory);
    }

    public function testGetSupportedQueryLanguages()
    {
        $ret = $this->sharedFixture['qm']->getSupportedQueryLanguages();
        $this->assertInternalType('array', $ret);
        $this->assertContains('JCR-SQL2', $ret);
        $this->assertContains('JCR-JQOM', $ret);
    }
}
