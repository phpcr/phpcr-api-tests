<?php
namespace PHPCR\Tests\Query\QOM;

/**
* Test queries for Sql2 language
*
* The SQL2 queries defined here correspond to the QOM queries defined in QomTestQueries.
* @see QomTestQueries
*/
class Sql2TestQueries
{
    public static function getQueries()
    {
        $queries = array();

        /**
        * 6.7.3. Selector
        */
        $queries['6.7.3.Selector.Named'] = 'SELECT * FROM [nt:unstructured] AS test';

        /**
        * 6.7.8. EquiJoinCondition
        */
        $queries['6.7.8.EquiJoin.Inner'] = 'SELECT * FROM [nt:file] AS file INNER JOIN [nt:folder] AS folder ON file.prop1=folder.prop2';
        $queries['6.7.8.EquiJoin.Left'] = 'SELECT * FROM [nt:file] AS file LEFT OUTER JOIN [nt:folder] AS folder ON file.prop1=folder.prop2';
        $queries['6.7.8.EquiJoin.Right'] = 'SELECT * FROM [nt:file] AS file RIGHT OUTER JOIN [nt:folder] AS folder ON file.prop1=folder.prop2';

        /**
        * 6.7.9. SameNodeJoinCondition
        */
        $queries['6.7.9.SameNodeJoinCondition.Simple'] = 'SELECT * FROM [nt:file] AS file INNER JOIN [nt:folder] AS folder ON ISSAMENODE(file, folder)';
        $queries['6.7.9.SameNodeJoinCondition.Path'] = 'SELECT * FROM [nt:file] AS file INNER JOIN [nt:folder] AS folder ON ISSAMENODE(file, folder, [/home])';
        $queries['6.7.9.SameNodeJoinCondition.Path_Space'] = array(
            'SELECT * FROM [nt:file] AS file INNER JOIN [nt:folder] AS folder ON ISSAMENODE(file, folder, ["/home node"])',
            'SELECT * FROM [nt:file] AS file INNER JOIN [nt:folder] AS folder ON ISSAMENODE(file, folder, [/home node])',
        );

        /**
        * 6.7.10 ChildNodeJoinCondition
        */
        $queries['6.7.10.ChildNodeCondition'] = 'SELECT * FROM [nt:file] AS child INNER JOIN [nt:folder] AS parent ON ISCHILDNODE(child, parent)';

        /**
        * 6.7.11 DescendantNodeJoinCondition
        */
        $queries['6.7.11.DescendantNodeJoinCondition'] = 'SELECT * FROM [nt:file] AS descendant INNER JOIN [nt:folder] AS ancestor ON ISDESCENDANTNODE(descendant, ancestor)';

        /**
        * 6.7.12. Constraint (operator precedence)
        */
        $queries['6.7.12.Constraint.Precedence.1'] = array(
            'SELECT * FROM [nt:file] AS file WHERE file.prop1 = \'1\' OR file.prop2 = \'2\' AND file.prop3 = \'3\'',
            'SELECT * FROM [nt:file] AS file WHERE (file.prop1 = \'1\' OR (file.prop2 = \'2\' AND file.prop3 = \'3\'))',
        );
        $queries['6.7.12.Constraint.Precedence.2'] = array(
            'SELECT * FROM [nt:file] AS file WHERE file.prop1 = \'1\' AND file.prop2 = \'2\' OR file.prop3 = \'3\'',
            'SELECT * FROM [nt:file] AS file WHERE ((file.prop1 = \'1\' AND file.prop2 = \'2\') OR file.prop3 = \'3\')',
        );

        $queries['6.7.12.Constraint.Precedence.3'] = array(
            'SELECT * FROM [nt:file] AS file WHERE NOT file.prop1 = \'1\' OR file.prop2 = \'2\' AND NOT file.prop3 = \'3\'',
            'SELECT * FROM [nt:file] AS file WHERE ((NOT file.prop1 = \'1\') OR (file.prop2 = \'2\' AND (NOT file.prop3 = \'3\')))',
        );

        $queries['6.7.12.Constraint.Precedence.4'] = array(
            'SELECT * FROM [nt:file] AS file WHERE
            file.prop1 IS NOT NULL AND file.prop2 IS NOT NULL
                AND file.prop3 IS NOT NULL
            OR file.prop4 IS NOT NULL AND file.prop5 IS NOT NULL
                AND file.prop6 IS NOT NULL AND file.prop7 IS NOT NULL',

            'SELECT * FROM [nt:file] AS file WHERE (((file.prop1 IS NOT NULL AND file.prop2 IS NOT NULL) AND file.prop3 IS NOT NULL) OR (((file.prop4 IS NOT NULL AND file.prop5 IS NOT NULL) AND file.prop6 IS NOT NULL) AND file.prop7 IS NOT NULL))',
        );

        $queries['6.7.12.Constraint.Precedence.5'] = array(
            'SELECT * FROM [nt:file] AS file WHERE
                NOT file.prop1 IS NOT NULL AND NOT NOT file.prop2 IS NOT NULL
                OR NOT file.prop3 = \'hello\' AND file.prop4 <> \'hello\'',
            'SELECT * FROM [nt:file] AS file WHERE ((NOT file.prop1 IS NOT NULL AND NOT NOT file.prop2 IS NOT NULL) OR (NOT file.prop3 = \'hello\' AND file.prop4 <> \'hello\'))',
            'SELECT * FROM [nt:file] AS file WHERE (((NOT file.prop1 IS NOT NULL) AND (NOT (NOT file.prop2 IS NOT NULL))) OR ((NOT file.prop3 = \'hello\') AND file.prop4 <> \'hello\'))',
        );

        /**
        * 6.7.13. AndConstraint
        */
        $queries['6.7.13.And'] = array(
            'SELECT * FROM [nt:file] AS file WHERE file.prop1 IS NOT NULL AND file.prop2 IS NOT NULL',
            'SELECT * FROM [nt:file] AS file WHERE (file.prop1 IS NOT NULL AND file.prop2 IS NOT NULL)',
        );

        /**
        * 6.7.14. OrConstraint
        */
        $queries['6.7.14.Or'] = array(
            'SELECT * FROM [nt:file] AS file WHERE file.prop1 IS NOT NULL OR file.prop2 IS NOT NULL',
            'SELECT * FROM [nt:file] AS file WHERE (file.prop1 IS NOT NULL OR file.prop2 IS NOT NULL)',
         );

        /**
        * 6.7.15. NotConstraint
        */
        $queries['6.7.15.Not'] = array(
            'SELECT * FROM [nt:file] AS file WHERE NOT file.prop1 IS NOT NULL',
            'SELECT * FROM [nt:file] AS file WHERE (NOT file.prop1 IS NOT NULL)',
        );

        /**
        * 6.7.16. Comparison
        */
        $queries['6.7.16.Comparison'] = 'SELECT * FROM [nt:file] AS file WHERE NAME(file) LIKE \'literal2\'';;

        /**
        * 6.7.18. PropertyExistence
        */
        $queries['6.7.18.PropertyExistence'] = 'SELECT * FROM [nt:file] AS file WHERE file.prop1 IS NOT NULL';

        /**
        * 6.7.19. FullTextSearch
        */
        $queries['6.7.19.FullTextSearch'] = 'SELECT * FROM [nt:file] AS file WHERE CONTAINS(file.prop, \'expr\')';

        /**
        * 6.7.20. SameNode
        */
        $queries['6.7.20.SameNode.Selector'] = 'SELECT * FROM [nt:file] AS file WHERE ISSAMENODE(file, [/home])';
        $queries['6.7.20.SameNode.Selector_Space'] = array(
            'SELECT * FROM [nt:file] AS file WHERE ISSAMENODE(file, ["/home node"])',
            'SELECT * FROM [nt:file] AS file WHERE ISSAMENODE(file, [/home node])',
        );

        /**
        * 6.7.21. ChildNode
        */
        $queries['6.7.21.ChildNode.Selector'] = 'SELECT * FROM [nt:file] AS file WHERE ISCHILDNODE(file, [/home])';
        $queries['6.7.21.ChildNode.Selector_Space'] = array(
            'SELECT * FROM [nt:file] AS file WHERE ISCHILDNODE(file, ["/home node"])',
            'SELECT * FROM [nt:file] AS file WHERE ISCHILDNODE(file, [/home node])',
        );

        /**
        * 6.7.22. DescendantNode
        */
        $queries['6.7.22.DescendantNode.Selector'] = 'SELECT * FROM [nt:file] AS file WHERE ISDESCENDANTNODE(file, [/home])';
        $queries['6.7.22.DescendantNode.Selector_Space'] = array(
            'SELECT * FROM [nt:file] AS file WHERE ISDESCENDANTNODE(file, ["/home node"])',
            'SELECT * FROM [nt:file] AS file WHERE ISDESCENDANTNODE(file, [/home node])'
        );

        /**
        * 6.7.27. PropertyValue
        */
        $queries['6.7.27.PropertyValue'] = 'SELECT * FROM [nt:file] AS file WHERE file.prop LIKE \'literal\'';
        $queries['6.7.27.1.PropertyValue'] = 'SELECT * FROM [nt:unstructured] AS sel WHERE sel.prop > CAST(\'2013-04-15T00:00:00.000+02:00\' AS DATE)';

        /**
        * 6.7.28. Length
        */
        $queries['6.7.28.Length'] = 'SELECT * FROM [nt:file] AS file WHERE LENGTH(file.prop) LIKE \'literal\'';

        /**
        * 6.7.29. NodeName
        */
        $queries['6.7.29.NodeName'] = 'SELECT * FROM [nt:file] AS file WHERE NAME(file) LIKE \'literal\'';

        /**
        * 6.7.30. NodeLocalName
        */
        $queries['6.7.30.NodeLocalName'] = 'SELECT * FROM [nt:file] AS file WHERE LOCALNAME(file) LIKE \'literal\'';

        /**
        * 6.7.31. FullTextSearchScore
        */
        $queries['6.7.31.FullTextSearchScore'] = 'SELECT * FROM [nt:file] AS file WHERE SCORE(file) LIKE \'literal\'';

        /**
        * 6.7.32. LowerCase
        */
        $queries['6.7.32.LowerCase'] = 'SELECT * FROM [nt:file] AS file WHERE LOWER(NAME(file)) LIKE \'literal\'';

        /**
        * 6.7.33. UpperCase
        */
        $queries['6.7.33.UpperCase'] = 'SELECT * FROM [nt:file] AS file WHERE UPPER(NAME(file)) LIKE \'literal\'';

        /**
        * 6.7.35. BindVariable
        */
        $queries['6.7.35.BindValue'] = 'SELECT * FROM [nt:file] AS file WHERE UPPER(NAME(file)) LIKE $var';

        /**
        * 6.7.38 Order
        */
        $queries['6.7.38.Order.None'] = 'SELECT * FROM [nt:unstructured] AS u';
        $queries['6.7.38.Order.Asc'] = 'SELECT * FROM [nt:unstructured] AS u ORDER BY u.prop1 ASC';
        $queries['6.7.38.Order.Desc'] = 'SELECT * FROM [nt:unstructured] AS u ORDER BY u.prop1 DESC';
        $queries['6.7.38.Order.Mixed'] = 'SELECT * FROM [nt:unstructured] AS u ORDER BY u.prop1 ASC, u.prop2 DESC';

        /**
        * 6.7.39 Column
        */
        $queries['6.7.39.Colum.Wildcard'] = 'SELECT * FROM [nt:unstructured] AS u';
        $queries['6.7.39.Colum.Selector'] = 'SELECT u.prop1 AS col1 FROM [nt:unstructured] AS u';
        $queries['6.7.39.Colum.Mixed'] = 'SELECT u.prop1 AS col1, u.prop2 FROM [nt:unstructured] AS u';

        return $queries;
    }
}
