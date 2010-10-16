<?php

require_once(dirname(__FILE__) . '/../../inc/baseSuite.php');
//6.6.8 getQueryManager is tested in 6.2.2 Workspace Read Methods
require_once(dirname(__FILE__) . '/SearchTest/QueryManager.php'); //6.6.9
//6.6.11 storeAsNode is about level2, not relevant here
require_once(dirname(__FILE__) . '/SearchTest/QueryResults.php'); //6.6.12
require_once(dirname(__FILE__) . '/SearchTest/Row.php'); //6.6.12
//TODO verify that permission restrictions are respected... (6.6.13)

class jackalope_tests_read_SearchTest extends jackalope_baseSuite
{

    public static function suite()
    {
        $suite = new jackalope_tests_read_SearchTest('Read: Search');
        //TODO: JACK-11: Implement search
        #$suite->addTestSuite('jackalope_tests_read_SearchTest_QueryManager');
        #$suite->addTestSuite('jackalope_tests_read_SearchTest_QueryResults');
        #$suite->addTestSuite('jackalope_tests_read_SearchTest_Row');
        return $suite;
    }

}
