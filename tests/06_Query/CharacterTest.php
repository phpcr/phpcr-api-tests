<?php

namespace PHPCR\Tests\Query;

use PHPCR\Query\QueryInterface;

require_once(__DIR__ . '/../../inc/BaseCase.php');


class CharacterTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = '06_Query/characters')
    {
        parent::setupBeforeClass($fixtures);
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
    }

    /**
     * Using /tests_general_base/propertyCharacterComparison/jcr:content
     */
    public function testPropertyWithBackslash()
    {
        /** @var QueryManager $queryManager */
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
        $this->assertEquals('PHPCR\Query\QueryInterface', $rows->current()->getValue('class'));
    }

    /**
     * Using /tests_general_base/propertyCharacterComparison/jcr:content
     */
    public function testPropertyWithDoubleBackslash()
    {
        /** @var QueryManager $queryManager */
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
     * Using /tests_general_base/propertyCharacterComparison/jcr:content
     */
    public function testPropertyWithQuotes()
    {
        /** @var QueryManager $queryManager */
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
     * Using /tests_general_base/propertyCharacterComparison/jcr:content
     */
    public function testPropertyWithQuotesAndBackslash()
    {
        /** @var QueryManager $queryManager */
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
        /** @var QueryManager $queryManager */
        $queryManager = $this->sharedFixture['qm'];
        $query = $queryManager->createQuery('
            SELECT data.property
            FROM [nt:unstructured] AS data
            WHERE data.property = "foo:bar"
            ',
            QueryInterface::JCR_SQL2
        )->execute();
    }
}