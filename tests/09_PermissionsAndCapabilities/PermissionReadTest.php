<?php

namespace PHPCR\Tests\PermissionsAndCapabilities;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Test Permission read methods
 */
class PermissionReadTest extends \PHPCR\Test\BaseCase
{
    public function testCheckPermission()
    {
        // A test without assertion is automatically marked skipped so here we
        // test no exception has occured
        $flag = false;
        try {
            $this->sharedFixture['session']->checkPermission('/tests_general_base', 'read');
        } catch (\PHPCR\Security\AccessControlException $ex) {
            $flag = true;
        }
        $this->assertFalse($flag);

        $flag = false;
        try {
            $this->sharedFixture['session']->checkPermission('/tests_general_base/numberPropertyNode/jcr:content/foo', 'read');
        } catch (\PHPCR\Security\AccessControlException $ex) {
            $flag = true;
        }
        $this->assertFalse($flag);
    }

    public function testHasPermission()
    {
        $this->assertTrue($this->sharedFixture['session']->hasPermission('/tests_general_base', 'read'));
        $this->assertTrue($this->sharedFixture['session']->hasPermission('/tests_general_base/numberPropertyNode/jcr:content/foo', 'read'));
        // TODO: check a WTF, this is supposed to fail, right? yet it succeeds
        //       if the test is moved after testCheckPermissionAccessControlException it fails
        $this->assertTrue($this->sharedFixture['session']->hasPermission('/tests_general_base/numberPropertyNode/jcr:content/foo', 'add_node')); //we have permission, but this node is not capable of the operation
    }

    /**
     * @expectedException \PHPCR\Security\AccessControlException
     */
    public function testCheckPermissionAccessControlException()
    {
        // Login with restricted credentials
        $session = self::$loader->getSession(self::$loader->getRestrictedCredentials());

        $session->checkPermission('/tests_general_base/numberPropertyNode/jcr:content/foo', 'add_node');
        $session->logout();
    }

    public function testHasCapability()
    {
        $node = $this->sharedFixture['session']->getNode('/tests_general_base');
        $this->assertTrue($this->sharedFixture['session']->hasCapability('getReferences', $node, array()), 'Does not have getReferences capability');
        $this->assertTrue($this->sharedFixture['session']->hasCapability('getProperty', $node, array('foo')));
        $property = $this->sharedFixture['session']->getProperty('/tests_general_base/numberPropertyNode/jcr:content/foo');
        $this->assertTrue($this->sharedFixture['session']->hasCapability('getNode', $property, array()));
        //$this->assertFalse($this->sharedFixture['session']->hasCapability('inexistentXXX', $property, array()));
        //actually, the repository is not required to know, it can always say that the info can not be determined and return true. this makes me think that this method is pretty useless...
    }

}