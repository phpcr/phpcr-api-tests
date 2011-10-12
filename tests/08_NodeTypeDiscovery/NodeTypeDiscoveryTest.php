<?php
namespace PHPCR\Tests\NodeTypeDiscovery;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

/**
 * Test the NoteTypeManager §8
 */
class NodeTypeDiscoveryTest extends \PHPCR\Test\BaseCase
{
    private $nodeTypeManager;

    /**
     * the predefined primary types that do not depend on optional features
     */
    public static $primary = array('nt:hierarchyNode', 'nt:file',
        'nt:linkedFile', 'nt:folder', 'nt:resource', 'nt:address');

    /**
     * the predefined mixin types that do not depend on optional features
     */
    public static $mixins = array(
            "mix:etag", "mix:language", "mix:lastModified", "mix:mimeType",
            "mix:referenceable", "mix:shareable", "mix:title"
            );

    public function setUp()
    {
        parent::setUp(false);
        $this->nodeTypeManager = $this->sharedFixture['session']->getWorkspace()->getNodeTypeManager();
    }

    public function testGetNodeType()
    {
        $type = $this->nodeTypeManager->getNodeType('nt:folder');
        $this->assertInstanceOf('\PHPCR\NodeType\NodeTypeInterface', $type);
        // if this makes sense is tested in NodeTypeTest
    }

    public function testGetMixinNodeType()
    {
        $type = $this->nodeTypeManager->getNodeType('mix:language');
        $this->assertInstanceOf('\PHPCR\NodeType\NodeTypeInterface', $type);
        // if this makes sense is tested in NodeTypeTest
    }

    /**
     * @expectedException \PHPCR\NodeType\NoSuchNodeTypeException
     */
    public function testGetNodeTypeNoSuch()
    {
        $this->nodeTypeManager->getNodeType('no-such-type');
    }

    /**
     * check if node types exist without fetching them
     */
    public function testHasNodeType()
    {
        $this->assertTrue($this->nodeTypeManager->hasNodeType('nt:file'));
        $this->assertFalse($this->nodeTypeManager->hasNodeType('no-such-type'));
    }

    public function testGetAllNodeTypes()
    {
        $types = $this->nodeTypeManager->getAllNodeTypes();
        $this->assertInstanceOf('SeekableIterator', $types);
        $names = array();
        foreach($types as $name => $type) {
            $this->assertInstanceOf('\PHPCR\NodeType\NodeTypeInterface', $type);
            $this->assertEquals($name, $type->getName());
            $names[$name] = true;
        }
        foreach(self::$primary as $key) {
            $this->assertArrayHasKey($key, $names);
        }
        foreach(self::$mixins as $key) {
            $this->assertArrayHasKey($key, $names);
        }
    }

    public function testGetPrimaryNodeTypes()
    {
        $types = $this->nodeTypeManager->getPrimaryNodeTypes();
        $this->assertInstanceOf('SeekableIterator', $types);
        $names = array();
        foreach($types as $name => $type) {
            $this->assertInstanceOf('\PHPCR\NodeType\NodeTypeInterface', $type);
            $this->assertEquals($name, $type->getName());
            $names[$name] = true;
        }
        foreach(self::$primary as $key) {
            $this->assertArrayHasKey($key, $names);
        }
        foreach(self::$mixins as $key) {
            $this->assertArrayNotHasKey($key, $names);
        }
    }

    public function testGetMixinNodeTypes()
    {
        $types = $this->nodeTypeManager->getMixinNodeTypes();
        $this->assertInstanceOf('SeekableIterator', $types);
        $names = array();
        foreach($types as $name => $type) {
            $this->assertInstanceOf('\PHPCR\NodeType\NodeTypeInterface', $type);
            $this->assertEquals($name, $type->getName());
            $names[$name] = true;
        }
        foreach(self::$primary as $key) {
            $this->assertArrayNotHasKey($key, $names);
        }
        foreach(self::$mixins as $key) {
            $this->assertArrayHasKey($key, $names);
        }
    }
}
