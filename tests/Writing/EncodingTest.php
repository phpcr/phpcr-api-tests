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

use PHPCR\NodeInterface;
use PHPCR\Test\BaseCase;

/**
 * Test javax.jcr.Node read methods (read) §5.6
 * With special characters.
 */
class EncodingTest extends BaseCase
{
    public static function setupBeforeClass($fixtures = '10_Writing/encoding')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        parent::setUp();

        // because of the data provider the signature will not match
        $this->node = $this->rootNode->getNode('tests_write_encoding')->getNode('testEncoding');
    }

    /**
     * @dataProvider getNodeNames
     */
    public function testEncoding($name)
    {
        $node = $this->node->addNode($name);
        $this->assertInstanceOf(NodeInterface::class, $node);

        $session = $this->saveAndRenewSession();
        $node = $session->getNode('/tests_write_encoding/testEncoding');
        $this->assertTrue($node->hasNode($name));
        $this->assertInstanceOf(NodeInterface::class, $node->getNode($name));
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
        ];
    }

    /**
     * @dataProvider getPropertyValues
     */
    public function testEncodingPropertyValues($value, $type)
    {
        $this->node->setProperty($type, $value);
        $session = $this->saveAndRenewSession();
        $this->assertEquals($value, $session->getRootNode()->getNode('tests_write_encoding')->getNode('testEncoding')->getPropertyValue($type));
    }

    public static function getPropertyValues()
    {
        return [
            ['PHPCR\Query\QueryInterface', 'backslash'],
            ['PHPCR\\\\Query\\\\QueryInterface', 'doublebackslash'],
            ['"\'', 'quotes'],
            ['a\\\'\\\'b\\\'\\\'c', 'quotesandbackslash'],
            ['foo & bar&baz', 'ampersand'],
        ];
    }
}
