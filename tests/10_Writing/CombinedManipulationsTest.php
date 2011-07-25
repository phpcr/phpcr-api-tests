<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

use PHPCR\PropertyType as Type;

/**
 * test sequences of adding / moving / removing stuff inside a transaction
 */
class Writing_10_CombinedManipulationsTest extends phpcr_suite_baseCase
{
    /**
     * remove a node and then add a new one at the same path
     *
     * the old should disappear and a new one appear in place
     */
    public function testRemoveAndAdd()
    {
        $session = $this->sharedFixture['session'];
        $basenode = $session->getNode('/tests_general_base');
        $this->assertInstanceOf('PHPCR\NodeInterface', $basenode);
        $node = $basenode->getNode('numberPropertyNode');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertInstanceOf('PHPCR\NodeType\NodeTypeInterface', $node->getPrimaryNodeType());
        $this->assertSame('nt:file', $node->getPrimaryNodeType()->getName());

        $node->remove();

        $newnode = $basenode->addNode('numberPropertyNode', 'nt:folder');

        $session = $this->saveAndRenewSession();

        $node = $session->getNode('/tests_general_base/numberPropertyNode');
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
        $this->assertSame('nt:folder', $node->getPrimaryNodeType()->getName());
        $this->assertFalse($node->hasNodes());
    }

    /*
     * add more:
     * move a not yet loaded node, then load it with the old path -> fail. with new path -> get it
     * same with moving child nodes not yet loaded and calling Node::getChildren. and loaded as well.
     * Test if order of write operations to backend is correct in larger batches. if i have
     * /some/path/parent/node and set the path of "parent" to /some/other/path/parent and in the same session change the path of node to /some/path/parent/something/node, result will depend on the order.
     * if you first move parent, then node, node ends up at the expected path.
     * if you first move node, then parent, node will end up in /some/other/path/parent/something/node, because a node is moved with all its children.
     */
}
