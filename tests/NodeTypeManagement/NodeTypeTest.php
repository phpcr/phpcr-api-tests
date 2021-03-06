<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\NodeTypeManagement;

use PHPCR\NodeType\NodeTypeDefinitionInterface;
use PHPCR\PropertyType;

/**
 * Covering jcr-2.8.3 spec $19.
 */
class NodeTypeTest extends NodeTypeBaseCase
{
    public function testRegisterNodeType()
    {
        $ns = $this->workspace->getNamespaceRegistry();
        $ns->registerNamespace('phpcr', 'http://www.doctrine-project.org/projects/phpcr_odm');

        $ntm = $this->workspace->getNodeTypeManager();

        $apitest = $ntm->createNodeTypeTemplate();
        $apitest->setName('phpcr:apitest');
        $apitest->setMixin(true);

        $class = $ntm->createPropertyDefinitionTemplate();
        $class->setName('phpcr:class');
        $class->setRequiredType(PropertyType::STRING);
        $class->setMultiple(true);
        $apitest->getPropertyDefinitionTemplates()->append($class);

        $type = $ntm->registerNodeType($apitest, true);

        $this->assertApitestType($type);

        $session = $this->renewSession();
        $ntm = $session->getWorkspace()->getNodeTypeManager();

        $this->assertTrue($ntm->hasNodeType('phpcr:apitest'));
        $this->assertApitestType($ntm->getNodeType('phpcr:apitest'));
    }

    private function assertApitestType($type)
    {
        $this->assertInstanceOf(NodeTypeDefinitionInterface::class, $type);
        /* @var $type NodeTypeDefinitionInterface */
        $this->assertEquals('phpcr:apitest', $type->getName());
        $props = $type->getDeclaredPropertyDefinitions();
        $this->assertCount(1, $props, 'Wrong number of properties in phpcr:apitest');
        $this->assertEquals('phpcr:class', $props[0]->getName());
        $this->assertTrue($props[0]->isMultiple());
    }

    protected function registerNodeTypes($allowUpdate)
    {
        $ns = $this->workspace->getNamespaceRegistry();
        $ns->registerNamespace('phpcr', 'http://www.doctrine-project.org/projects/phpcr_odm');

        $ntm = $this->workspace->getNodeTypeManager();

        $apitest = $ntm->createNodeTypeTemplate();
        $apitest->setName('phpcr:apitest');
        $apitest->setMixin(true);

        $class = $ntm->createPropertyDefinitionTemplate();
        $class->setName('phpcr:class');
        $class->setRequiredType(PropertyType::STRING);
        $class->setMultiple(true);
        $apitest->getPropertyDefinitionTemplates()->append($class);
        $nodeTypes[] = $apitest;

        $test = $ntm->createNodeTypeTemplate();
        $test->setName('phpcr:test');
        $test->setMixin(true);

        $prop = $ntm->createPropertyDefinitionTemplate();
        $prop->setName('phpcr:prop');
        $prop->setRequiredType(PropertyType::STRING);
        $test->getPropertyDefinitionTemplates()->append($prop);
        $nodeTypes[] = $test;

        return $ntm->registerNodeTypes($nodeTypes, $allowUpdate);
    }

    protected function registerNodeTypePrimaryItem()
    {
        $ns = $this->workspace->getNamespaceRegistry();
        $ns->registerNamespace('phpcr', 'http://www.doctrine-project.org/projects/phpcr_odm');

        $ntm = $this->workspace->getNodeTypeManager();

        $test = $ntm->createNodeTypeTemplate();
        $test->setName('phpcr:primary_item_test');

        $prop = $ntm->createPropertyDefinitionTemplate();
        $prop->setName('phpcr:content');
        $prop->setRequiredType(PropertyType::STRING);
        $test->getPropertyDefinitionTemplates()->append($prop);
        $test->setPrimaryItemName('phpcr:content');
        $nodeTypes[] = $test;

        return $ntm->registerNodeTypes($nodeTypes, true);
    }

    protected function registerBuiltinNodeType()
    {
        $ntm = $this->workspace->getNodeTypeManager();

        $test = $ntm->createNodeTypeTemplate();
        $test->setName('nt:file');

        $prop = $ntm->createPropertyDefinitionTemplate();
        $prop->setName('x');
        $prop->setRequiredType(PropertyType::STRING);
        $test->getPropertyDefinitionTemplates()->append($prop);

        $nodeTypes[] = $test;

        return $ntm->registerNodeTypes($nodeTypes, true);
    }
}
