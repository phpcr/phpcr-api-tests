<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Writing;

/**
 * Testing that mix:referenceable nodes references work correctly.
 *
 * Covering jcr-2.8.3 spec $10.10.3
 */
class MixinCreatedTest extends \PHPCR\Test\BaseCase
{
    public function setUp()
    {
        $this->renewSession(); // discard changes
    }

    /**
     * Test that a node with newly set mix:referenceable type can be referenced.
     */
    public function testCreationNode()
    {
        $path = '/tests_general_base/idExample/jcr:content';
        /** @var $node \PHPCR\NodeInterface */
        $node = $this->session->getNode($path);
        $child = $node->addNode('test');
        $path .= '/test';
        $this->assertEquals($path, $child->getPath());
        $child->addMixin('mix:created');

        $this->session->save();

        $this->assertTrue($child->isNodeType('mix:created'));
        $this->assertTrue($child->hasProperty('jcr:created'));
        $date = $child->getPropertyValue('jcr:created');
        $this->assertInstanceOf('DateTime', $date);
        /* @var $date \DateTime */
        $diff = time() - $date->getTimestamp();
        $this->assertTrue($diff < 60 * 10, 'jcr:created should be current date as fixture was just imported: '.$date->format('c'));

        // Re-read the node to be sure things got properly saved
        $this->renewSession();
        $child = $this->session->getNode($path);

        $this->assertTrue($child->isNodeType('mix:created'));
        $this->assertTrue($child->hasProperty('jcr:created'));
        $date = $child->getPropertyValue('jcr:created');
        $this->assertInstanceOf('DateTime', $date);
        $diff = time() - $date->getTimestamp();
        $this->assertTrue($diff < 60 * 10, 'jcr:created should be current date as fixture was just imported: '.$date->format('c'));
    }
}
