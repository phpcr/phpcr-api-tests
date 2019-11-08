<?php
namespace PHPCR\Tests\AccessControlManagement;

use PHPCR\Security\AccessControlManagerInterface;
use PHPCR\Security\PrivilegeInterface;

require_once(__DIR__ . '/../../inc/BaseCase.php');

class PrivilegeTest extends \PHPCR\Test\BaseCase
{
    /**
     * @var AccessControlManagerInterface
     */
    private $manager;


    public function setup()
    {
        parent::setUp();
        $this->manager = $this->session->getAccessControlManager();
    }

    public function testDeclaredAggregatePrivileges()
    {
        $privileges = $this->manager->getSupportedPrivileges();
        $foundAll = false;

        foreach ($privileges as $privilege) {
            $this->assertInstanceof('\PHPCR\Security\PrivilegeInterface', $privilege);
            if (PrivilegeInterface::JCR_ALL === $privilege->getName()) {
                $declared = $privilege->getDeclaredAggregatePrivileges();
                $this->assertInternalType('array', $declared);
                $this->assertContainsOnlyInstancesOf('\PHPCR\Security\PrivilegeInterface', $declared);
                $foundAll = true;
            }
        }

        if (!$foundAll) {
            $this->fail('Privilege ' . PrivilegeInterface::JCR_ALL . ' not found');
        }
    }
}
