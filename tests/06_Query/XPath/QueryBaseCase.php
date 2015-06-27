<?php
namespace PHPCR\Tests\Query\XPath;


/**
 * a base class for all query tests
 */
abstract class QueryBaseCase extends \PHPCR\Tests\Query\QueryBaseCase
{
    /**
     * in addition to base stuff, prepare $this->query with a simple select query
     */
    public function setUp()
    {
        parent::setUp();

        $this->query = $this->sharedFixture['qm']->createQuery("//element(*,nt:folder)", \PHPCR\Query\QueryInterface::XPATH);
    }

}
