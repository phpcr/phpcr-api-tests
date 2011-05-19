<?php
require_once(dirname(__FILE__) . '/../../inc/baseCase.php');


abstract class QueryBaseCase extends jackalope_baseCase
{
    /**
     * in addition to base stuff, prepare the query manager and load general/query fixture
     *
     * @param string $fixture name of the fixture to load, defaults to general/base
     */
    public static function setupBeforeClass($fixture = 'general/base')
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
        self::$staticSharedFixture['ie']->import($fixture);
    }

    /**
     * in addition to base stuff, prepare $this->query with a simple select query
     */
    public function setUp()
    {
        parent::setUp();

        $this->query = $this->sharedFixture['qm']->createQuery("SELECT * FROM [nt:unstructured]", \PHPCR\Query\QueryInterface::JCR_SQL2);
    }
}
