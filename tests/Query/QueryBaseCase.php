<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Query;

use PHPCR\Query\QueryInterface;
use PHPCR\Test\BaseCase;

/**
 * a base class for all query tests.
 */
abstract class QueryBaseCase extends BaseCase
{
    /**
     * @var QueryInterface
     */
    protected $query;

    /**
     * The results to be expected in $this->query.
     *
     * @var array
     */
    protected $resultPaths;

    /**
     * in addition to base stuff, prepare the query manager and load general/query fixture.
     *
     * @param string $fixture name of the fixture to load, defaults to general/base
     */
    public static function setupBeforeClass($fixture = 'general/base')
    {
        parent::setupBeforeClass($fixture);
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
    }

    /**
     * in addition to base stuff, prepare $this->query with a simple select query.
     */
    public function setUp()
    {
        parent::setUp();

        $this->query = $this->sharedFixture['qm']->createQuery('
            SELECT *
            FROM [nt:folder]
            WHERE ISDESCENDANTNODE([/tests_general_base])
              OR ISSAMENODE([/tests_general_base])
            ',
            QueryInterface::JCR_SQL2
        );

        // the query result is not ordered, but these are the nodes that are to be expected in any order
        $this->resultPaths = [
            '/tests_general_base',
            '/tests_general_base/test:namespacedNode',
            '/tests_general_base/emptyExample',
            '/tests_general_base/multiValueProperty/deepnode',
            '/tests_general_base/multiValueProperty'
        ];
    }
}
