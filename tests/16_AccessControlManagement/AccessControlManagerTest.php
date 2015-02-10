<?php
namespace PHPCR\Tests\AccessControlManagement;

require_once(__DIR__ . '/../../inc/BaseCase.php');

class AccessControlManagerTest extends \PHPCR\Test\BaseCase
{
    public function testIsMixin()
    {
        $manager = $this->session->getAccessControlManager();
        var_dump($manager->getSupportedPrivileges());

//        var_dump($manager->getPolicies('/')->getAccessControlEntries());
    }

}
