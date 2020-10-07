<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\PhpcrUtils;

use PHPCR\NodeInterface;
use PHPCR\Util\NodeHelper;
use PHPCR\Test\BaseCase;

/**
 * Functional test for the node helper.
 */
class NodeHelperTest extends BaseCase
{
    protected function setUp(): void
    {
        if (!class_exists(NodeHelper::class)) {
            $this->markTestSkipped('This testbed does not have phpcr-utils available');
        }
        parent::setUp();
    }

    public function testCreatePartialPath()
    {
        $node = NodeHelper::createPath($this->session, '/tests_general_base/index.txt/jcr:content/test/node');
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->session->save();
    }

    public function testCreateNewPath()
    {
        $node = NodeHelper::createPath($this->session, '/tests_nodehelper/test/node');
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->session->save();
    }

    public function testCreateExistingPath()
    {
        $node = NodeHelper::createPath($this->session, '/tests_general_base/index.txt');
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertFalse($this->session->hasPendingChanges());
    }
}
