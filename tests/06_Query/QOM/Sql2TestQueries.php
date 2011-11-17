<?php
namespace PHPCR\Tests\Query\QOM;

/**
* Test queries for Sql2 language
*
* The SQL2 queries defined here correspond to the QOM queries defined in QomTestQueries.
* @see QomTestQueries
*/
class Sql2TestQueries {

    public static function getQueries() {

        $queries = array();

        /**
        * 6.7.3. Selector
        */
        $queries['6.7.3.Selector.Simple'] = 'SELECT * FROM [nt:unstructured]';
        $queries['6.7.3.Selector.Named'] = 'SELECT * FROM [nt:unstructured] AS test';

        /**
        * 6.7.8. EquiJoinCondition
        */
        $queries['6.7.8.EquiJoin.Inner'] = 'SELECT * FROM [nt:file] INNER JOIN [nt:folder] ON sel1.prop1=sel2.prop2';
        $queries['6.7.8.EquiJoin.Left'] = 'SELECT * FROM [nt:file] LEFT OUTER JOIN [nt:folder] ON sel1.prop1=sel2.prop2';
        $queries['6.7.8.EquiJoin.Right'] = 'SELECT * FROM [nt:file] RIGHT OUTER JOIN [nt:folder] ON sel1.prop1=sel2.prop2';

        /**
        * 6.7.9. SameNodeJoinCondition
        */
        $queries['6.7.9.SameNodeJoinCondition.Simple'] = 'SELECT * FROM [nt:file] INNER JOIN [nt:folder] ON ISSAMENODE(sel1, sel2)';
        $queries['6.7.9.SameNodeJoinCondition.Path'] = 'SELECT * FROM [nt:file] INNER JOIN [nt:folder] ON ISSAMENODE(sel1, sel2, [/home])';

        /**
        * 6.7.10 ChildNodeJoinCondition
        */
        $queries['6.7.10.ChildNodeCondition'] = 'SELECT * FROM [nt:file] INNER JOIN [nt:folder] ON ISCHILDNODE(child, parent)';

        /**
        * 6.7.11 DescendantNodeJoinCondition
        */
        $queries['6.7.11.DescendantNodeJoinCondition'] = 'SELECT * FROM [nt:file] INNER JOIN [nt:folder] ON ISDESCENDANTNODE(descendant, ancestor)';

        /**
        * 6.7.13. AndConstraint
        */
        $queries['6.7.13.And'] = 'SELECT * FROM [nt:file] WHERE sel1.prop1 IS NOT NULL AND sel2.prop2 IS NOT NULL';

        /**
        * 6.7.14. OrConstraint
        */
        $queries['6.7.14.Or'] = 'SELECT * FROM [nt:file] WHERE sel1.prop1 IS NOT NULL OR sel2.prop2 IS NOT NULL';

        /**
        * 6.7.15. NotConstraint
        */
        $queries['6.7.15.Not'] = 'SELECT * FROM [nt:file] WHERE NOT sel1.prop1 IS NOT NULL';

        /**
        * 6.7.16. Comparison
        */
        $queries['6.7.16.Comparison'] = 'SELECT * FROM [nt:file] WHERE NAME(test) LIKE \'literal2\'';;

        /**
        * 6.7.18. PropertyExistence
        */
        $queries['6.7.18.PropertyExistence'] = 'SELECT * FROM [nt:file] WHERE sel1.prop1 IS NOT NULL';

        /**
        * 6.7.19. FullTextSearch
        */
        $queries['6.7.19.FullTextSearch'] = 'SELECT * FROM [nt:file] WHERE CONTAINS(sel.prop, \'expr\')';

        /**
        * 6.7.20. SameNode
        */
        $queries['6.7.20.SameNode.Simple'] = 'SELECT * FROM [nt:file] WHERE ISSAMENODE([/home])';
        $queries['6.7.20.SameNode.Selector'] = 'SELECT * FROM [nt:file] WHERE ISSAMENODE(sel1, [/home])';

        /**
        * 6.7.21. ChildNode
        */
        $queries['6.7.21.ChildNode.Simple'] = 'SELECT * FROM [nt:file] WHERE ISCHILDNODE([/home])';
        $queries['6.7.21.ChildNode.Selector'] = 'SELECT * FROM [nt:file] WHERE ISCHILDNODE(sel1, [/home])';

        /**
        * 6.7.22. DescendantNode
        */
        $queries['6.7.22.DescendantNode.Simple'] = 'SELECT * FROM [nt:file] WHERE ISDESCENDANTNODE([/home])';
        $queries['6.7.22.DescendantNode.Selector'] = 'SELECT * FROM [nt:file] WHERE ISDESCENDANTNODE(sel1, [/home])';

        /**
        * 6.7.27. ProperyValue
        */
        $queries['6.7.27.PropertyValue'] = 'SELECT * FROM [nt:file] WHERE sel.prop LIKE \'literal\'';

        /**
        * 6.7.28. Length
        */
        $queries['6.7.28.Length'] = 'SELECT * FROM [nt:file] WHERE LENGTH(prop) LIKE \'literal\'';

        /**
        * 6.7.29. NodeName
        */
        $queries['6.7.29.NodeName'] = 'SELECT * FROM [nt:file] WHERE NAME(sel) LIKE \'literal\'';

        /**
        * 6.7.30. NodeLocalName
        */
        $queries['6.7.30.NodeLocalName'] = 'SELECT * FROM [nt:file] WHERE LOCALNAME(sel) LIKE \'literal\'';

        /**
        * 6.7.31. FullTextSearchScore
        */
        $queries['6.7.31.FullTextSearchScore'] = 'SELECT * FROM [nt:file] WHERE SCORE(sel) LIKE \'literal\'';

        /**
        * 6.7.32. LowerCase
        */
        $queries['6.7.32.LowerCase'] = 'SELECT * FROM [nt:file] WHERE LOWER(NAME()) LIKE \'literal\'';

        /**
        * 6.7.33. UpperCase
        */
        $queries['6.7.33.UpperCase'] = 'SELECT * FROM [nt:file] WHERE UPPER(NAME()) LIKE \'literal\'';

        /**
        * 6.7.35. BindVariable
        */
        $queries['6.7.35.BindValue'] = 'SELECT * FROM [nt:file] WHERE UPPER(NAME()) LIKE $var';


        /**
        * 6.7.38 Order
        */
        $queries['6.7.38.Order.None'] = 'SELECT * FROM [nt:unstructured]';
        $queries['6.7.38.Order.Asc'] = 'SELECT * FROM [nt:unstructured] ORDER BY prop1 ASC';
        $queries['6.7.38.Order.Desc'] = 'SELECT * FROM [nt:unstructured] ORDER BY prop1 DESC';
        $queries['6.7.38.Order.Mixed'] = 'SELECT * FROM [nt:unstructured] ORDER BY prop1 ASC, prop2 DESC';

        /**
        * 6.7.39 Column
        */
        $queries['6.7.39.Colum.Wildcard'] = 'SELECT * FROM [nt:unstructured]';
        $queries['6.7.39.Colum.Simple'] = 'SELECT prop1 FROM [nt:unstructured]';
        $queries['6.7.39.Colum.Named'] = 'SELECT prop1 AS col1 FROM [nt:unstructured]';
        $queries['6.7.39.Colum.Selector'] = 'SELECT sel1.prop1 FROM [nt:unstructured]';
        $queries['6.7.39.Colum.Mixed'] = 'SELECT prop1, prop2 as col2, sel3.prop3 as col3 FROM [nt:unstructured]';

        return $queries;
    }
}
