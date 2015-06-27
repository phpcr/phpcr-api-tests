<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2013 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\PhpcrUtils;

use PHPCR\NamespaceRegistryInterface;
use PHPCR\PropertyType;
use PHPCR\Util\CND\Writer\CndWriter;
use PHPCR\Version\OnParentVersionAction;
use PHPCR\WorkspaceInterface;

class CndWriterTest extends \PHPCR\Test\BaseCase
{
    /**
     * the "worst case" example from http://jackrabbit.apache.org/node-type-notation.html.
     */
    public function testWorstCaseExample()
    {
        $cnd = <<<EOT
<ns='http://namespace.com/ns'>
<ex='http://namespace.com/example'>
[ns:NodeType] > ns:ParentType1, ns:ParentType2
orderable mixin query
- ex:property (String)
= 'default1', 'default2'
mandatory autocreated protected multiple VERSION
< 'constraint1', 'constraint2'
+ ns:node (ns:reqType1, ns:reqType2)
= ns:defaultType
mandatory autocreated protected VERSION

EOT;

        /** @var $workspace WorkspaceInterface */
        $workspace = $this->session->getWorkspace();
        $ntm = $workspace->getNodeTypeManager();

        $tpl = $ntm->createNodeTypeTemplate();
        $tpl->setName('ns:NodeType');
        $tpl->setMixin(true);
        $tpl->setDeclaredSuperTypeNames(array('ns:ParentType1', 'ns:ParentType2'));
        $tpl->setOrderableChildNodes(true);

        $prop = $ntm->createPropertyDefinitionTemplate();
        $prop->setName('ex:property');
        $prop->setRequiredType(PropertyType::STRING);
        $prop->setDefaultValues(array('default1', 'default2'));
        $prop->setMandatory(true);
        $prop->setAutoCreated(true);
        $prop->setProtected(true);
        $prop->setMultiple(true);
        $prop->setOnParentVersion(OnParentVersionAction::VERSION);
        $prop->setValueConstraints(array('constraint1', 'constraint2'));
        $prop->setFullTextSearchable(true);
        $prop->setQueryOrderable(true);

        $tpl->getPropertyDefinitionTemplates()->append($prop);

        $child = $ntm->createNodeDefinitionTemplate();
        $child->setName('ns:node');
        $child->setRequiredPrimaryTypeNames(array('ns:reqType1', 'ns:reqType2'));
        $child->setDefaultPrimaryTypeName('ns:defaultType');
        $child->setMandatory(true);
        $child->setAutoCreated(true);
        $child->setProtected(true);
        $child->setOnParentVersion(OnParentVersionAction::VERSION);

        $tpl->getNodeDefinitionTemplates()->append($child);

        $ns = $this->getMock('PHPCR\Tests\PhpcrUtils\MockNamespaceRegistry');
        $ns->expects($this->any())
            ->method('getUri')
            ->will($this->returnCallback(
                function ($prefix) {
                    switch ($prefix) {
                        case 'ns':
                            return 'http://namespace.com/ns';
                        case 'ex':
                            return 'http://namespace.com/example';
                        default:
                            throw new \Exception($prefix);
                    }
                }
            ))
        ;
        $cndWriter = new CndWriter($ns);
        $res = $cndWriter->writeString(array($tpl));

        $this->assertEquals($cnd, $res);
    }
}

abstract class MockNamespaceRegistry implements \Iterator, NamespaceRegistryInterface
{
}
