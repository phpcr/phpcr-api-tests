<?php
namespace PHPCR\Tests\NodeTypeDiscovery;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

use PHPCR\Query\QOM\QueryObjectModelConstantsInterface;
/**
 * Test the PropertyDefinition §8
 *
 * Requires that NodeTypeManager->getNodeType and NodeTypeDefinition->getPropertyDefinitions() works correctly
 */
class PropertyDefinitionTest extends \PHPCR\Test\BaseCase
{
    private static $base;
    private static $address;
    private static $mix_created;
    private static $resource;

    /** nt:base property */
    private $primaryType; // (NAME) mandatory autocreated protected COMPUTE
    private $mixinTypes; // (NAME) protected multiple COMPUTE
    /** properties of nt:address */
    private $workspace; // (STRING)
    private $pathprop; // (PATH)
    private $id; // (WEAKREFERENCE)
    /** property of mix:created */
    private $created; // (DATE) autocreated protected
    /** property of nt:resource */
    private $data; // (BINARY) mandatory

    static public function setupBeforeClass($fixtures = false)
    {
        parent::setupBeforeClass($fixtures);
        $ntm = self::$staticSharedFixture['session']->getWorkspace()->getNodeTypeManager();
        self::$base = $ntm->getNodeType('nt:base');
        self::$address = $ntm->getNodeType('nt:address');
        self::$mix_created = $ntm->getNodeType('mix:created');
        self::$resource = $ntm->getNodeType('nt:resource');

    }

    public function setUp()
    {
        try {
            $defs = self::$base->getPropertyDefinitions();
            $this->assertInternalType('array', $defs);
            foreach($defs as $def) {
                $this->assertInstanceOf('\PHPCR\NodeType\PropertyDefinitionInterface', $def);
                switch($def->getName()) {
                    case 'jcr:primaryType':
                        $this->primaryType = $def;
                        break;
                    case 'jcr:mixinTypes':
                        $this->mixinTypes = $def;
                        break;
                }
            }
            $this->assertNotNull($this->primaryType);
            $this->assertNotNull($this->mixinTypes);

            $defs = self::$address->getPropertyDefinitions();
            $this->assertInternalType('array', $defs);
            foreach($defs as $def) {
                $this->assertInstanceOf('\PHPCR\NodeType\PropertyDefinitionInterface', $def);
                switch($def->getName()) {
                    case 'jcr:workspace':
                        $this->workspace = $def;
                        break;
                    case 'jcr:path':
                        $this->pathprop = $def;
                        break;
                    case 'jcr:id':
                        $this->id = $def;
                        break;
                }
            }
            $this->assertNotNull($this->workspace);
            $this->assertNotNull($this->pathprop);
            $this->assertNotNull($this->id);

            $defs = self::$mix_created->getPropertyDefinitions();
            $this->assertInternalType('array', $defs);
            foreach($defs as $def) {
                $this->assertInstanceOf('\PHPCR\NodeType\PropertyDefinitionInterface', $def);
                if ('jcr:created' == $def->getName()) {
                    $this->created = $def;
                }
            }
            $this->assertNotNull($this->created);

            $defs = self::$resource->getPropertyDefinitions();
            $this->assertInternalType('array', $defs);
            foreach($defs as $def) {
                $this->assertInstanceOf('\PHPCR\NodeType\PropertyDefinitionInterface', $def);
                if ('jcr:data' == $def->getName()) {
                    $this->data = $def;
                }
            }
            $this->assertNotNull($this->data);
        } catch (\Exception $e) {
            $this->markTestSkipped('getChildNodeDefinitions not working as it should, skipping tests about NodeDefinitionInterface: '.$e->getMessage());
        }
    }

    public function testGetAvailableQueryOperators()
    {
        $ops = $this->primaryType->getAvailableQueryOperators();
        $expected = array(QueryObjectModelConstantsInterface::JCR_OPERATOR_EQUAL_TO,
                          QueryObjectModelConstantsInterface::JCR_OPERATOR_NOT_EQUAL_TO,
                          QueryObjectModelConstantsInterface::JCR_OPERATOR_LESS_THAN,
                          QueryObjectModelConstantsInterface::JCR_OPERATOR_LESS_THAN_OR_EQUAL_TO,
                          QueryObjectModelConstantsInterface::JCR_OPERATOR_GREATER_THAN,
                          QueryObjectModelConstantsInterface::JCR_OPERATOR_GREATER_THAN_OR_EQUAL_TO,
                          QueryObjectModelConstantsInterface::JCR_OPERATOR_LIKE
                          );

        asort($ops);
        asort($expected);

        $this->assertEquals(array_values($expected), array_values($ops)); // array_values to get rid of indexes

        // no built-in type without all query operators
    }

    public function testGetDefaultValues()
    {
        $def = $this->primaryType->getDefaultValues();
        $this->assertInternalType('array', $def);
        $this->assertEquals(0, count($def));
        // no built-in types with default value
    }

    public function testGetRequiredType()
    {
        $tid = $this->primaryType->getRequiredType();
        $this->assertEquals(\PHPCR\PropertyType::NAME, $tid);
        $tid = $this->workspace->getRequiredType();
        $this->assertEquals(\PHPCR\PropertyType::STRING, $tid);
        $tid = $this->pathprop->getRequiredType();
        $this->assertEquals(\PHPCR\PropertyType::PATH, $tid);
        $tid = $this->id->getRequiredType();
        $this->assertEquals(\PHPCR\PropertyType::WEAKREFERENCE, $tid);
        $tid = $this->created->getRequiredType();
        $this->assertEquals(\PHPCR\PropertyType::DATE, $tid);
        $tid = $this->data->getRequiredType();
        $this->assertEquals(\PHPCR\PropertyType::BINARY, $tid);
    }

    public function testGetValueConstraints()
    {
        $constraint = $this->primaryType->getValueConstraints();
        $this->assertInternalType('array', $constraint);
        $this->assertEquals(0, count($constraint));
        // no built-in type with constraints
    }

    public function testIsFullTextSearchable()
    {
        $b = $this->primaryType->isFullTextSearchable();
        $this->assertInternalType('boolean', $b);
        $this->assertTrue($b);
    }
    public function testIsMultiple()
    {
        $b = $this->primaryType->isMultiple();
        $this->assertInternalType('boolean', $b);
        $this->assertFalse($b);

        $b = $this->mixinTypes->isMultiple();
        $this->assertInternalType('boolean', $b);
        $this->assertTrue($b);
    }

    public function testIsQueryOrderable()
    {
        $b = $this->primaryType->isQueryOrderable();
        $this->assertInternalType('boolean', $b);
        $this->assertTrue($b);
    }

     /// item methods ///
    public function testGetDeclaringNodeType()
    {
        $nt = $this->primaryType->getDeclaringNodeType();
        $this->assertSame(self::$base, $nt);

        $nt = $this->created->getDeclaringNodeType();
        $this->assertSame(self::$mix_created, $nt);
    }
    public function testName()
    {
        $this->assertEquals('jcr:primaryType', $this->primaryType->getName());
    }
    public function testGetOnParentVersion()
    {
        $this->assertEquals(\PHPCR\Version\OnParentVersionAction::COMPUTE, $this->primaryType->getOnParentVersion());
        $this->assertEquals(\PHPCR\Version\OnParentVersionAction::COPY, $this->created->getOnParentVersion());
    }
    public function testIsAutoCreated()
    {
        $this->assertTrue($this->primaryType->isAutoCreated());
        $this->assertFalse($this->pathprop->isAutoCreated());
        $this->assertTrue($this->created->isAutoCreated());
        $this->assertFalse($this->data->isAutoCreated());
    }
    public function testIsMandatory()
    {
        $this->assertTrue($this->primaryType->isMandatory());
        $this->assertFalse($this->pathprop->isMandatory());
        $this->assertFalse($this->created->isMandatory());
        $this->assertTrue($this->data->isMandatory());
    }
    public function testIsProtected()
    {
        $this->assertTrue($this->primaryType->isProtected());
        $this->assertFalse($this->pathprop->isProtected());
        $this->assertTrue($this->created->isProtected());
        $this->assertFalse($this->data->isProtected());
    }
}