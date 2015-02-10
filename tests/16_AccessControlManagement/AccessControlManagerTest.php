<?php
namespace PHPCR\Tests\AccessControlManagement;

use Jackalope\Security\AccessControlList;
use Jackalope\Security\AccessControlPolicy;

require_once(__DIR__ . '/../../inc/BaseCase.php');

class AccessControlManagerTest extends \PHPCR\Test\BaseCase
{
    public function testIsMixin()
    {
        $manager = $this->session->getAccessControlManager();
        var_dump($manager->getSupportedPrivileges());

//        var_dump($manager->getPolicies('/')->getAccessControlEntries());
    }

    public function testWriteAcl()
    {
        $manager = $this->session->getAccessControlManager();

        $path = '/foo';
        $list = $manager->getApplicablePolicies('/foo');
        $policy = reset($list);
        $manager->setPolicy($path, $policy);
        $this->session->save();
    }
}
