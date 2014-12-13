<?php
namespace PHPCR\Tests\Writing;

use PHPCR\PropertyType;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Testing that mix:referenceable nodes references work correctly
 *
 * Covering jcr-2.8.3 spec $10.10.3
 */
class MixinReferenceableTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = '10_Writing/mixinreferenceable')
    {
        parent::setupBeforeClass($fixtures);
    }

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
        $nonReferenceableNode = $this->node->getNode('non-referenceable');

        // Try to reference it
        $sourceNode = $this->node->getNode('node');
        $sourceNode->setProperty('reference', $nonReferenceableNode, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->session->save();
    }

    /**
     * Test that a node with newly set mix:referenceable type can be referenced
     */
    public function testReferenceOnNewlyReferenceableNode()
    {
        // Load a non-referenceable node and make it referenceable
        $referencedNode = $this->node->getNode('node');
        $referencedNode->addMixin('mix:referenceable');

        // Re-read the node to be sure it has a UUID
        $this->saveAndRenewSession();
        $referencedNode = $this->node->getNode('node');

        // Reference it from another node
        $sourceNode = $this->node->getNode('other-node');
        $sourceNode->setProperty('reference', $referencedNode, \PHPCR\PropertyType::WEAKREFERENCE);

        $this->session->save();

        $this->assertInstanceOf('PHPCR\NodeInterface', $sourceNode->getPropertyValue('reference'));

        // referrers only required to work once save happened
        $this->assertCount(0, $referencedNode->getReferences());
        $this->assertCount(1, $referencedNode->getWeakReferences());
    }

    /**
     * Test that a node with mix:referenceable in the fixtures can be referenced
     */
    public function testReferenceOnReferenceableNode()
    {
        // Load a referenceable node
        $referencedNode = $this->node->getNode('referenceable');

        // Reference it from another node
        $sourceNode = $this->node->getNode('node');
        $sourceNode->setProperty('oreference', $referencedNode, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->session->save();

        $this->assertInstanceOf('PHPCR\NodeInterface', $sourceNode->getPropertyValue('oreference'));
    }

    /**
     * Test that we can update a reference
     */
    public function testUpdateReference()
    {
        $referenced1 = $this->node->getNode('node');
        $referenced1->addMixin('mix:referenceable');
        $this->session->save();

        // Load a referenceable node
        $referenced2 = $this->node->getNode('referenceable');

        // Reference it from another node
        $sourceNode = $this->node->getNode('other-node');

        $sourceNode->setProperty('reference', $referenced1, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->session->save();
        $sourceNode->setProperty('reference', $referenced2, \PHPCR\PropertyType::WEAKREFERENCE);
        $this->session->save();
        $this->assertSame($referenced2, $sourceNode->getPropertyValue('reference'));

        $this->renewSession();
        $referenced2 = $this->node->getNode('referenceable');
        $this->assertSame($referenced2, $this->node->getNode('other-node')->getProperty('reference')->getValue());
    }

    public function testMultiValueReference()
    {
        $this->doTestMultiValueReference(
            array('one', 'two', 'three'),
            array('one', 'two', 'one', 'one', 'two', 'three'),
            PropertyType::REFERENCE
        );
    }

    public function testMultiValueWeakReference()
    {
        $this->doTestMultiValueReference(
            array('one', 'two', 'three'),
            array('one', 'two', 'one', 'one', 'two', 'three'),
            PropertyType::WEAKREFERENCE
        );
    }

    private function doTestMultiValueReference($nodeNames, $nodeCollectionNames, $referenceType)
    {
        $baseNode = $this->node;
        $nodes = array();
        foreach ($nodeNames as $nodeName) {
            $node = $baseNode->addNode($nodeName);
            $node->addMixin('mix:referenceable');
            $nodes[$nodeName] = $node;
        }

        $this->session->save();

        $referrer = $baseNode->addNode('referrer');

        $nodeCollection = array();

        foreach ($nodeCollectionNames as $nodeCollectionName) {
            $nodeCollection[] = $nodes[$nodeCollectionName];
        }
        $referrer->setProperty('references', $nodeCollection, $referenceType);

        $this->session->save();

        $this->renewSession();
        $referrer = $this->node->getNode('referrer');
        $values = $referrer->getProperty('references');

        foreach ($values as $referencedNode) {
            $name = array_shift($nodeCollectionNames);
            $this->assertSame($name, $referencedNode->getName());
        }
    }
}
