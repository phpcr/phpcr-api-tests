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

use Jackalope\Session;

/**
 * Testing the mix:lastModified support when the values are never updated
 * automatically.
 */
class LastModifiedTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = '10_Writing/lastmodified')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        parent::setUp();

        if (self::$loader->doesSessionLastModified()) {
            $this->markTestSkipped('Session updates lastModified automatically');
        }
    }

    /**
     * Add mixin to an existing and to a new node.
     */
    public function testCreate()
    {
        $this->assertFalse($this->node->hasProperty('jcr:lastModified'));
        $this->node->addMixin('mix:lastModified');

        $this->session->save();

        $this->assertTrue($this->node->hasProperty('jcr:lastModifiedBy'));
        $this->assertTrue($this->node->hasProperty('jcr:lastModified'));
        $this->assertSimilarDateTime(new \DateTime(), $this->node->getPropertyValue('jcr:lastModified'));

        $node = $this->node->addNode('child');
        $node->addMixin('mix:lastModified');

        $this->session->save();

        $this->assertTrue($node->hasProperty('jcr:lastModifiedBy'));
        $this->assertTrue($node->hasProperty('jcr:lastModified'));
        $this->assertSimilarDateTime(new \DateTime(), $node->getPropertyValue('jcr:lastModified'));
    }

    /**
     * When setting the lastModified information manually, it should not be
     * overwritten.
     */
    public function testCreateManual()
    {
        $this->assertFalse($this->node->hasProperty('jcr:lastModified'));
        $this->node->addMixin('mix:lastModified');
        $this->node->setProperty('jcr:lastModifiedBy', 'me');

        $this->session->save();

        $this->assertTrue($this->node->hasProperty('jcr:lastModifiedBy'));
        $this->assertEquals('me', $this->node->getPropertyValue('jcr:lastModifiedBy'));
        $this->assertTrue($this->node->hasProperty('jcr:lastModified'));
        $this->assertSimilarDateTime(new \DateTime(), $this->node->getPropertyValue('jcr:lastModified'));

        $node = $this->node->addNode('child');
        $node->addMixin('mix:lastModified');
        $date = new \DateTime('2012-01-02');
        $node->setProperty('jcr:lastModified', $date);

        $this->session->save();

        $this->assertTrue($node->hasProperty('jcr:lastModifiedBy'));
        $this->assertTrue($node->hasProperty('jcr:lastModified'));
        $this->assertEqualDateTime($date, $node->getPropertyValue('jcr:lastModified'));
    }

    public function testUpdateText()
    {
        $date = $this->node->getPropertyValue('jcr:lastModified');
        $this->node->setProperty('text', 'new');

        $this->session->save();

        $this->assertEqualDateTime($date, $this->node->getPropertyValue('jcr:lastModified'));
    }

    public function testUpdateManual()
    {
        $date = new \DateTime('2013-10-10');
        $this->node->setProperty('jcr:lastModified', $date);
        $this->node->setProperty('text', 'new');

        $this->session->save();

        $this->assertEqualDateTime($date, $this->node->getPropertyValue('jcr:lastModified'));
    }

    public function testUpdateBinary()
    {
        $date = $this->node->getPropertyValue('jcr:lastModified');
        $stream = fopen('php://memory', 'w+');
        fwrite($stream, 'foo bar');
        rewind($stream);
        $this->node->setProperty('binary-data', $stream);

        $this->session->save();

        $this->assertEqualDateTime($date, $this->node->getPropertyValue('jcr:lastModified'));
    }

    public function testRemoveProperty()
    {
        $date = $this->node->getPropertyValue('jcr:lastModified');
        $this->node->setProperty('text', null);

        $this->session->save();

        $this->assertEqualDateTime($date, $this->node->getPropertyValue('jcr:lastModified'));
    }
}
