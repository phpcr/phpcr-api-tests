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
     * @expectedException \PHPCR\ValueFormatException
     */
    public function testReferenceOnNonReferenceableNode()
    {
        // Load a non-referenceable node
        $referenced_node = $this->session->getNode('/tests_general_base/emptyExample');

        // Try to reference it
        $source_node = $this->session->getNode('/tests_general_base/index.txt/jcr:content');
        $source_node->setProperty('reference', $referenced_node, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->session->save();
    }

    /**
     * Test that a node with newly set mix:referenceable type can be referenced
     */
    public function testReferenceOnNewlyReferenceableNode()
    {
        // Load a non-referenceable node and make it referenceable
        $referenced_node = $this->session->getNode('/tests_general_base/emptyExample');
        $referenced_node->addMixin('mix:referenceable');

        // Re-read the node to be sure it has a UUID
        $this->saveAndRenewSession();
        $referenced_node = $this->session->getNode('/tests_general_base/emptyExample');

        // Reference it from another node
        $source_node = $this->session->getNode('/tests_general_base/index.txt/jcr:content');
        $source_node->setProperty('reference', $referenced_node, \PHPCR\PropertyType::WEAKREFERENCE);

        $this->session->save();

        $this->assertInstanceOf('PHPCR\NodeInterface', $source_node->getPropertyValue('reference'));

        // referrers only required to work once save happened
        $this->assertCount(0, $referenced_node->getReferences());
        $this->assertCount(1, $referenced_node->getWeakReferences());
    }

    /**
     * Test that a node with mix:referenceable in the fixtures can be referenced
     */
    public function testReferenceOnReferenceableNode()
    {
        // Load a referenceable node
        $referenced_node = $this->session->getNode('/tests_general_base/idExample');

        // Reference it from another node
        $source_node = $this->session->getNode('/tests_general_base/index.txt/jcr:content');
        $source_node->setProperty('oreference', $referenced_node, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->session->save();

        $this->assertInstanceOf('PHPCR\NodeInterface', $source_node->getPropertyValue('oreference'));
    }

    /**
     * Test that we can update a reference
     */
    public function testUpdateReference()
    {
        $referenced1 = $this->session->getNode('/tests_general_base/emptyExample');
        $referenced1->addMixin('mix:referenceable');
        $this->session->save();

        // Load a referenceable node
        $referenced2 = $this->session->getNode('/tests_general_base/idExample');

        // Reference it from another node
        $source_node = $this->session->getNode('/tests_general_base/index.txt/jcr:content');

        $source_node->setProperty('reference', $referenced1, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->session->save();
        $source_node->setProperty('reference', $referenced2, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->session->save();
        $this->assertSame($referenced2, $source_node->getPropertyValue('reference'));

        $session = $this->renewSession();
        $referenced2 = $session->getNode('/tests_general_base/idExample');
        $this->assertSame($referenced2, $session->getProperty('/tests_general_base/index.txt/jcr:content/reference')->getValue());
    }

    public function testSetUuidNewReferenceable()
    {
        $uuid = 'aaaa61c0-09ab-42a9-87c0-308ccc93aaaa';
        $node = $this->session->getNode('/tests_general_base/index.txt/jcr:content')->addNode('newId', 'nt:unstructured');
        $node->addMixin('mix:referenceable');
        $node->setProperty('jcr:uuid', $uuid);
        $this->session->save();
        $this->assertSame($uuid, $node->getIdentifier());

        $session = $this->renewSession();

        $node = $session->getNode('/tests_general_base/index.txt/jcr:content/newId');
        $this->assertSame($uuid, $node->getIdentifier());
    }

    /**
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testSetUuidNewButNonreferenceable()
    {
        $node = $this->session->getNode('/tests_general_base/index.txt/jcr:content')->addNode('newNonref', 'nt:unstructured');
        $node->setProperty('jcr:uuid', 'bbbb61c0-09ab-42a9-87c0-308ccc93aaaa');
    }

    /**
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testSetUuidReferenceableButExisting()
    {
        $node = $this->session->getNode('/tests_general_base/idExample');
        $node->setProperty('jcr:uuid', 'cccc61c0-09ab-42a9-87c0-308ccc93aaaa');
    }

    /**
     * @expectedException \PHPCR\NodeType\ConstraintViolationException
     */
    public function testSetUuidButNotReferenceableExisting()
    {
        $node = $this->session->getNode('/tests_general_base/index.txt/jcr:content');
        $node->setProperty('jcr:uuid', 'dddd61c0-09ab-42a9-87c0-308ccc93aaaa');
    }
}
