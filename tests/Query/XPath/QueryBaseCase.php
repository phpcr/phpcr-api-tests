<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Query\XPath;

use PHPCR\Query\QueryInterface;

/**
 * a base class for all query tests.
 */
abstract class QueryBaseCase extends \PHPCR\Tests\Query\QueryBaseCase
{
    /**
     * in addition to base stuff, prepare $this->query with a simple select query.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->sharedFixture['qm']->createQuery('//element(*,nt:folder)', QueryInterface::XPATH);
    }
}
