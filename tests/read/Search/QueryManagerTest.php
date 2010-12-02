<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * test javax.jcr.QueryManager interface
 * todo: getQOMFactory
 */
class Read_Search_QueryManagerTest extends jackalope_baseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('read/search/base.xml');
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
    }

    public function testCreateQuery()
    {
        $ret = $this->sharedFixture['qm']->createQuery(null, PHPCR\Query\QueryInterface::JCR_SQL2);
        $this->assertType('PHPCR\Query\QueryInterface', $ret);
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
        $this->sharedFixture['ie']->import('read/search/query.xml');
        try {
            $qnode = $this->sharedFixture['session']->getRootNode()->getNode('queryNode');
            $this->assertType('PHPCR\NodeInterface', $qnode);

            $query = $this->sharedFixture['qm']->getQuery($qnode);
            $this->assertTrue('PHPCR\Query\QueryInterface', $query);
        } catch(exception $e) {
            //FIXME: finally?
            $this->sharedFixture['ie']->import('read/search/base.xml');
            throw $e;
        }
        $this->sharedFixture['ie']->import('read/search/base.xml');
    }
    /**
     * @expectedException PHPCR\Query\InvalidQueryException
     */
    public function testGetQueryInvalid()
    {
        $this->sharedFixture['qm']->getQuery($this->sharedFixture['session']->getRootNode());
    }

    public function testGetSupportedQueryLanguages()
    {
        $ret = $this->sharedFixture['qm']->getSupportedQueryLanguages();
        $this->assertType('array', $ret);
        $this->assertContains('JCR-SQL2', $ret);
        $this->assertContains('JCR-JQOM', $ret);
    }
}
