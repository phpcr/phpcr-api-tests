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
 * Testing the mix:lastModified support when properties should be updated
 * automatically.
 *
 * We do not enforce the update for the WorkspaceInterface::removeItem
 * operation. If the implementation can do that without performance loss, it
 * may update the date, but it is not forced to, as this is potentially quite
 * expensive.
 */
class LastModifiedUpdateTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = '10_Writing/lastmodified')
    {
        parent::setupBeforeClass($fixtures);

        self::$staticSharedFixture['session'] = self::$loader->getSessionWithLastModified();
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
        $this->node->setProperty('text', 'new');

        $this->session->save();

        $this->assertSimilarDateTime(new \DateTime(), $this->node->getPropertyValue('jcr:lastModified'));
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
        $stream = fopen('php://memory', 'w+');
        fwrite($stream, 'foo bar');
        rewind($stream);
        $this->node->setProperty('binary-data', $stream);

        $this->session->save();

        $this->assertSimilarDateTime(new \DateTime(), $this->node->getPropertyValue('jcr:lastModified'));
    }

    public function testRemoveProperty()
    {
        $this->node->setProperty('text', null);

        $this->session->save();

        $this->assertSimilarDateTime(new \DateTime(), $this->node->getPropertyValue('jcr:lastModified'));
    }
}
