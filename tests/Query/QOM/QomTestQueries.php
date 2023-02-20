<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Query\QOM;

use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;
use PHPCR\Query\QOM\QueryObjectModelFactoryInterface;

/**
* Test queries for QOM language.
*
* The QOM queries defined here correspond to the SQL2 queries defined in Sql2TestQueries.
*
* @see Sql2TestQueries
*/
class QomTestQueries
{
    public static function getQueries(QueryObjectModelFactoryInterface $factory)
    {
        $queries = [];

        /*
        * 6.7.3. Selector
        */
        // SELECT * FROM nt:unstructured as test
        $queries['6.7.3.Selector.Named'] =
            $factory->createQuery(
                $factory->selector('test', 'nt:unstructured'),
                null,
                [],
                []
            )
        ;

        /*
        * 6.7.8. EquiJoinCondition
        */

        // SELECT * FROM nt:file INNER JOIN nt:folder ON sel1.prop1=sel2.prop2
        $queries['6.7.8.EquiJoin.Inner'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('file', 'nt:file'),
                    $factory->selector('folder', 'nt:folder'),
                    Constants::JCR_JOIN_TYPE_INNER,
                    $factory->equiJoinCondition('file', 'prop1', 'folder', 'prop2')
                ),
                null,
                [],
                []
            )
        ;

        // SELECT * FROM nt:file LEFT OUTER JOIN nt:folder ON sel1.prop1=sel2.prop2
        $queries['6.7.8.EquiJoin.Left'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('file', 'nt:file'),
                    $factory->selector('folder', 'nt:folder'),
                    Constants::JCR_JOIN_TYPE_LEFT_OUTER,
                    $factory->equiJoinCondition('file', 'prop1', 'folder', 'prop2')
                ),
                null,
                [],
                []
            )
        ;

        // SELECT * FROM nt:file RIGHT OUTER JOIN nt:folder ON sel1.prop1=sel2.prop2
        $queries['6.7.8.EquiJoin.Right'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('file', 'nt:file'),
                    $factory->selector('folder', 'nt:folder'),
                    Constants::JCR_JOIN_TYPE_RIGHT_OUTER,
                    $factory->equiJoinCondition('file', 'prop1', 'folder', 'prop2')
                ),
                null,
                [],
                []
            )
        ;

        // SELECT * FROM [nt:folder] AS folder INNER JOIN [nt:file] AS file ON folder.[prop2]=file.[prop1] INNER JOIN [nt:folder] AS folder2 ON file.[prop1]=folder.[prop2]
        $queries['6.7.8.EquiJoin.NestedJoin'] =
            $factory->createQuery(
                $factory->join(
                    $factory->join(
                        $factory->selector('folder', 'nt:folder'),
                        $factory->selector('file', 'nt:file'),
                        Constants::JCR_JOIN_TYPE_INNER,
                        $factory->equiJoinCondition('folder', 'prop2', 'file', 'prop1')
                    ),
                    $factory->selector('folder2', 'nt:folder'),
                    Constants::JCR_JOIN_TYPE_INNER,
                    $factory->equiJoinCondition('file', 'prop1', 'folder', 'prop2')
                ),
                null,
                [],
                []
            )
        ;

        /*
        * 6.7.9. SameNodeJoinCondition
        */

        // SELECT * FROM nt:file INNER JOIN nt:folder ON ISSAMENODE(sel1, sel2)
        $queries['6.7.9.SameNodeJoinCondition.Simple'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('file', 'nt:file'),
                    $factory->selector('folder', 'nt:folder'),
                    Constants::JCR_JOIN_TYPE_INNER,
                    $factory->sameNodeJoinCondition('file', 'folder')
                ),
                null,
                [],
                []
            )
        ;

        // SELECT * FROM nt:file INNER JOIN nt:folder ON ISSAMENODE(sel1, sel2, /home)
        $queries['6.7.9.SameNodeJoinCondition.Path'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('file', 'nt:file'),
                    $factory->selector('folder', 'nt:folder'),
                    Constants::JCR_JOIN_TYPE_INNER,
                    $factory->sameNodeJoinCondition('file', 'folder', '/home')
                ),
                null,
                [],
                []
            )
        ;

        /*
         * 6.7.10 ChildNodeJoinCondition
         */

        // SELECT * FROM nt:file INNER JOIN nt:folder ON ISCHILDNODE(child, parent)
        $queries['6.7.10.ChildNodeCondition'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('child', 'nt:file'),
                    $factory->selector('parent', 'nt:folder'),
                    Constants::JCR_JOIN_TYPE_INNER,
                    $factory->childNodeJoinCondition('child', 'parent')
                ),
                null,
                [],
                []
            )
        ;

        /*
        * 6.7.11 DescendantNodeJoinCondition
        */

        // SELECT * FROM nt:file INNER JOIN nt:folder ON ISDESCENDANTNODE(descendant, ancestor)
        $queries['6.7.11.DescendantNodeJoinCondition'] =
            $factory->createQuery(
                $factory->join(
                    $factory->selector('descendant', 'nt:file'),
                    $factory->selector('ancestor', 'nt:folder'),
                    Constants::JCR_JOIN_TYPE_INNER,
                    $factory->descendantNodeJoinCondition('descendant', 'ancestor')
                ),
                null,
                [],
                []
            )
        ;

        /*
        * 6.7.12. Constraint (operator precedence)
        */
        $queries['6.7.12.Constraint.Precedence.1'] = $factory->createQuery(
            $factory->selector('file', 'nt:file'),
            $factory->orConstraint(
                $factory->comparison(
                    $factory->propertyValue('file', 'prop1'),
                    Constants::JCR_OPERATOR_EQUAL_TO,
                    $factory->literal('1')
                ),
                $factory->andConstraint(
                    $factory->comparison(
                        $factory->propertyValue('file', 'prop2'),
                        Constants::JCR_OPERATOR_EQUAL_TO,
                        $factory->literal('2')
                    ),
                    $factory->comparison(
                        $factory->propertyValue('file', 'prop3'),
                        Constants::JCR_OPERATOR_EQUAL_TO,
                        $factory->literal('3')
                    )
                )
            ),
            [],
            []
        );

        $queries['6.7.12.Constraint.Precedence.2'] = $factory->createQuery(
            $factory->selector('file', 'nt:file'),
            $factory->orConstraint(
                $factory->andConstraint(
                    $factory->comparison(
                        $factory->propertyValue('file', 'prop1'),
                        Constants::JCR_OPERATOR_EQUAL_TO,
                        $factory->literal('1')
                    ),
                    $factory->comparison(
                        $factory->propertyValue('file', 'prop2'),
                        Constants::JCR_OPERATOR_EQUAL_TO,
                        $factory->literal('2')
                    )
                ),
                $factory->comparison(
                    $factory->propertyValue('file', 'prop3'),
                    Constants::JCR_OPERATOR_EQUAL_TO,
                    $factory->literal('3')
                )
            ),
            [],
            []
        );

        $queries['6.7.12.Constraint.Precedence.3'] = $factory->createQuery(
            $factory->selector('file', 'nt:file'),
            $factory->orConstraint(
                $factory->notConstraint(
                    $factory->comparison(
                        $factory->propertyValue('file', 'prop1'),
                        Constants::JCR_OPERATOR_EQUAL_TO,
                        $factory->literal('1')
                    )
                ),
                $factory->andConstraint(
                    $factory->comparison(
                        $factory->propertyValue('file', 'prop2'),
                        Constants::JCR_OPERATOR_EQUAL_TO,
                        $factory->literal('2')
                    ),
                    $factory->notConstraint(
                        $factory->comparison(
                            $factory->propertyValue('file', 'prop3'),
                            Constants::JCR_OPERATOR_EQUAL_TO,
                            $factory->literal('3')
                        )
                    )
                )
            ),
            [],
            []
        );

        $queries['6.7.12.Constraint.Precedence.4'] = $factory->createQuery(
            $factory->selector('file', 'nt:file'),
            $factory->orConstraint(
                $factory->andConstraint(
                    $factory->andConstraint(
                        $factory->propertyExistence('file', 'prop1'),
                        $factory->propertyExistence('file', 'prop2')
                    ),
                    $factory->propertyExistence('file', 'prop3')
                ),
                $factory->andConstraint(
                    $factory->andConstraint(
                        $factory->andConstraint(
                            $factory->propertyExistence('file', 'prop4'),
                            $factory->propertyExistence('file', 'prop5')
                        ),
                        $factory->propertyExistence('file', 'prop6')
                    ),
                    $factory->propertyExistence('file', 'prop7')
                )
            ),
            [],
            []
        );

        $queries['6.7.12.Constraint.Precedence.5'] = $factory->createQuery(
            $factory->selector('file', 'nt:file'),
            $factory->orConstraint(
                $factory->andConstraint(
                    $factory->notConstraint(
                        $factory->propertyExistence('file', 'prop1')
                    ),
                    $factory->notConstraint(
                        $factory->notConstraint(
                            $factory->propertyExistence('file', 'prop2')
                        )
                    )
                ),
                $factory->andConstraint(
                    $factory->notConstraint(
                        $factory->comparison(
                            $factory->propertyValue('file', 'prop3'),
                            Constants::JCR_OPERATOR_EQUAL_TO,
                            $factory->literal('hello')
                        )
                    ),
                    $factory->comparison(
                        $factory->propertyValue('file', 'prop4'),
                        Constants::JCR_OPERATOR_NOT_EQUAL_TO,
                        $factory->literal('hello')
                    )
                )
            ),
            [],
            []
        );

        /*
        * 6.7.13. AndConstraint
        */

        // SELECT * FROM nt:file WHERE sel1.prop1 IS NOT NULL AND sel2.prop2 IS NOT NULL
        $queries['6.7.13.And'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->andConstraint(
                    $factory->propertyExistence('file', 'prop1'),
                    $factory->propertyExistence('file', 'prop2')
                ),
                [],
                []
            )
        ;

        /*
        * 6.7.14. OrConstraint
        */

        // SELECT * FROM nt:file WHERE sel1.prop1 IS NOT NULL OR sel2.prop2 IS NOT NULL
        $queries['6.7.14.Or'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->orConstraint(
                    $factory->propertyExistence('file', 'prop1'),
                    $factory->propertyExistence('file', 'prop2')
                ),
                [],
                []
            )
        ;

        /*
        * 6.7.15. NotConstraint
        */

        // SELECT * FROM nt:file WHERE NOT sel1.prop1 IS NOT NULL
        $queries['6.7.15.Not'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->notConstraint(
                    $factory->propertyExistence('file', 'prop1')
                ),
                [],
                []
            )
        ;

        /*
        * 6.7.16. Comparison
        */

        // SELECT * FROM nt:file WHERE NAME(test) LIKE 'literal2'
        $queries['6.7.16.Comparison'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->comparison(
                    $factory->nodeName('file'),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal2')
                ),
                [],
                []
            )
        ;

        /*
        * 6.7.18. PropertyExistence
        */

        // SELECT * FROM nt:file WHERE sel1.prop1 IS NOT NULL
        $queries['6.7.18.PropertyExistence'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->propertyExistence('file', 'prop1'),
                [],
                []
            )
        ;

        /*
        * 6.7.19. FullTextSearch
        */

        // SELECT * FROM nt:file WHERE CONTAINS(sel.prop, expr)
        $queries['6.7.19.FullTextSearch'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->fullTextSearch('file', 'prop', 'expr'),
                [],
                []
            )
        ;

        $queries['6.7.19.FullTextSearch_With_Single_Quote'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->fullTextSearch('file', 'prop', "expr'"),
                [],
                []
            )
        ;

        /*
        * 6.7.20. SameNode
        */
        // SELECT * FROM [nt:file] AS file WHERE ISSAMENODE(file, /home)
        $queries['6.7.20.SameNode.Selector'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->sameNode('file', '/home'),
                [],
                []
            )
        ;

        $queries['6.7.20.SameNode.Selector_Space'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->sameNode('file', '/home node'),
                [],
                []
            )
        ;

        /*
        * 6.7.21. ChildNode
        */
        // SELECT * FROM [nt:file] AS file WHERE ISCHILDNODE(file, /home)
        $queries['6.7.21.ChildNode.Selector'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->childNode('file', '/home'),
                [],
                []
            )
        ;

        /*
        * 6.7.22. DescendantNode
        */
        // SELECT * FROM [nt:file] AS file WHERE ISDESCENDANTNODE(file, /home)
        $queries['6.7.22.DescendantNode.Selector'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->descendantNode('file', '/home'),
                [],
                []
            )
        ;

        /*
        * 6.7.27. ProperyValue
        */

        // SELECT * FROM [nt:file] AS file WHERE file.prop LIKE 'literal'
        $queries['6.7.27.PropertyValue'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->comparison(
                    $factory->propertyValue('file', 'prop'),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')
                ),
                [],
                []
            )
        ;

        // SELECT * FROM nt:unstructured WHERE sel.prop > '2013-04-15'
        $queries['6.7.27.1.PropertyValue'] =
            $factory->createQuery(
                $factory->selector('sel', 'nt:unstructured'),
                $factory->comparison(
                    $factory->propertyValue('sel', 'prop'),
                    Constants::JCR_OPERATOR_GREATER_THAN,
                    $factory->literal(new \DateTime('2013-04-15 +02:00'))
                ),
                [],
                []
            )
        ;

        /*
        * 6.7.28. Length
        */

        // SELECT * FROM [nt:file] AS file WHERE LENGTH(file.prop) LIKE 'literal'
        $queries['6.7.28.Length'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->comparison(
                    $factory->length($factory->propertyValue('file', 'prop')),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')
                ),
                [],
                []
            )
        ;

        /*
        * 6.7.29. NodeName
        */

        // SELECT * FROM [nt:file] AS file WHERE NAME(file) LIKE 'literal'
        $queries['6.7.29.NodeName'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->comparison(
                    $factory->nodeName('file'),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')
                ),
                [],
                []
            )
        ;

        /*
        * 6.7.30. NodeLocalName
        */

        // SELECT * FROM [nt:file] AS file WHERE LOCALNAME(file) LIKE 'literal'
        $queries['6.7.30.NodeLocalName'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->comparison(
                    $factory->nodeLocalName('file'),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')
                ),
                [],
                []
            )
        ;

        /*
        * 6.7.31. FullTextSearchScore
        */

        // SELECT * FROM [nt:file] AS file WHERE SCORE(file) LIKE 'literal'
        $queries['6.7.31.FullTextSearchScore'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->comparison(
                    $factory->fullTextSearchScore('file'),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')
                ),
                [],
                []
            )
        ;

        /*
        * 6.7.32. LowerCase
        */

        // SELECT * FROM [nt:file] AS file WHERE LOWER(NAME(file)) LIKE 'literal'
        $queries['6.7.32.LowerCase'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->comparison(
                    $factory->lowerCase($factory->nodeName('file')),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')
                ),
                [],
                []
            )
        ;

        /*
        * 6.7.33. UpperCase
        */

        // SELECT * FROM [nt:file] AS file WHERE UPPER(NAME()) LIKE 'literal'
        $queries['6.7.33.UpperCase'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->comparison(
                    $factory->upperCase($factory->nodeName('file')),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->literal('literal')
                ),
                [],
                []
            )
        ;

        /*
        * 6.7.35. BindVariable
        */

        // SELECT * FROM [nt:file] AS file WHERE UPPER(NAME(file)) LIKE $var
        $queries['6.7.35.BindValue'] =
            $factory->createQuery(
                $factory->selector('file', 'nt:file'),
                $factory->comparison(
                    $factory->upperCase($factory->nodeName('file')),
                    Constants::JCR_OPERATOR_LIKE,
                    $factory->bindVariable('var')
                ),
                [],
                []
            )
        ;

        /*
        * 6.7.38 Order
        */

        // SELECT * FROM nt:unstructured
        $queries['6.7.38.Order.None'] =
            $factory->createQuery(
                $factory->selector('u', 'nt:unstructured'),
                null,
                [],
                []
            )
        ;

        // SELECT * FROM nt:unstructured ORDER BY prop1 ASC
        $queries['6.7.38.Order.Asc'] =
            $factory->createQuery(
                $factory->selector('u', 'nt:unstructured'),
                null,
                [$factory->ascending($factory->propertyValue('u', 'prop1'))],
                []
            )
        ;

        // SELECT * FROM nt:unstructured ORDER BY prop1 DESC
        $queries['6.7.38.Order.Desc'] =
            $factory->createQuery(
                $factory->selector('u', 'nt:unstructured'),
                null,
                [$factory->descending($factory->propertyValue('u', 'prop1'))],
                []
            )
        ;

        // SELECT * FROM nt:unstructured ORDER BY prop1 ASC, prop2 DESC
        $queries['6.7.38.Order.Mixed'] =
            $factory->createQuery(
                $factory->selector('u', 'nt:unstructured'),
                null,
                [
                    $factory->ascending($factory->propertyValue('u', 'prop1')),
                    $factory->descending($factory->propertyValue('u', 'prop2'))
                ],
                []
            )
        ;

        /*
        * 6.7.39 Column
        */

        // SELECT * FROM nt:unstructured
        $queries['6.7.39.Colum.Wildcard'] =
            $factory->createQuery(
                $factory->selector('u', 'nt:unstructured'),
                null,
                [],
                []
            )
        ;

        // SELECT u.prop1 AS prop1 FROM [nt:unstructured] AS u
        $queries['6.7.39.Colum.Selector'] =
            $factory->createQuery(
                $factory->selector('u', 'nt:unstructured'),
                null,
                [],
                [$factory->column('u', 'prop1', 'col1')]
            )
        ;

        // SELECT u.prop1, u.prop2 AS col2 FROM nt:unstructured
        $queries['6.7.39.Colum.Mixed'] =
            $factory->createQuery(
                $factory->selector('u', 'nt:unstructured'),
                null,
                [],
                [
                    $factory->column('u', 'prop1', 'col1'),
                    $factory->column('u', 'prop2', 'prop2'),
                ]
            );

        return $queries;
    }
}
