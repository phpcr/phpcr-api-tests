<?php
namespace PHPCR\Tests\AccessControlManagement;

use Jackalope\Security\Principal;
use PHPCR\Security\AccessControlListInterface;
use PHPCR\Security\AccessControlManagerInterface;
use PHPCR\Security\PrivilegeInterface;

require_once(__DIR__ . '/../../inc/BaseCase.php');

class AccessControlManagerTest extends \PHPCR\Test\BaseCase
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

    public function testGetSupportedPrivileges()
    {
        $privileges = $this->manager->getSupportedPrivileges('/tests_general_base');
        $privileges = $this->manager->getSupportedPrivileges();
    }

    public function testGetPolicies()
    {
//        var_dump($manager->getPolicies('/')->getAccessControlEntries());
    }

    public function testWriteAcl()
    {
        $manager = $this->session->getAccessControlManager();

        $path = '/tests_general_base';
        $list = $manager->getApplicablePolicies($path);
        /** @var $policy AccessControlListInterface */
        $policy = reset($list);
        $policy->addAccessControlEntry(new Principal('foo'), array($this->manager->privilegeFromName(PrivilegeInterface::JCR_READ)));
        $manager->setPolicy($path, $policy);
        $this->session->save();

        $session = $this->renewSession();
        $acls = $session->getAccessControlManager()->getPolicies($path);
        $acls = $session->getAccessControlManager()->getApplicablePolicies($path);
        $this->assertCount(1, $acls);
    }

    public function testGetPrivilegeFromName()
    {
        $privilege = $this->manager->privilegeFromName(PrivilegeInterface::JCR_READ);
        $this->assertInstanceof('\PHPCR\Security\PrivilegeInterface', $privilege);
        $this->assertEquals(PrivilegeInterface::JCR_READ, $privilege->getName());
        $this->assertFalse($privilege->isAbstract());
        $this->assertFalse($privilege->isAggregate());
        $this->assertEquals(array(), $privilege->getAggregatePrivileges());
    }


    /**
     * @expectedException \PHPCR\Security\AccessControlException
     */
    public function testGetPrivilegeByNameNotFound()
    {
        $this->manager->privilegeFromName('foobar');
    }
}
