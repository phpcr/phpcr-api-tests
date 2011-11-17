<?php
namespace PHPCR\Tests\NodeTypeDiscovery;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Test the NoteType §8
 *
 * Requires that NodeTypeManager->getNodeType works correctly
 */
class NodeTypeTest extends \PHPCR\Test\BaseCase
{
    private static $base;
    private static $hierarchyNode;
    private static $file;
    private static $resource;
    private static $versionable;
    private static $created;

    static public function setupBeforeClass($fixtures = false)
    {
        parent::setupBeforeClass($fixtures);
        $ntm = self::$staticSharedFixture['session']->getWorkspace()->getNodeTypeManager();
        self::$base = $ntm->getNodeType('nt:base');
        self::$hierarchyNode = $ntm->getNodeType('nt:hierarchyNode');
        self::$file = $ntm->getNodeType('nt:file');
        self::$resource = $ntm->getNodeType('nt:resource');
        self::$created = $ntm->getNodeType('mix:created');
        self::$versionable = $ntm->getNodeType('mix:versionable');
    }

    public function testGetSupertypes()
    {
        $types = self::$file->getSupertypes();
        $this->assertInternalType('array', $types);
        $typenames = array();
        foreach ($types as $type) {
            $this->assertInstanceOf('\PHPCR\NodeType\NodeTypeInterface', $type);
            $typenames[] = $type->getName();
        }
        $this->assertEquals(array('nt:hierarchyNode', 'mix:created', 'nt:base'), $typenames);
    }
    public function testGetSupertypesNone()
    {
        $types = self::$base->getSupertypes();
        $this->assertInternalType('array', $types);
        $this->assertEquals(0, count($types));
    }

    public function testGetDeclaredSupertypes()
    {
        $types = self::$file->getDeclaredSupertypes();
        $this->assertInternalType('array', $types);
        $typenames = array();
        foreach ($types as $type) {
            $this->assertInstanceOf('\PHPCR\NodeType\NodeTypeInterface', $type);
            $typenames[] = $type->getName();
        }
        $this->assertContains('nt:hierarchyNode', $typenames);
        $this->assertNotContains('nt:base', $typenames);

        $types = self::$resource->getDeclaredSupertypes();
        $this->assertInternalType('array', $types);
        $typenames = array();
        foreach ($types as $type) {
            $this->assertInstanceOf('\PHPCR\NodeType\NodeTypeInterface', $type);
            $typenames[] = $type->getName();
        }
        $this->assertContains('mix:lastModified', $typenames);
        $this->assertContains('mix:mimeType', $typenames);
        $this->assertContains('nt:base', $typenames);
    }
    public function testGetDeclaredSupertypesNone()
    {
        $types = self::$base->getDeclaredSupertypes();
        $this->assertInternalType('array', $types);
        $this->assertEquals(0, count($types));
    }

    public function testGetSubtypes()
    {
        $types = self::$created->getSubtypes();
        $this->assertInstanceOf('SeekableIterator', $types);
        $names = array();
        foreach($types as $name => $type) {
            $this->assertInstanceOf('\PHPCR\NodeType\NodeTypeInterface', $type);
            $this->assertEquals($name, $type->getName());
            $names[$name] = true;
        }
        $this->assertArrayHasKey('nt:hierarchyNode', $names);
        $this->assertArrayHasKey('nt:file', $names);
        $this->assertArrayHasKey('nt:folder', $names);
        $this->assertArrayNotHasKey('nt:base', $names);
        $this->assertArrayNotHasKey('nt:resource', $names);
    }

    public function testGetDeclaredSubtypes()
    {
        $types = self::$created->getDeclaredSubtypes();
        $this->assertInstanceOf('SeekableIterator', $types);
        $names = array();
        foreach($types as $name => $type) {
            $this->assertInstanceOf('\PHPCR\NodeType\NodeTypeInterface', $type);
            $this->assertEquals($name, $type->getName());
            $names[$name] = true;
        }
        $this->assertArrayHasKey('nt:hierarchyNode', $names);
        $this->assertArrayNotHasKey('nt:file', $names);
        $this->assertArrayNotHasKey('nt:folder', $names);
        $this->assertArrayNotHasKey('nt:base', $names);
        $this->assertArrayNotHasKey('nt:resource', $names);
    }

    public function testGetChildNodeDefinitions()
    {
        $children = self::$file->getChildNodeDefinitions();
        $this->assertInternalType('array', $children);
        $this->assertEquals(1, count($children));
        list($key, $child) = each($children);
        $this->assertInstanceOf('\PHPCR\NodeType\NodeDefinitionInterface', $child);
        $this->assertEquals('jcr:content', $child->getName());
        // the rest is tested in NodeDefinitionTest
    }

    public function testGetPropertyDefinitions()
    {
        $properties = self::$file->getPropertyDefinitions();
        $this->assertInternalType('array', $properties);
        $this->assertEquals(4, count($properties));
        $names=array();
        foreach($properties as $prop) {
            $this->assertInstanceOf('\PHPCR\NodeType\PropertyDefinitionInterface', $prop);
            $names[] = $prop->getName();
        }
        arsort($names);
        $this->assertEquals(array('jcr:createdBy', 'jcr:created', 'jcr:mixinTypes', 'jcr:primaryType'), $names);
    }

    public function testIsNodeTypePrimary()
    {
        $this->assertTrue(self::$file->isNodeType('nt:file'));
        $this->assertTrue(self::$file->isNodeType('nt:hierarchyNode'));
        $this->assertTrue(self::$file->isNodeType('nt:base'));
        $this->assertFalse(self::$file->isNodeType('nt:resource'));
    }

    public function testIsNodeTypeMixin()
    {
        $this->assertTrue(self::$versionable->isNodeType('mix:versionable'));
        $this->assertTrue(self::$versionable->isNodeType('mix:referenceable'));
        $this->assertFalse(self::$versionable->isNodeType('mix:lockable'));
    }
}
