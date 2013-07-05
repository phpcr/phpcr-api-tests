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
        $this->assertEquals('PHPCR\\Query\\QueryInterface', $rows->current()->getValue('class'));
    }

    /**
     * Using /tests_general_base/propertyCharacterComparison/jcr:content
     */
    public function testPropertyWithQuotes()
    {
        /** @var QueryManager $queryManager */
        $queryManager = $this->sharedFixture['qm'];
        $query = $queryManager->createQuery('
            SELECT data.quotes
            FROM [nt:unstructured] AS data
            WHERE data.quotes = "\\"\'"
            ',
            QueryInterface::JCR_SQL2
        );

        $result = $query->execute();

        $rows = $result->getRows();
        $this->assertCount(1, $rows);
        $this->assertEquals('"\'', $rows->current()->getValue('quotes'));
    }
}