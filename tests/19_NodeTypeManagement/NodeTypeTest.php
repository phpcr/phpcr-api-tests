<?php
namespace PHPCR\Tests\NodeTypeManagement;

require_once 'NodeTypeBaseCase.php';

use PHPCR\PropertyType;

/**
 * Covering jcr-2.8.3 spec $19
 */
class NodeTypeTest extends NodeTypeBaseCase
{
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
}
