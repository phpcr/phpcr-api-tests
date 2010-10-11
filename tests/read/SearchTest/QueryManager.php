<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * test javax.jcr.QueryManager interface
 * todo: getQOMFactory
 */
class jackalope_tests_read_SearchTest_QueryManager extends jackalope_baseCase {
    static public function setupBeforeClass() {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('read/search/base.xml');
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
    }

    public function testCreateQuery() {
        $ret = $this->sharedFixture['qm']->createQuery(null, PHPCR_Query_QueryInterface::JCR_SQL2);
        $this->assertTrue(is_object($ret));
        $this->assertTrue($ret instanceof PHPCR_Query_QueryInterface);
    }

    /**
     * @expectedException PHPCR_Query_InvalidQueryException
     */
    public function testCreateXpathQuery() {
        $this->sharedFixture['qm']->createQuery('/jcr:root', 'xpath');
    }

    public function testGetQuery() {
        $this->sharedFixture['ie']->import('read/search/query.xml');
        try {
            $qnode = $this->sharedFixture['session']->getRootNode()->getNode('queryNode');
            $this->assertTrue(is_object($qnode));
            $this->assertTrue($qnode instanceof PHPCR_NodeInterface);

            $query = $this->sharedFixture['qm']->getQuery($qnode);
            $this->assertTrue(is_object($qnode));
            $this->assertTrue($query instanceof PHPCR_Query_QueryInterface);
        } catch(exception $e) {
            //FIXME: finally?
            $this->sharedFixture['ie']->import('read/search/base.xml');
            throw $e;
        }
        $this->sharedFixture['ie']->import('read/search/base.xml');
    }
    /**
     * @expectedException PHPCR_Query_InvalidQueryException
     */
    public function testGetQueryInvalid() {
        $this->sharedFixture['qm']->getQuery($this->sharedFixture['session']->getRootNode());
    }

    public function testGetSupportedQueryLanguages() {
        $ret = $this->sharedFixture['qm']->getSupportedQueryLanguages();
        $this->assertType('array', $ret);
        $this->assertContains('xpath', $ret);
    }
}
