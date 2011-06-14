<?php

namespace Jackalope\Tests\QOM;

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');
require_once('Sql2TestQueries.php');

use Jackalope\Query\QOM;
use Jackalope\Query\QOM\Converter\QomToSql2QueryConverter;
use Jackalope\Query\QOM\Converter\Sql2Generator;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;

/**
 * Test for Jackalope\Query\QomParser
 */
class QomToSql2ConverterTest extends \phpcr_suite_baseCase
{
    /**
     * @var \Jackalope\Query\QomParser
     */
    protected $parser;

    /**
     * @var \Jackalope\Query\QOM\QueryObjectModelFactory
     */
    protected $factory;

    protected $queries;

    public function setUp()
    {
        parent::setUp();

        if (! $this->sharedFixture['session']->getWorkspace() instanceof \Jackalope\Workspace) {
            $this->markTestSkipped('This is a test for Jackalope specific functionality');
        }

        $this->parser = new QomToSql2QueryConverter(new Sql2Generator());
        $this->factory = new QOM\QueryObjectModelFactory();
        $this->queries = Sql2TestQueries::getQueries();
    }

    /**
     * 6.7.3. Selector
     */
    public function testSelector()
    {
        $this->assertQuery($this->queries['6.7.3.Selector.Simple'], $this->factory->selector('nt:unstructured'));
        $this->assertQuery($this->queries['6.7.3.Selector.Named'], $this->factory->selector('nt:unstructured', 'test'));
    }

    /**
     * 6.7.8. EquiJoinCondition
     */
    public function testEquiJoin()
    {
        $left = $this->factory->selector('nt:file');
        $right = $this->factory->selector('nt:folder');
        $condition = new QOM\EquiJoinCondition('sel1', 'prop1', 'sel2', 'prop2');

        $this->assertQuery($this->queries['6.7.8.EquiJoin.Inner'], $this->factory->join($left, $right, Constants::JCR_JOIN_TYPE_INNER, $condition));
        $this->assertQuery($this->queries['6.7.8.EquiJoin.Left'], $this->factory->join($left, $right, Constants::JCR_JOIN_TYPE_LEFT_OUTER, $condition));
        $this->assertQuery($this->queries['6.7.8.EquiJoin.Right'], $this->factory->join($left, $right, Constants::JCR_JOIN_TYPE_RIGHT_OUTER, $condition));
    }

    /**
     * 6.7.9. SameNodeJoinCondition
     */
    public function testSameNodeJoin()
    {
        $left = $this->factory->selector('nt:file');
        $right = $this->factory->selector('nt:folder');
        
        $condition = new QOM\SameNodeJoinCondition('sel1', 'sel2');
        $this->assertQuery($this->queries['6.7.9.SameNodeJoinCondition.Simple'], $this->factory->join($left, $right, Constants::JCR_JOIN_TYPE_INNER, $condition));

        //TODO: should path be surronded by quotes?
        $condition = new QOM\SameNodeJoinCondition('sel1', 'sel2', '/home');
        $this->assertQuery($this->queries['6.7.9.SameNodeJoinCondition.Path'], $this->factory->join($left, $right, Constants::JCR_JOIN_TYPE_INNER, $condition));
        
    }

    /**
     * 6.7.10 ChildNodeJoinCondition
     */
    public function testChildNodeJoin()
    {
        $left = $this->factory->selector('nt:file');
        $right = $this->factory->selector('nt:folder');

        $condition = new QOM\ChildNodeJoinCondition('child', 'parent');
        $this->assertQuery($this->queries['6.7.10.ChildNodeCondition'], $this->factory->join($left, $right, Constants::JCR_JOIN_TYPE_INNER, $condition));
    }

    /**
     * 6.7.11 DescendantNodeJoinCondition
     */
    public function testDescendantNodeJoin()
    {
        $left = $this->factory->selector('nt:file');
        $right = $this->factory->selector('nt:folder');

        $condition = new QOM\DescendantNodeJoinCondition('descendant', 'ancestor');
        $this->assertQuery($this->queries['6.7.11.DescendantNodeJoinCondition'], $this->factory->join($left, $right, Constants::JCR_JOIN_TYPE_INNER, $condition));
    }

    /**
     * 6.7.13. AndConstraint
     */
    public function testAndConstraint()
    {
        $selector = $this->factory->selector('nt:file');
        $constraint1 = $this->factory->propertyExistence('prop1', 'sel1');
        $constraint2 = $this->factory->propertyExistence('prop2', 'sel2');
        $this->assertQuery($this->queries['6.7.13.And'], $selector, array(), $this->factory->_and($constraint1, $constraint2), array());
    }

    /**
     * 6.7.14. OrConstraint
     */
    public function testOrConstraint()
    {
        $selector = $this->factory->selector('nt:file');
        $constraint1 = $this->factory->propertyExistence('prop1', 'sel1');
        $constraint2 = $this->factory->propertyExistence('prop2', 'sel2');
        $this->assertQuery($this->queries['6.7.14.Or'], $selector, array(), $this->factory->_or($constraint1, $constraint2), array());
    }

    /**
     * 6.7.15. NotConstraint
     */
    public function testNotConstraint()
    {
        $selector = $this->factory->selector('nt:file');
        $constraint = $this->factory->propertyExistence('prop1', 'sel1');
        $this->assertQuery($this->queries['6.7.15.Not'], $selector, array(), $this->factory->not($constraint), array());
    }

    /**
     * 6.7.16. Comparison
     */
    public function testComparison()
    {
        $selector = $this->factory->selector('nt:file');
        $operand1 = $this->factory->nodeName('test');
        $operator = Constants::JCR_OPERATOR_LIKE;
        $operand2 = $this->factory->literal('literal2');
        $this->assertQuery($this->queries['6.7.16.Comparison'], $selector, array(), $this->factory->comparison($operand1, $operator, $operand2), array());
    }

    /**
     * 6.7.18. PropertyExistence
     */
    public function testPropertyExistence()
    {
        $selector = $this->factory->selector('nt:file');
        $this->assertQuery($this->queries['6.7.18.PropertyExistence'], $selector, array(), $this->factory->propertyExistence('prop1', 'sel1'), array());
    }

    /**
     * 6.7.19. FullTextSearch
     */
    public function testFullTextSearch()
    {
        $selector = $this->factory->selector('nt:file');
        $this->assertQuery($this->queries['6.7.19.FullTextSearch'], $selector, array(), $this->factory->fullTextSearch('prop', 'expr', 'sel'), array());
    }

    /**
     * 6.7.20. SameNode
     */
    public function testSameNode()
    {
        $selector = $this->factory->selector('nt:file');
        $this->assertQuery($this->queries['6.7.20.SameNode.Simple'], $selector, array(), $this->factory->sameNode('/home'), array());
        $this->assertQuery($this->queries['6.7.20.SameNode.Selector'], $selector, array(), $this->factory->sameNode('/home', 'sel1'), array());
    }

    /**
     * 6.7.21. ChildNode
     */
    public function testChildNode()
    {
        $selector = $this->factory->selector('nt:file');
        $this->assertQuery($this->queries['6.7.21.ChildNode.Simple'], $selector, array(), $this->factory->childNode('/home'), array());
        $this->assertQuery($this->queries['6.7.21.ChildNode.Selector'], $selector, array(), $this->factory->childNode('/home', 'sel1'), array());
    }

    /**
     * 6.7.22. DescendantNode
     */
    public function testDescendantNode()
    {
        $selector = $this->factory->selector('nt:file');
        $this->assertQuery($this->queries['6.7.22.DescendantNode.Simple'], $selector, array(), $this->factory->descendantNode('/home'), array());
        $this->assertQuery($this->queries['6.7.22.DescendantNode.Selector'], $selector, array(), $this->factory->descendantNode('/home', 'sel1'), array());
    }

    /**
     * 6.7.27. ProperyValue
     */
    public function testPropertyValue()
    {
        $selector = $this->factory->selector('nt:file');
        $operand1 = $this->factory->propertyValue('prop', 'sel');
        $operand2 = $this->factory->literal('literal');
        $operator = Constants::JCR_OPERATOR_LIKE;
        $constraint = $this->factory->comparison($operand1, $operator, $operand2);
        $this->assertQuery($this->queries['6.7.27.PropertyValue'], $selector, array(), $constraint, array());
    }

    /**
     * 6.7.28. Length
     */
    public function testLength()
    {
        $selector = $this->factory->selector('nt:file');
        $operand1 = $this->factory->length($this->factory->propertyValue('prop'));
        $operand2 = $this->factory->literal('literal');
        $operator = Constants::JCR_OPERATOR_LIKE;
        $constraint = $this->factory->comparison($operand1, $operator, $operand2);
        $this->assertQuery($this->queries['6.7.28.Length'], $selector, array(), $constraint, array());
    }

    /**
     * 6.7.29. NodeName
     */
    public function testNodeName()
    {
        $selector = $this->factory->selector('nt:file');
        $operand1 = $this->factory->nodeName('sel');
        $operand2 = $this->factory->literal('literal');
        $operator = Constants::JCR_OPERATOR_LIKE;
        $constraint = $this->factory->comparison($operand1, $operator, $operand2);
        $this->assertQuery($this->queries['6.7.29.NodeName'], $selector, array(), $constraint, array());
    }

    /**
     * 6.7.30. NodeLocalName
     */
    public function testNodeLocalName()
    {
        $selector = $this->factory->selector('nt:file');
        $operand1 = $this->factory->nodeLocalName('sel');
        $operand2 = $this->factory->literal('literal');
        $operator = Constants::JCR_OPERATOR_LIKE;
        $constraint = $this->factory->comparison($operand1, $operator, $operand2);
        $this->assertQuery($this->queries['6.7.30.NodeLocalName'], $selector, array(), $constraint, array());
    }

    /**
     * 6.7.31. FullTextSearchScore
     */
    public function testFullTextSearchScore()
    {
        $selector = $this->factory->selector('nt:file');
        $operand1 = $this->factory->fullTextSearchScore('sel');
        $operand2 = $this->factory->literal('literal');
        $operator = Constants::JCR_OPERATOR_LIKE;
        $constraint = $this->factory->comparison($operand1, $operator, $operand2);
        $this->assertQuery($this->queries['6.7.31.FullTextSearchScore'], $selector, array(), $constraint, array());
    }

    /**
     * 6.7.32. LowerCase
     */
    public function testLowerCase()
    {
        $selector = $this->factory->selector('nt:file');
        $operand1 = $this->factory->lowerCase($this->factory->nodeName());
        $operand2 = $this->factory->literal('literal');
        $operator = Constants::JCR_OPERATOR_LIKE;
        $constraint = $this->factory->comparison($operand1, $operator, $operand2);
        $this->assertQuery($this->queries['6.7.32.LowerCase'], $selector, array(), $constraint, array());
    }

    /**
     * 6.7.33. UpperCase
     */
    public function testUpperCase()
    {
        $selector = $this->factory->selector('nt:file');
        $operand1 = $this->factory->upperCase($this->factory->nodeName());
        $operand2 = $this->factory->literal('literal');
        $operator = Constants::JCR_OPERATOR_LIKE;
        $constraint = $this->factory->comparison($operand1, $operator, $operand2);
        $this->assertQuery($this->queries['6.7.33.UpperCase'], $selector, array(), $constraint, array());
    }

    /**
     * 6.7.35. BindVariable
     */
    public function testBindVariable()
    {
        $selector = $this->factory->selector('nt:file');
        $operand1 = $this->factory->upperCase($this->factory->nodeName());
        $operand2 = $this->factory->bindVariable('var');
        $operator = Constants::JCR_OPERATOR_LIKE;
        $constraint = $this->factory->comparison($operand1, $operator, $operand2);
        $this->assertQuery($this->queries['6.7.35.BindValue'], $selector, array(), $constraint, array());
    }


    /**
     * 6.7.38 Order
     */
    public function testOrdering()
    {
        $selector = $this->factory->selector('nt:unstructured');
        $order1 = $this->factory->ascending($this->factory->propertyValue('prop1'));
        $order2 = $this->factory->descending($this->factory->propertyValue('prop2'));

        $this->assertQuery($this->queries['6.7.38.Order.None'], $selector, array(), null, array());
        $this->assertQuery($this->queries['6.7.38.Order.Asc'], $selector, array(), null, array($order1));
        $this->assertQuery($this->queries['6.7.38.Order.Mixed'], $selector, array(), null, array($order1, $order2));
    }

    /**
     * 6.7.39 Column
     */
    public function testColumns()
    {
        $selector = $this->factory->selector('nt:unstructured');
        $col1 = $this->factory->column('prop1');
        $col2 = $this->factory->column('prop2', 'col2');
        $col3 = $this->factory->column('prop3', 'col3', 'sel3');

        $this->assertQuery($this->queries['6.7.39.Colum.Wildcard'], $selector, array());
        $this->assertQuery($this->queries['6.7.39.Colum.Simple'], $selector, array($col1));
        $this->assertQuery('SELECT prop1, prop2 AS col2 FROM [nt:unstructured]', $selector, array($col1, $col2));
        $this->assertQuery($this->queries['6.7.39.Colum.Mixed'], $selector, array($col1, $col2, $col3));
    }


    // -------------------------------------------------------------------------

    protected function assertQuery($expectedSql2, $source, $columns = array(), $constraint = null, $ordering = array())
    {
        $query = new QOM\QueryObjectModel($source, $constraint, $ordering, $columns);
        $this->assertEquals($expectedSql2, $this->parser->convert($query));
    }
}