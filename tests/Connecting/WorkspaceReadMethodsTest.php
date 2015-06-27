<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2013 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Connecting;

use PHPCR\WorkspaceInterface;

/** test javax.jcr.Workspace read methods (read)
 *  most of the pdf specification is in chapter 4.5.
 *
 *  Locking: getLockManager
 *  Versioning: getVersionManager
 *  level2: WorkspaceWriteMethods: clone, copy, createWorkspace, deleteWorkspace, getImportContentHandler, importXML, move
 */
class WorkspaceReadMethodsTest extends \PHPCR\Test\BaseCase
{
    protected $path = 'read/read';
    /**
     * @var WorkspaceInterface
     */
    protected $workspace;

    //4.5 Workspace Read Methods

    public function setUp()
    {
        parent::setUp();
        $this->workspace = $this->session->getWorkspace();
    }

    //4.5.2
    public function testGetSession()
    {
        $this->assertEquals($this->session, $this->workspace->getSession());
    }

    //4.5.3
    public function testGetName()
    {
        $this->assertEquals(self::$loader->getWorkspaceName(), $this->workspace->getName());
    }

    public function testGetQueryManager()
    {
        $qm = $this->workspace->getQueryManager();
        $this->assertInstanceOf('PHPCR\Query\QueryManagerInterface', $qm);
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetQueryManagerRepositoryException()
    {
        $this->markTestIncomplete('TODO: Figure how to produce this exception.');
    }

    public function testGetNamespaceRegistry()
    {
        $nr = $this->workspace->getNamespaceRegistry();
        $this->assertInstanceOf('PHPCR\NamespaceRegistryInterface', $nr);
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetNamespaceRegistryRepositoryException()
    {
        $this->markTestIncomplete('TODO: Figure how to produce this exception.');
    }

    public function testGetNodeTypeManager()
    {
        $ntm = $this->workspace->getNodeTypeManager();
        $this->assertInstanceOf('PHPCR\NodeType\NodeTypeManagerInterface', $ntm);
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetNodeTypeManagerRepositoryException()
    {
        $this->markTestIncomplete('TODO: Figure how to produce this exception.');
    }

    //4.5.4
    public function testGetAccessibleWorkspaceNames()
    {
        $names = $this->workspace->getAccessibleWorkspaceNames();
        $this->assertInternalType('array', $names);
        $this->assertContains(self::$loader->getWorkspaceName(), $names);
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGetAccessibleWorkspaceNamesRepositoryException()
    {
        $this->markTestIncomplete('TODO: Figure how to produce this exception.');
    }
}
