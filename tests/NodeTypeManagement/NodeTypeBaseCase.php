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

use PHPCR\NodeType\NodeTypeInterface;
use PHPCR\WorkspaceInterface;
use PHPCR\SessionInterface;
use PHPCR\NodeType\NodeTypeDefinitionInterface;
use PHPCR\Test\BaseCase;

/**
 * Covering jcr-2.8.3 spec $19.
 */
abstract class NodeTypeBaseCase extends BaseCase
{
    /**
     * @var WorkspaceInterface
     */
    protected $workspace;
    /**
     * @var SessionInterface
     */
    protected $session;

    protected function setUp()
    {
        $this->renewSession(); // reset session
        parent::setUp();

        $this->workspace = $this->session->getWorkspace();
    }

    /**
     * Register the node types cnd/classes.
     *
     * @param bool $allowUpdate
     *
     * @return NodeTypeInterface[] registered node types, like
     *      NodeTypeManagerInterface returns them.
     */
    abstract protected function registerNodeTypes($allowUpdate);

    /**
     * Register the node type cnd/class that defines a primary item.
     *
     * @param bool $allowUpdate
     *
     * @return NodeTypeInterface[] registered node types, like
     *      NodeTypeManagerInterface returns them.
     */
    abstract protected function registerNodeTypePrimaryItem();

    /**
     * Try to register a node type with an object or cnd that would overwrite
     * a build-in node type, e.g. nt:file.
     *
     * Have allowUpdate true, should still fail.
     */
    abstract protected function registerBuiltinNodeType();

    public function testRegisterNodeTypes()
    {
        $types = (array) $this->registerNodeTypes(true);

        $this->assertTypes($types);

        $session = $this->renewSession();
        $ntm = $session->getWorkspace()->getNodeTypeManager();

        $this->assertTrue($ntm->hasNodeType('phpcr:apitest'));
        $this->assertTrue($ntm->hasNodeType('phpcr:test'));

        $types['phpcr:apitest'] = $ntm->getNodeType('phpcr:apitest');
        $types['phpcr:test'] = $ntm->getNodeType('phpcr:test');

        reset($types);

        $this->assertTypes($types);
    }

    protected function assertTypes($types)
    {
        $this->assertCount(2, $types, 'Wrong number of nodes registered');

        // apitest
        list($name, $type) = each($types);
        $this->assertEquals('phpcr:apitest', $name);
        $this->assertInstanceOf('PHPCR\NodeType\NodeTypeDefinitionInterface', $type);
        /* @var $type NodeTypeDefinitionInterface */
        $props = $type->getDeclaredPropertyDefinitions();
        $this->assertCount(1, $props, 'Wrong number of properties in phpcr:apitest');
        $this->assertEquals('phpcr:class', $props[0]->getName());
        $this->assertTrue($props[0]->isMultiple());

        // test
        list($name, $type) = each($types);
        $this->assertEquals('phpcr:test', $name);
        $this->assertInstanceOf('PHPCR\NodeType\NodeTypeDefinitionInterface', $type);
        /* @var $type NodeTypeDefinitionInterface */
        $props = $type->getDeclaredPropertyDefinitions();
        $this->assertCount(1, $props, 'Wrong number of properties in phpcr:test');
        $this->assertEquals('phpcr:prop', $props[0]->getName());
        $this->assertFalse($props[0]->isMultiple());
    }

    /**
     * @depends testRegisterNodeTypes
     */
    public function testValidateCustomNodeType()
    {
        $node = $this->rootNode->getNode('tests_general_base');
        try {
            // the node is of type nt:folder - it can only have allowed properties
            $node->setProperty('phpcr:prop', 'test');
            $this->session->save();
            $this->fail('This node should not accept the property');
        } catch (\PHPCR\NodeType\ConstraintViolationException $e) {
            // expected
        }
        $node->addMixin('phpcr:test');
        $node->setProperty('phpcr:prop', 'test');

        $node->addMixin('phpcr:apitest');
        try {
            $node->setProperty('phpcr:class', 'x');
            $this->session->save();
            $this->fail('This property was multivalue');
        } catch (\PHPCR\NodeType\ConstraintViolationException $e) {
            // expected
        }

        $node->setProperty('phpcr:class', array('x', 'y'));
        $this->session->save();
    }

    /**
     * @expectedException \PHPCR\NodeType\NodeTypeExistsException
     */
    public function testRegisterNodeTypesNoUpdate()
    {
        $this->registerNodeTypes(false); // if the node types exist from previous, this fails
        $this->registerNodeTypes(false); // otherwise it must fail here
    }

    public function testPrimaryItem()
    {
        $this->registerNodeTypePrimaryItem();

        // Create a node of that type
        $root = $this->session->getRootNode();

        if ($root->hasNode('test_node')) {
            $node = $root->getNode('test_node');
            $node->remove();
            $this->session->save();
        }

        $node = $root->addNode('test_node', 'phpcr:primary_item_test');
        $node->setProperty('phpcr:content', 'test');
        $this->session->save();

        // Check the primary item of the new node
        $primary = $node->getPrimaryItem();
        $this->assertInstanceOf('PHPCR\ItemInterface', $node);
        $this->assertEquals('phpcr:content', $primary->getName());
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testOverwriteBuiltinNodeType()
    {
        $this->registerBuiltinNodeType();
    }
}
