<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\PermissionsAndCapabilities;

use PHPCR\Security\AccessControlException;
use PHPCR\Test\BaseCase;

/**
 * Test Permission read methods.
 */
class PermissionReadTest extends BaseCase
{
    public function testCheckPermission()
    {
        // A test without assertion is automatically marked skipped so here we
        // test no exception has occured
        $flag = false;
        try {
            $this->session->checkPermission('/tests_general_base', 'read');
        } catch (AccessControlException $ex) {
            $flag = true;
        }
        $this->assertFalse($flag);

        $flag = false;
        try {
            $this->session->checkPermission('/tests_general_base/numberPropertyNode/jcr:content/foo', 'read');
        } catch (AccessControlException $ex) {
            $flag = true;
        }
        $this->assertFalse($flag);
    }

    public function testHasPermission()
    {
        $this->assertTrue($this->session->hasPermission('/tests_general_base', 'read'));
        $this->assertTrue($this->session->hasPermission('/tests_general_base/numberPropertyNode/jcr:content/foo', 'read'));
        // TODO: check a WTF, this is supposed to fail, right? yet it succeeds
        //       if the test is moved after testCheckPermissionAccessControlException it fails
        $this->assertTrue($this->session->hasPermission('/tests_general_base/numberPropertyNode/jcr:content/foo', 'add_node')); //we have permission, but this node is not capable of the operation
    }

    public function testCheckPermissionAccessControlException()
    {
        $this->expectException(AccessControlException::class);

        // Login with restricted credentials
        $session = self::$loader->getSession(self::$loader->getRestrictedCredentials());

        $session->checkPermission('/tests_general_base/numberPropertyNode/jcr:content/foo', 'add_node');
        $session->logout();
    }

    public function testHasCapability()
    {
        $node = $this->session->getNode('/tests_general_base');
        $this->assertTrue($this->session->hasCapability('getReferences', $node, []), 'Does not have getReferences capability');
        $this->assertTrue($this->session->hasCapability('getProperty', $node, ['foo']));
        $property = $this->session->getProperty('/tests_general_base/numberPropertyNode/jcr:content/foo');
        $this->assertTrue($this->session->hasCapability('getNode', $property, []));
        //$this->assertFalse($this->session->hasCapability('inexistentXXX', $property, []));
        //actually, the repository is not required to know, it can always say that the info can not be determined and return true. this makes me think that this method is pretty useless...
    }
}
