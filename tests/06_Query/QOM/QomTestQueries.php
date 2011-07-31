<?php
namespace PHPCR\Tests\Query\QOM;

use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;

/**
* Test queries for QOM language
*
* The QOM queries defined here correspond to the SQL2 queries defined in Sql2TestQueries.
* @see Sql2TestQueries
*/
class QomTestQueries {

    public static function getQueries(\PHPCR\Query\QOM\QueryObjectModelFactoryInterface $factory) {

        $queries = array();

        /**
        * 6.7.3. Selector
        */

        // SELECT * FROM nt:unstructured
        $queries['6.7.3.Selector.Simple'] =
            $factory->createQuery(
                $factory->selector('nt:unstructured'),
                null,
                array(),
                array());

        // SELECT * FROM nt:unstructured AS test
        $queries['6.7.3.Selector.Named'] =
            $factory->createQuery(
                $factory->selector('nt:unstructured', 'test'),
                null,
                array(),
                array());

        /**
        * 6.7.8. EquiJoinCondition
        */

        // SELECT * FROM nt:file INNER JOIN nt:folder ON sel1.prop1=sel2.prop2
        $queries['6.7.8.EquiJoin.Inner'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('nt:file'),
                    $factory->selector('nt:folder'),
                    Constants::JCR_JOIN_TYPE_INNER,
                    $factory->equiJoinCondition('sel1', 'prop1', 'sel2', 'prop2')),
                null,
                array(),
                array());

        // SELECT * FROM nt:file LEFT OUTER JOIN nt:folder ON sel1.prop1=sel2.prop2
        $queries['6.7.8.EquiJoin.Left'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('nt:file'),
                    $factory->selector('nt:folder'),
                    Constants::JCR_JOIN_TYPE_LEFT_OUTER,
                    $factory->equiJoinCondition('sel1', 'prop1', 'sel2', 'prop2')),
                null,
                array(),
                array());

        // SELECT * FROM nt:file RIGHT OUTER JOIN nt:folder ON sel1.prop1=sel2.prop2
        $queries['6.7.8.EquiJoin.Right'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('nt:file'),
                    $factory->selector('nt:folder'),
                    Constants::JCR_JOIN_TYPE_RIGHT_OUTER,
                    $factory->equiJoinCondition('sel1', 'prop1', 'sel2', 'prop2')),
                null,
                array(),
                array());

        /**
        * 6.7.9. SameNodeJoinCondition
        */

        // SELECT * FROM nt:file INNER JOIN nt:folder ON ISSAMENODE(sel1, sel2)
        $queries['6.7.9.SameNodeJoinCondition.Simple'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('nt:file'),
                    $factory->selector('nt:folder'),
                    Constants::JCR_JOIN_TYPE_INNER,
                    $factory->sameNodeJoinCondition('sel1', 'sel2')),
                null,
                array(),
                array());

        // SELECT * FROM nt:file INNER JOIN nt:folder ON ISSAMENODE(sel1, sel2, /home)
        $queries['6.7.9.SameNodeJoinCondition.Path'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('nt:file'),
                    $factory->selector('nt:folder'),
                    Constants::JCR_JOIN_TYPE_INNER,
                    $factory->sameNodeJoinCondition('sel1', 'sel2', '/home')),
                null,
                array(),
                array());

       /**
        * 6.7.10 ChildNodeJoinCondition
        */

        // SELECT * FROM nt:file INNER JOIN nt:folder ON ISCHILDNODE(child, parent)
        $queries['6.7.10.ChildNodeCondition'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('nt:file'),
                    $factory->selector('nt:folder'),
                    Constants::JCR_JOIN_TYPE_INNER,
                    $factory->childNodeJoinCondition('child', 'parent')),
                null,
                array(),
                array());

        /**
        * 6.7.11 DescendantNodeJoinCondition
        */

        // SELECT * FROM nt:file INNER JOIN nt:folder ON ISDESCENDANTNODE(descendant, ancestor)
        $queries['6.7.11.DescendantNodeJoinCondition'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('nt:file'),
                    $factory->selector('nt:folder'),
                    Constants::JCR_JOIN_TYPE_INNER,
                    $factory->descendantNodeJoinCondition('descendant', 'ancestor')),
                null,
                array(),
                array());

        /**
        * 6.7.13. AndConstraint
        */

        // SELECT * FROM nt:file WHERE sel1.prop1 IS NOT NULL AND sel2.prop2 IS NOT NULL
        $queries['6.7.13.And'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->_and(
                    $factory->propertyExistence('prop1', 'sel1'),
                    $factory->propertyExistence('prop2', 'sel2')),
                array(),
                array());

        /**
        * 6.7.14. OrConstraint
        */

        // SELECT * FROM nt:file WHERE sel1.prop1 IS NOT NULL OR sel2.prop2 IS NOT NULL
        $queries['6.7.14.Or'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->_or(
                    $factory->propertyExistence('prop1', 'sel1'),
                    $factory->propertyExistence('prop2', 'sel2')),
                array(),
                array());

        /**
        * 6.7.15. NotConstraint
        */

        // SELECT * FROM nt:file WHERE NOT sel1.prop1 IS NOT NULL
        $queries['6.7.15.Not'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->not(
                    $factory->propertyExistence('prop1', 'sel1')),
                array(),
                array());

        /**
        * 6.7.16. Comparison
        */

        // SELECT * FROM nt:file WHERE NAME(test) LIKE 'literal2'
        $queries['6.7.16.Comparison'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->comparison(
                    $factory->nodeName('test'),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal2')),
                array(),
                array());

        /**
        * 6.7.18. PropertyExistence
        */

        // SELECT * FROM nt:file WHERE sel1.prop1 IS NOT NULL
        $queries['6.7.18.PropertyExistence'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->propertyExistence('prop1', 'sel1'),
                array(),
                array());

        /**
        * 6.7.19. FullTextSearch
        */

        // SELECT * FROM nt:file WHERE CONTAINS(sel.prop, expr)
        // TODO: NOT YET IMPLEMENTED
//        $queries['6.7.19.FullTextSearch'] =
//            $factory->createQuery(
//                $factory->selector('nt:file'),
//                $factory->fullTextSearch('prop', 'expr', 'sel'),
//                array(),
//                array());

        /**
        * 6.7.20. SameNode
        */

        // SELECT * FROM nt:file WHERE ISSAMENODE(/home)
        $queries['6.7.20.SameNode.Simple'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->sameNode('/home'),
                array(),
                array());

        // SELECT * FROM nt:file WHERE ISSAMENODE(sel1, /home)
        $queries['6.7.20.SameNode.Selector'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->sameNode('/home', 'sel1'),
                array(),
                array());

        /**
        * 6.7.21. ChildNode
        */

        // SELECT * FROM nt:file WHERE ISCHILDNODE(/home)
        $queries['6.7.21.ChildNode.Simple'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->childNode('/home'),
                array(),
                array());

        // SELECT * FROM nt:file WHERE ISCHILDNODE(sel1, /home)
        $queries['6.7.21.ChildNode.Selector'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->childNode('/home', 'sel1'),
                array(),
                array());

        /**
        * 6.7.22. DescendantNode
        */

        // SELECT * FROM nt:file WHERE ISDESCENDANTNODE(/home)
        $queries['6.7.22.DescendantNode.Simple'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->descendantNode('/home'),
                array(),
                array());

        // SELECT * FROM nt:file WHERE ISDESCENDANTNODE(sel1, /home)
        $queries['6.7.22.DescendantNode.Selector'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->descendantNode('/home', 'sel1'),
                array(),
                array());

        /**
        * 6.7.27. ProperyValue
        */

        // SELECT * FROM nt:file WHERE sel.prop LIKE 'literal'
        $queries['6.7.27.PropertyValue'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->comparison(
                    $factory->propertyValue('prop', 'sel'),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')),
                array(),
                array());

        /**
        * 6.7.28. Length
        */

        // SELECT * FROM nt:file WHERE LENGTH(prop) LIKE 'literal'
        $queries['6.7.28.Length'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->comparison(
                    $factory->length($factory->propertyValue('prop')),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')),
                array(),
                array());

         /**
        * 6.7.29. NodeName
        */

        // SELECT * FROM nt:file WHERE NAME(sel) LIKE 'literal'
        $queries['6.7.29.NodeName'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->comparison(
                    $factory->nodeName('sel'),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')),
                array(),
                array());

        /**
        * 6.7.30. NodeLocalName
        */

        // SELECT * FROM nt:file WHERE LOCALNAME(sel) LIKE 'literal'
        $queries['6.7.30.NodeLocalName'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->comparison(
                    $factory->nodeLocalName('sel'),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')),
                array(),
                array());

        /**
        * 6.7.31. FullTextSearchScore
        */

        // SELECT * FROM nt:file WHERE SCORE(sel) LIKE 'literal'
        $queries['6.7.31.FullTextSearchScore'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->comparison(
                    $factory->fullTextSearchScore('sel'),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')),
                array(),
                array());

        /**
        * 6.7.32. LowerCase
        */

        // SELECT * FROM nt:file WHERE LOWER(NAME()) LIKE 'literal'
        $queries['6.7.32.LowerCase'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->comparison(
                    $factory->lowerCase($factory->nodeName()),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')),
                array(),
                array());

        /**
        * 6.7.33. UpperCase
        */

        // SELECT * FROM nt:file WHERE UPPER(NAME()) LIKE 'literal'
        $queries['6.7.33.UpperCase'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->comparison(
                    $factory->upperCase($factory->nodeName()),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')),
                array(),
                array());

        /**
        * 6.7.35. BindVariable
        */

        // SELECT * FROM nt:file WHERE UPPER(NAME()) LIKE $var
        $queries['6.7.35.BindValue'] =
            $factory->createQuery(
                $factory->selector('nt:file'),
                $factory->comparison(
                    $factory->upperCase($factory->nodeName()),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->bindVariable('var')),
                array(),
                array());

        /**
        * 6.7.38 Order
        */

        // SELECT * FROM nt:unstructured
        $queries['6.7.38.Order.None'] =
            $factory->createQuery(
                $factory->selector('nt:unstructured'),
                null,
                array(),
                array());

        // SELECT * FROM nt:unstructured ORDER BY prop1 ASC
        $queries['6.7.38.Order.Asc'] =
            $factory->createQuery(
                $factory->selector('nt:unstructured'),
                null,
                array(
                    $factory->ascending($factory->propertyValue('prop1'))),
                array());

        // SELECT * FROM nt:unstructured ORDER BY prop1 DESC
        $queries['6.7.38.Order.Desc'] =
            $factory->createQuery(
                $factory->selector('nt:unstructured'),
                null,
                array(
                    $factory->descending($factory->propertyValue('prop1'))),
                array());

        // SELECT * FROM nt:unstructured ORDER BY prop1 ASC, prop2 DESC
        $queries['6.7.38.Order.Mixed'] =
            $factory->createQuery(
                $factory->selector('nt:unstructured'),
                null,
                array(
                    $factory->ascending($factory->propertyValue('prop1')),
                    $factory->descending($factory->propertyValue('prop2'))),
                array());

        /**
        * 6.7.39 Column
        */

        // SELECT * FROM nt:unstructured
        $queries['6.7.39.Colum.Wildcard'] =
            $factory->createQuery(
                $factory->selector('nt:unstructured'),
                null,
                array(),
                array());

        // SELECT prop1 FROM nt:unstructured
        $queries['6.7.39.Colum.Simple'] =
            $factory->createQuery(
                $factory->selector('nt:unstructured'),
                null,
                array(),
                array(
                    $factory->column('prop1')));

        // SELECT prop1 AS col1 FROM nt:unstructured
        $queries['6.7.39.Colum.Named'] =
            $factory->createQuery(
                $factory->selector('nt:unstructured'),
                null,
                array(),
                array(
                    $factory->column('prop1', 'col1')));

        // SELECT sel1.prop1 FROM nt:unstructured
        $queries['6.7.39.Colum.Selector'] =
            $factory->createQuery(
                $factory->selector('nt:unstructured'),
                null,
                array(),
                array(
                    $factory->column('prop1', null, 'sel1')));

        // SELECT prop1, prop2 AS col2, sel3.prop3 AS col3 FROM nt:unstructured
        $queries['6.7.39.Colum.Mixed'] =
            $factory->createQuery(
                $factory->selector('nt:unstructured'),
                null,
                array(),
                array(
                    $factory->column('prop1'),
                    $factory->column('prop2', 'col2'),
                    $factory->column('prop3', 'col3', 'sel3')));

        return $queries;
    }
}