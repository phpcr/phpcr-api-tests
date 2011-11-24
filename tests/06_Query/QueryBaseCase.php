<?php
namespace PHPCR\Tests\Query;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * a base class for all query tests
 */
abstract class QueryBaseCase extends \PHPCR\Test\BaseCase
{
    /**
     * in addition to base stuff, prepare the query manager and load general/query fixture
     *
     * @param string $fixture name of the fixture to load, defaults to general/base
     */
    public static function setupBeforeClass($fixture = 'general/base')
    {
        parent::setupBeforeClass($fixture);
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
    }

    /**
     * in addition to base stuff, prepare $this->query with a simple select query
     */
    public function setUp()
    {
        parent::setUp();

        $this->query = $this->sharedFixture['qm']->createQuery("SELECT * FROM [nt:folder]", \PHPCR\Query\QueryInterface::JCR_SQL2);

        // the query result is not ordered, but these are the nodes that are to be expected in any order
        $this->resultPaths = array("/tests_general_base",
                                   "/tests_general_base/test:namespacedNode",
                                   "/tests_general_base/emptyExample",
                                   "/tests_general_base/multiValueProperty/deepnode",
                                   "/tests_general_base/multiValueProperty");
    }
}
