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

/**
 * Test javax.jcr.Node read methods (read) §5.6
 * With special characters.
 */
class EncodingTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = '05_Reading/encoding')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
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
        $this->assertInstanceOf('PHPCR\NodeInterface', $node);
    }

    public static function getNodeNames()
    {
        return array(
            array('node-ä-x'),
            array('node-è-x'),
            array('node-ï-x'),
            array('node-%-x'),
            array('node-%2F-x'),
            array('node-;-x'),
            array('node- -x'),
            array('node-ç-x'),
            array('node-&-x'),
            array('node?'),
            array('node-¢'),
        );
    }
}
