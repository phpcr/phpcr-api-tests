<?php
namespace PHPCR\Tests\Writing;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Testing that mix:referenceable nodes references work correctly
 *
 * Covering jcr-2.8.3 spec $10.10.3
 */
class MixinReferenceableTest extends \PHPCR\Test\BaseCase
{
    public function setUp()
    {
        $this->renewSession(); // discard changes
    }

    /**
     * Test that a node without mix:referenceable type cannot be referenced
     * @expectedException PHPCR\ValueFormatException
     */
    public function testReferenceOnNonReferenceableNode()
    {
        // Load a non-referenceable node
        $referenced_node = $this->sharedFixture['session']->getNode('/tests_general_base/emptyExample');

        // Try to reference it
        $source_node = $this->sharedFixture['session']->getNode('/tests_general_base/index.txt/jcr:content');
        $source_node->setProperty('reference', $referenced_node, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->sharedFixture['session']->save();
    }

    /**
     * Test that a node with newly set mix:referenceable type can be referenced
     * @group test
     */
    public function testReferenceOnNewlyReferenceableNode()
    {
        // Load a non-referenceable node and make it referenceable
        $referenced_node = $this->sharedFixture['session']->getNode('/tests_general_base/emptyExample');
        $referenced_node->addMixin('mix:referenceable');
        $this->saveAndRenewSession();

        // Re-read the node to be sure it has a UUID
        $referenced_node = $this->sharedFixture['session']->getNode('/tests_general_base/emptyExample');

        // Reference it from another node
        $source_node = $this->sharedFixture['session']->getNode('/tests_general_base/index.txt/jcr:content');
        $source_node->setProperty('reference', $referenced_node, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->sharedFixture['session']->save();

        $this->assertInstanceOf('PHPCR\NodeInterface', $source_node->getPropertyValue('reference'));
    }

    /**
     * Test that a node with mix:referenceable in the fixtures can be referenced
     */
    public function testReferenceOnReferenceableNode()
    {
        // Load a referenceable node
        $referenced_node = $this->sharedFixture['session']->getNode('/tests_general_base/idExample');

        // Reference it from another node
        $source_node = $this->sharedFixture['session']->getNode('/tests_general_base/index.txt/jcr:content');
        $source_node->setProperty('reference', $referenced_node, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->sharedFixture['session']->save();

        $this->assertInstanceOf('PHPCR\NodeInterface', $source_node->getPropertyValue('reference'));
    }

}
