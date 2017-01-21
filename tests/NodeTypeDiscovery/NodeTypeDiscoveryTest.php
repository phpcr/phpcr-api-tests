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

use PHPCR\NodeType\NodeTypeInterface;
use PHPCR\NodeType\NodeTypeManagerInterface;
use PHPCR\NodeType\NoSuchNodeTypeException;
use PHPCR\Test\BaseCase;
use SeekableIterator;

/**
 * Test the NoteTypeManager §8.
 */
class NodeTypeDiscoveryTest extends BaseCase
{
    /**
     * @var NodeTypeManagerInterface
     */
    private $nodeTypeManager;

    /**
     * the predefined primary types that do not depend on optional features.
     */
    public static $primary = [
        'nt:hierarchyNode',
        'nt:file',
        'nt:linkedFile',
        'nt:folder',
        'nt:resource',
        'nt:address'
    ];

    /**
     * the predefined mixin types that do not depend on optional features.
     */
    public static $mixins = [
        'mix:etag',
        'mix:language',
        'mix:lastModified',
        'mix:mimeType',
        'mix:referenceable',
        'mix:shareable',
        'mix:title',
    ];

    public function setUp()
    {
        parent::setUp(false);

        $this->nodeTypeManager = $this->session->getWorkspace()->getNodeTypeManager();
    }

    public function testGetNodeType()
    {
        $type = $this->nodeTypeManager->getNodeType('nt:folder');
        $this->assertInstanceOf(NodeTypeInterface::class, $type);
        // if this makes sense is tested in NodeTypeTest
    }

    public function testGetMixinNodeType()
    {
        $type = $this->nodeTypeManager->getNodeType('mix:language');
        $this->assertInstanceOf(NodeTypeInterface::class, $type);
        // if this makes sense is tested in NodeTypeTest
    }

    public function testGetNodeTypeNoSuch()
    {
        $this->expectException(NoSuchNodeTypeException::class);

        $this->nodeTypeManager->getNodeType('no-such-type');
    }

    /**
     * check if node types exist without fetching them.
     */
    public function testHasNodeType()
    {
        $this->assertTrue($this->nodeTypeManager->hasNodeType('nt:file'));
        $this->assertFalse($this->nodeTypeManager->hasNodeType('no-such-type'));
    }

    public function testGetAllNodeTypes()
    {
        $types = $this->nodeTypeManager->getAllNodeTypes();
        $this->assertInstanceOf(SeekableIterator::class, $types);
        $names = array();

        foreach ($types as $name => $type) {
            $this->assertInstanceOf(NodeTypeInterface::class, $type);
            $this->assertEquals($name, $type->getName());
            $names[$name] = true;
        }

        foreach (self::$primary as $key) {
            $this->assertArrayHasKey($key, $names);
        }

        foreach (self::$mixins as $key) {
            $this->assertArrayHasKey($key, $names);
        }
    }

    public function testGetPrimaryNodeTypes()
    {
        $types = $this->nodeTypeManager->getPrimaryNodeTypes();
        $this->assertInstanceOf(SeekableIterator::class, $types);
        $names = array();
        foreach ($types as $name => $type) {
            $this->assertInstanceOf(NodeTypeInterface::class, $type);
            $this->assertEquals($name, $type->getName());
            $names[$name] = true;
        }

        foreach (self::$primary as $key) {
            $this->assertArrayHasKey($key, $names);
        }

        foreach (self::$mixins as $key) {
            $this->assertArrayNotHasKey($key, $names);
        }
    }

    public function testGetMixinNodeTypes()
    {
        $types = $this->nodeTypeManager->getMixinNodeTypes();
        $this->assertInstanceOf(SeekableIterator::class, $types);
        $names = array();
        foreach ($types as $name => $type) {
            $this->assertInstanceOf(NodeTypeInterface::class, $type);
            $this->assertEquals($name, $type->getName());
            $names[$name] = true;
        }
        foreach (self::$primary as $key) {
            $this->assertArrayNotHasKey($key, $names);
        }
        foreach (self::$mixins as $key) {
            $this->assertArrayHasKey($key, $names);
        }
    }
}
