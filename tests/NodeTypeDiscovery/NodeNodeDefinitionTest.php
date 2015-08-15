<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\NodeTypeDiscovery;

use PHPCR\NodeType\NodeDefinitionInterface;
use PHPCR\NodeType\NodeTypeInterface;
use PHPCR\NodeType\NodeTypeManagerInterface;
use PHPCR\Test\BaseCase;

/**
 * Test reading NodeDefinition from Nodes §8.
 *
 * @see NodeInterface::getDefinition
 */
class NodeNodeDefinitionTest extends BaseCase
{
    public function testGetNodeDefinitionExact()
    {
        // an nt:file must have a jcr:content property
        $node = $this->rootNode->getNode('tests_general_base/numberPropertyNode/jcr:content');
        $nodeDef = $node->getDefinition();
        $this->assertInstanceOf('PHPCR\\NodeType\\NodeDefinitionInterface', $nodeDef);
        $this->assertEquals('jcr:content', $nodeDef->getName());
        $this->assertTrue($nodeDef->isMandatory());
    }

    public function testGetNodeDefinitionWildcard()
    {
        // defines a child of nt:folder
        $node = $this->rootNode->getNode('tests_general_base/index.txt');
        $nodeDef = $node->getDefinition();
        $this->assertInstanceOf('PHPCR\\NodeType\\NodeDefinitionInterface', $nodeDef);
        $this->assertEquals('*', $nodeDef->getName());
    }
}
