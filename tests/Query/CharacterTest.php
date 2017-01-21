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
use PHPCR\Query\QueryManagerInterface;
use PHPCR\Test\BaseCase;

class CharacterTest extends BaseCase
{
    public static function setupBeforeClass($fixtures = '06_Query/characters')
    {
        parent::setupBeforeClass($fixtures);
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
    }

    /**
     * Using /tests_general_base/propertyCharacterComparison/jcr:content.
     */
    public function testPropertyWithBackslash()
    {
        /** @var QueryManagerInterface $queryManager */
        $queryManager = $this->sharedFixture['qm'];
        $query = $queryManager->createQuery('
            SELECT data.class
            FROM [nt:unstructured] AS data
            WHERE data.class = "PHPCR\Query\QueryInterface"',
            QueryInterface::JCR_SQL2
        );

        $result = $query->execute();

        $rows = $result->getRows();
        $this->assertCount(1, $rows);
        $this->assertEquals(QueryInterface::class, $rows->current()->getValue('class'));
    }

    /**
     * Using /tests_general_base/propertyCharacterComparison/jcr:content.
     */
    public function testPropertyWithDoubleBackslash()
    {
        /** @var QueryManagerInterface $queryManager */
        $queryManager = $this->sharedFixture['qm'];
        $query = $queryManager->createQuery('
            SELECT data.doublebackslash
            FROM [nt:unstructured] AS data
            WHERE data.doublebackslash = "PHPCR\\\\Query\\\\QueryInterface"',
            QueryInterface::JCR_SQL2
        );

        $result = $query->execute();

        $rows = $result->getRows();
        $this->assertCount(1, $rows);
        $this->assertEquals('PHPCR\\\\Query\\\\QueryInterface', $rows->current()->getValue('doublebackslash'));
    }

    /**
     * Using /tests_general_base/propertyCharacterComparison/jcr:content.
     */
    public function testPropertyWithQuotes()
    {
        /** @var QueryManagerInterface $queryManager */
        $queryManager = $this->sharedFixture['qm'];
        $query = $queryManager->createQuery(sprintf('
            SELECT data.quotes
            FROM [nt:unstructured] AS data
            WHERE data.quotes = "%s"
            ', "\\\"'"),
            QueryInterface::JCR_SQL2
        );

        $result = $query->execute();

        $rows = $result->getRows();
        $this->assertCount(1, $rows);
        $this->assertEquals('"\'', $rows->current()->getValue('quotes'));
    }

    /**
     * Using /tests_general_base/propertyCharacterComparison/jcr:content.
     */
    public function testPropertyWithQuotesAndBackslash()
    {
        /** @var QueryManagerInterface $queryManager */
        $queryManager = $this->sharedFixture['qm'];
        $query = $queryManager->createQuery(sprintf('
            SELECT data.quoteandbackslash
            FROM [nt:unstructured] AS data
            WHERE data.quoteandbackslash = "%s"
            ', "'a\'\'b\'\'c'"),
            QueryInterface::JCR_SQL2
        );

        $result = $query->execute();

        $rows = $result->getRows();
        $this->assertCount(1, $rows);
        $this->assertEquals("'a\'\'b\'\'c'", $rows->current()->getValue('quoteandbackslash'));
    }

    public function testQueryWithColon()
    {
        /** @var QueryManagerInterface $queryManager */
        $queryManager = $this->sharedFixture['qm'];
        $query = $queryManager->createQuery('
            SELECT data.property
            FROM [nt:unstructured] AS data
            WHERE data.property = "foo:bar"
            ',
            QueryInterface::JCR_SQL2
        )->execute();
    }

    public function testQueryWithAmpersand()
    {
        /** @var QueryManagerInterface $queryManager */
        $queryManager = $this->sharedFixture['qm'];
        $query = $queryManager->createQuery('
            SELECT data.ampersand
            FROM [nt:unstructured] AS data
            WHERE data.ampersand = "foo & bar&baz"
            ',
            QueryInterface::JCR_SQL2
        );

        $result = $query->execute();
        $rows = $result->getRows();
        $this->assertCount(1, $rows);
        $this->assertEquals('foo & bar&baz', $rows->current()->getValue('ampersand'));
    }
}
