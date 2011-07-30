<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

use PHPCR\PropertyType;

/**
 * Testing whether node property dynamic re-binding (i.e. setting a new type and
 * value for a property) works correctly
 *
 * Covering jcr-2.8.3 spec $10.4.2
 */
class Writing_10_SetPropertyDynamicRebindingTest extends phpcr_suite_baseCase
{
    protected $referenceable_node_uuid = '842e61c0-09ab-42a9-87c0-308ccc90e6f4';

    protected static $created_nodes = array();

    static public function setupBeforeClass()
    {
        parent::setupBeforeClass('10_Writing/nodetype');
    }

    public function setUp() {
        parent::setUp();
        $this->renewSession();

        // TODO: re-enable this test once Session.logout is working properly
        // (for the moment this test will take too long to run because we reach
        // the max number of sessions limit)
        //$this->markTestSkipped('Fix Session.logout before enabling this test');
    }

    public static function tearDownAfterClass()
    {
        // Remove the created nodes to avoid keeping referenced nodes
        foreach(self::$created_nodes as $node) {
            self::$staticSharedFixture['session']->removeItem($node);
        }

        self::$staticSharedFixture['session']->save();

        parent::tearDownAfterClass();
    }

    /**
     * @dataProvider dynamicRebindingProvider
     *
     * @param string $propName The name of the new property to create
     * @param int $sourcePropType The initial type of the property
     * @param mixed $sourcePropValue The initial value of the property
     * @param int $destPropType The new type of the property
     * @param mixed $destPropValue The new value of the property
     * @param string $getterFunc The getter function to use to read the new value
     */
    public function testDynamicRebinding($propName, $sourcePropType, $sourcePropValue, $destPropType, $destPropValue, $getterFunc)
    {
        $node = $this->sharedFixture['session']->getRootNode();

        // Create the property with the source type and value and save it
        $prop = $node->setProperty($propName, $sourcePropValue, $sourcePropType);
        $this->assertInstanceOf('\PHPCR\PropertyInterface', $prop);
        $this->assertEquals($sourcePropType, $prop->getType(), 'Initial property type does not match before saving');

        self::$created_nodes[] = $prop->getPath();

        if ($sourcePropType === PropertyType::REFERENCE
         || $sourcePropType === PropertyType::WEAKREFERENCE) {
            $this->assertEquals($this->referenceable_node_uuid, $prop->getString());
        } elseif ($sourcePropType === PropertyType::DATE) {
            // To avoid problems with the representation of the TZ, we compare timestamps
            $this->assertEquals($sourcePropValue->getTimestamp(), $prop->getValue()->getTimestamp());
        } elseif ($sourcePropType !== \PHPCR\PropertyType::BINARY) {
            $this->assertEquals($sourcePropValue, $prop->getValue(), 'Initial property value does not match before saving');
        } else {
            // PHPUnit does not like to assertEquals on resources
            $this->assertTrue(is_resource($prop->getValue()));
        }

        // Read it from backend check it's still valid
        $this->saveAndRenewSession();
        $prop = $this->sharedFixture['session']->getProperty('/' . $propName);
        $this->assertInstanceOf('\PHPCR\PropertyInterface', $prop);
        $this->assertEquals($sourcePropType, $prop->getType(), 'Initial property type does not match after saving');

        if ($sourcePropType === PropertyType::REFERENCE
         || $sourcePropType === PropertyType::WEAKREFERENCE) {
            $this->assertEquals($this->referenceable_node_uuid, $prop->getString());
        } elseif ($sourcePropType === PropertyType::DATE) {
            // To avoid problems with the representation of the TZ, we compare timestamps
            $this->assertEquals($sourcePropValue->getTimestamp(), $prop->getValue()->getTimestamp());
        } elseif ($sourcePropType !== \PHPCR\PropertyType::BINARY) {
            $this->assertEquals($sourcePropValue, $prop->getValue(), 'Initial property value does not match after saving');
        } else {
            // PHPUnit does not like to assertEquals on resources
            $this->assertTrue(is_resource($prop->getValue()));
        }

        // Re-bind the property to the new type/value and save it
        $prop->setValue($destPropValue, $destPropType);
        $this->assertEquals($destPropType, $prop->getType(), 'Property type does not match after re-binding');
        $this->assertEquals($destPropValue, $prop->$getterFunc(), 'Property value does not match after re-binding');

        // Finally re-read it from backend and check it's still ok
        $this->saveAndRenewSession();
        $prop = $this->sharedFixture['session']->getProperty('/' . $propName);
        $this->assertInstanceOf('\PHPCR\PropertyInterface', $prop);
        $this->assertEquals($destPropType, $prop->getType(), 'Property type does not match after re-binding and save');

        if ($destPropType === PropertyType::DATE) {
            // To avoid problems with the representation of the TZ, we compare timestamps
            $this->assertEquals($destPropValue->getTimestamp(), $prop->getValue()->getTimestamp());
        } else {
            $this->assertEquals($destPropValue, $prop->$getterFunc(), 'Property value does not match after re-binding and save');
        }
    }

    /**
     * Construct the test data for the testDynamicRebinding test.
     * The resulting array is composed of arrays as follow:
     *
     *      array(new_property_name,
     *            initial_property_type, initial_property_value,
     *            new_property_type, new_property_value,
     *            name_of_the_getter_to_read_the_new_value)
     *
     * @return array
     */
    public function dynamicRebindingProvider()
    {
        $typesAndValues = array(
            PropertyType::STRING        => 'abcdefg',
            PropertyType::URI           => 'https://github.com/jackalope/jackalope/wiki',
            PropertyType::BOOLEAN       => true,
            PropertyType::LONG          => 3,
            PropertyType::DOUBLE        => 3.1415926535897,
            PropertyType::DECIMAL       => '3.14',
            PropertyType::BINARY        => 'some binary stuff',
            PropertyType::DATE          => new DateTime(),
            PropertyType::NAME          => 'jcr:some_name',
            PropertyType::PATH          => '/some/valid/path',
            PropertyType::WEAKREFERENCE => $this->referenceable_node_uuid,
            PropertyType::REFERENCE     => $this->referenceable_node_uuid,
        );

        $getters = array(
            PropertyType::STRING        => 'getString',
            PropertyType::URI           => 'getString',
            PropertyType::BOOLEAN       => 'getBoolean',
            PropertyType::LONG          => 'getLong',
            PropertyType::DOUBLE        => 'getDouble',
            PropertyType::DECIMAL       => 'getDecimal',
            PropertyType::BINARY        => 'getString',
            PropertyType::DATE          => 'getDate',
            PropertyType::NAME          => 'getString',
            PropertyType::PATH          => 'getString',
            PropertyType::WEAKREFERENCE => 'getString',
            PropertyType::REFERENCE     => 'getString',
        );

        $provider = array();
        foreach($typesAndValues as $sourceKey => $sourceVal) {
            foreach($typesAndValues as $destKey => $destVal) {
                if ($sourceKey !== $destKey) {
                    $propName =
                        'dynRebinding_' . PropertyType::nameFromValue($sourceKey) .
                        '_To_' . PropertyType::nameFromValue($destKey);
                    $provider[] = array($propName, $sourceKey, $sourceVal, $destKey, $destVal, $getters[$destKey]);
                }
            }
        }

        return $provider;
    }
}
