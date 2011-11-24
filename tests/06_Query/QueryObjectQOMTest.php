<?php
namespace PHPCR\Tests\Query;

require_once('QueryBaseCase.php');

/**
 * test the Query Factory integration
 *
 * setLimit, setOffset, bindValue, getBindVariableNames
 *
 * the details of the QOM model are covered in the QOM subfolder
 */
class QueryObjectQOMTest extends QueryBaseCase
{

    /**
     * @var PHPCR\Query\QOM\QueryObjectManagerFactory
     */
    protected $factory;
    protected $query;

    public function setUp()
    {
        parent::setUp();

        try {
            $this->factory = $this->sharedFixture['qm']->getQOMFactory();
        } catch (\Exception $e) {
            $this->markTestSkipped('Can not get the QOM factory, skipping tests about QOM query. '.$e->getMessage());
        }

        $source = $this->factory->selector('nt:folder','data');
        $constraint = null;
        $orderings = array();
        $columns = array();

        $this->query = $this->factory->createQuery($source,$constraint,$orderings,$columns);
    }

    public function testExecute()
    {
        $qr = $this->query->execute();
        $this->assertInstanceOf('PHPCR\Query\QueryResultInterface', $qr);
        $this->assertEquals(5, count($qr->getRows()));
        // we assume content is the same as for sql2
    }

    /**
     * @expectedException PHPCR\Query\InvalidQueryException
     *
     * the doc claims there would just be a PHPCR\RepositoryException
     * it makes sense that there is a InvalidQueryException
     */
    public function testExecuteInvalid()
    {
        $source = $this->factory->selector('nonodetype','data');
        $constraint = null;
        $orderings = array();
        $columns = array();

        $query = $this->factory->createQuery($source,$constraint,$orderings,$columns);
        $qr = $query->execute();
    }

    public function testGetStatement()
    {
        $this->assertEquals('SELECT * FROM [nt:folder] AS data', $this->query->getStatement());
    }

    public function testGetLanguage()
    {
        $this->assertEquals(\PHPCR\Query\QueryInterface::JCR_JQOM, $this->query->getLanguage());
    }

    /**
     * a transient query has no stored query path
     * @expectedException PHPCR\ItemNotFoundException
     */
    public function testGetStoredQueryPathItemNotFound()
    {
        $this->query->getStoredQueryPath();
    }
    /* this is level 2 only */
    /*
    public function testStoreAsNode()
    {
        $qstr = '//idExample[jcr:mimeType="text/plain"]';
        $query = $this->sharedFixture['qm']->createQuery($qstr, 'xpath');
        $query->storeAsNode('/queryNode');
        $this->sharedFixture['session']->save();
    }
    */
    /*
        TODO: trigger the possible exceptions
    */

    // stored queries are tested in SQL2 but can be whatever the implementation wants actually
    // so no need to test them here again
}
