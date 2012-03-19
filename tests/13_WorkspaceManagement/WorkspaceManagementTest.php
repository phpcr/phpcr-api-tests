<?php
namespace PHPCR\Tests\WorkspaceManagement;

require_once(__DIR__ . '/../../inc/BaseCase.php');

//6.5 Import Repository Content
class WorkspaceManagementTest extends \PHPCR\Test\BaseCase
{
    public function testCreateWorkspace()
    {
        $workspacename = 'test' . time();
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $workspace->createWorkspace($workspacename);

        $session = self::$loader->getRepository()->login(self::$loader->getCredentials(), $workspacename);
        $this->assertTrue($session->isLive());

        return $workspacename;
    }

    /**
     * @depends testCreateWorkspace
     * @expectedException PHPCR\RepositoryException
     */
    public function testCreateWorkspaceExisting($workspacename)
    {
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $workspace->createWorkspace($workspacename);
    }

    public function testCreateWorkspaceWithSource()
    {
        $workspacename = 'testWithSource' . time();
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $workspace->createWorkspace($workspacename, $workspace->getName());

        $session = self::$loader->getRepository()->login(self::$loader->getCredentials(), $workspacename);

        $this->assertTrue($session->nodeExists('/index.txt'));
    }

    /**
     * @expectedException PHPCR\NoSuchWorkspaceException
     */
    public function testCreateWorkspaceWithInvalidSource()
    {
        $workspacename = 'testWithSource' . time();
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $workspace->createWorkspace($workspacename, 'thisworkspaceisnotexisting');
    }

    /**
     * @depends testCreateWorkspace
     */
    public function testDeleteWorkspace($workspacename)
    {
        $workspace = $this->sharedFixture['session']->getWorkspace();
        $this->assertContains($workspacename, $workspace->getAccessibleWorkspaceNames());
        $workspace->deleteWorkspace($workspacename);

        $workspace = self::$loader->getSession()->getWorkspace();
        $this->assertNotContains($workspacename, $workspace->getAccessibleWorkspaceNames());
    }
}
