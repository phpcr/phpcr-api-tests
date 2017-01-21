<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\WorkspaceManagement;

use PHPCR\NodeType\NoSuchNodeTypeException;
use PHPCR\RepositoryException;
use PHPCR\Test\BaseCase;

// 6.5 Import Repository Content
class WorkspaceManagementTest extends BaseCase
{
    public function testCreateWorkspace()
    {
        $workspacename = 'test'.time();
        $workspace = $this->session->getWorkspace();
        $workspace->createWorkspace($workspacename);

        $session = self::$loader->getRepository()->login(self::$loader->getCredentials(), $workspacename);
        $this->assertTrue($session->isLive());

        return $workspacename;
    }

    /**
     * @depends testCreateWorkspace
     */
    public function testCreateWorkspaceExisting($workspacename)
    {
        $this->expectException(RepositoryException::class);

        $workspace = $this->session->getWorkspace();
        $workspace->createWorkspace($workspacename);
    }

    public function testCreateWorkspaceWithSource()
    {
        $workspacename = 'testWithSource'.time();
        $workspace = $this->session->getWorkspace();
        $workspace->createWorkspace($workspacename, $workspace->getName());

        $session = self::$loader->getRepository()->login(self::$loader->getCredentials(), $workspacename);

        $this->assertTrue($session->nodeExists('/tests_general_base/index.txt'));
    }

    public function testCreateWorkspaceWithInvalidSource()
    {
        $this->expectException(NoSuchNodeTypeException::class);

        $workspacename = 'testWithSource'.time();
        $workspace = $this->session->getWorkspace();
        $workspace->createWorkspace($workspacename, 'thisworkspaceisnotexisting');
    }

    /**
     * @depends testCreateWorkspace
     */
    public function testDeleteWorkspace($workspacename)
    {
        $workspace = $this->session->getWorkspace();
        $this->assertContains($workspacename, $workspace->getAccessibleWorkspaceNames());
        $workspace->deleteWorkspace($workspacename);

        $workspace = self::$loader->getSession()->getWorkspace();
        $this->assertNotContains($workspacename, $workspace->getAccessibleWorkspaceNames());
    }
}
