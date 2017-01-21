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

use Exception;
use PHPCR\NodeType\NodeDefinitionInterface;
use PHPCR\NodeType\NodeTypeInterface;
use PHPCR\NodeType\NodeTypeManagerInterface;
use PHPCR\Test\BaseCase;
use PHPCR\Version\OnParentVersionAction;

/**
 * Test NodeDefinition behaviour and reading NodeDefinition from NodeTypeDefinition §8.
 *
 * Requires that NodeTypeManager->getNodeType and NodeTypeDefinition->getChildNodeDefinitions() works correctly
 */
class NodeDefinitionTest extends BaseCase
{
    private static $base;

    /**
     * @var NodeTypeInterface
     */
    private static $file;

    /**
     * @var NodeTypeInterface
     */
    private static $folder;

    /**
     * @var NodeTypeInterface
     */
    private static $hierarchyNodeType;

    /**
     * Node definition of the jcr:content in an nt:file type.
     *
     * @var NodeDefinitionInterface
     */
    private $content;

    /**
     * Node definition of a hierarchy node.
     *
     * @var NodeDefinitionInterface
     */
    private $hierarchyNodeDef;

    public static function setupBeforeClass($fixtures = false)
    {
        parent::setupBeforeClass($fixtures);
        /** @var NodeTypeManagerInterface $ntm */
        $ntm = self::$staticSharedFixture['session']->getWorkspace()->getNodeTypeManager();
        self::$file = $ntm->getNodeType('nt:file');
        self::$folder = $ntm->getNodeType('nt:folder');
        self::$hierarchyNodeType = $ntm->getNodeType('nt:hierarchyNode');
    }

    public function setUp()
    {
        parent::setUp();

        try {
            $defs = self::$file->getChildNodeDefinitions();
            $this->assertInternalType('array', $defs);
            $this->assertCount(1, $defs);
            list($key, $this->content) = each($defs);
            $this->assertInstanceOf(NodeDefinitionInterface::class, $this->content);
            $this->assertEquals('jcr:content', $this->content->getName());

            $defs = self::$folder->getChildNodeDefinitions();
            $this->assertInternalType('array', $defs);
            $this->assertCount(1, $defs);
            list($key, $this->hierarchyNodeDef) = each($defs);
            $this->assertInstanceOf(NodeDefinitionInterface::class, $this->hierarchyNodeDef);
            $this->assertEquals('*', $this->hierarchyNodeDef->getName());
        } catch (Exception $e) {
            $this->markTestSkipped('getChildNodeDefinitions not working as it should, skipping tests about NodeDefinitionInterface: '.$e->getMessage());
        }
    }

    public function testAllowsSameNameSiblings()
    {
        $this->assertFalse($this->content->allowsSameNameSiblings());
    }

    public function testDefaultPrimaryType()
    {
        $this->assertNull($this->content->getDefaultPrimaryType());
    }

    public function testDefaultPrimaryTypeName()
    {
        $this->assertNull($this->content->getDefaultPrimaryTypeName());
    }

    public function getRequiredPrimaryTypeNames()
    {
        $names = $this->content->getRequiredPrimaryTypeNames();
        $this->assertInternalType('array', $names);
        $this->assertTrue(1, count($names));
        $this->assertEquals('nt:base', $names[0]);

        $names = $this->hierarchyNodeDef->getRequiredPrimaryTypeNames();
        $this->assertInternalType('array', $names);
        $this->assertTrue(1, count($names));
        $this->assertEquals('nt:hierarchyNode', $names[0]);
    }
    public function getRequiredPrimaryTypes()
    {
        $types = $this->content->getRequiredPrimaryTypeNames();
        $this->assertInternalType('array', $types);
        $this->assertTrue(1, count($types));
        $this->assertEquals(self::$base, $types[0]);

        $types = $this->hierarchyNodeDef->getRequiredPrimaryTypeNames();
        $this->assertInternalType('array', $types);
        $this->assertTrue(1, count($types));
        $this->assertEquals(self::$hierarchyNodeType, $types[0]);
    }

    /// item methods ///
    public function testGetDeclaringNodeType()
    {
        $nt = $this->content->getDeclaringNodeType();
        $this->assertSame(self::$file, $nt);

        $nt = $this->hierarchyNodeDef->getDeclaringNodeType();
        $this->assertSame(self::$folder, $nt);
    }

    public function testName()
    {
        $this->assertEquals('jcr:content', $this->content->getName());
        $this->assertEquals('*', $this->hierarchyNodeDef->getName());
    }

    public function testGetOnParentVersion()
    {
        $this->assertEquals(OnParentVersionAction::COPY, $this->content->getOnParentVersion());
        $this->assertEquals(OnParentVersionAction::VERSION, $this->hierarchyNodeDef->getOnParentVersion());
    }

    public function testIsAutoCreated()
    {
        $this->assertFalse($this->content->isAutoCreated());
        $this->assertFalse($this->hierarchyNodeDef->isAutoCreated());
    }

    public function testIsMandatory()
    {
        $this->assertTrue($this->content->isMandatory());
        $this->assertFalse($this->hierarchyNodeDef->isMandatory());
    }

    public function testIsProtected()
    {
        $this->assertFalse($this->content->isProtected());
        $this->assertFalse($this->hierarchyNodeDef->isProtected());
    }
}
