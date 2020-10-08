<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Reading;

use PHPCR\NodeInterface;
use PHPCR\Test\BaseCase;

/**
 * Test javax.jcr.Node read methods (read) §5.6
 * With special characters.
 */
class EncodingTest extends BaseCase
{
    public static function setupBeforeClass($fixtures = '05_Reading/encoding'): void
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp(): void
    {
        parent::setUp();

        // because of the data provider the signature will not match
        $this->node = $this->rootNode->getNode('tests_read_encoding')->getNode('testEncoding');
    }

    /**
     * @dataProvider getNodeNames
     */
    public function testEncoding($name)
    {
        $this->assertTrue($this->node->hasNode($name));
        $node = $this->node->getNode($name);
        $this->assertInstanceOf(NodeInterface::class, $node);
    }

    public static function getNodeNames()
    {
        return [
            ['node-ä-x'],
            ['node-è-x'],
            ['node-ï-x'],
            ['node-%-x'],
            ['node-%2F-x'],
            ['node-;-x'],
            ['node- -x'],
            ['node-ç-x'],
            ['node-&-x'],
            ['node?'],
            ['node-¢'],
        ];
    }
}
