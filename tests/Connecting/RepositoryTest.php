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

class RepositoryTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = null)
    {
        //don't care about fixtures
        parent::setupBeforeClass($fixtures);
    }

    // 4.1 Repository
    public function testRepository()
    {
        $rep = self::$loader->getRepository();
        $this->assertInstanceOf('PHPCR\RepositoryInterface', $rep);
    }

    public function testLoginSession()
    {
        $repository = self::$loader->getRepository();
        $session = $repository->login(self::$loader->getCredentials(), self::$loader->getWorkspaceName());
        $this->assertInstanceOf('PHPCR\SessionInterface', $session);
        $this->assertEquals(self::$loader->getWorkspaceName(), $session->getWorkspace()->getName());
    }

    public function testDefaultWorkspace()
    {
        $repository = self::$loader->getRepository();
        $session = $repository->login(self::$loader->getCredentials());
        $this->assertInstanceOf('PHPCR\SessionInterface', $session);
        $this->assertEquals(self::$loader->getDefaultWorkspaceName(), $session->getWorkspace()->getName());
    }

    /**
     * external authentication.
     */
    public function testNoLogin()
    {
        $repository = self::$loader->getRepository();
        if (!self::$loader->prepareAnonymousLogin()) {
            $this->setExpectedException('PHPCR\LoginException');
        }
        $session = $repository->login(null, self::$loader->getWorkspaceName());
        $this->assertInstanceOf('PHPCR\SessionInterface', $session);
        $this->assertEquals(self::$loader->getWorkspaceName(), $session->getWorkspace()->getName());
    }

    /**
     * external authentication.
     */
    public function testNoLoginAndWorkspace()
    {
        $repository = self::$loader->getRepository();
        if (!self::$loader->prepareAnonymousLogin()) {
            $this->setExpectedException('PHPCR\LoginException');
        }
        $session = $repository->login();
        $this->assertInstanceOf('PHPCR\SessionInterface', $session);
        $this->assertEquals('default', $session->getWorkspace()->getName());
    }

    /**
     * @expectedException \PHPCR\LoginException
     */
    public function testLoginException()
    {
        $repository = self::$loader->getRepository();
        $repository->login(self::$loader->getInvalidCredentials());
    }

    /**
     * @expectedException \PHPCR\NoSuchWorkspaceException
     */
    public function testLoginNoSuchWorkspace()
    {
        $repository = self::$loader->getRepository();
        $repository->login(self::$loader->getCredentials(), 'notexistingworkspace');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testLoginRepositoryException()
    {
        $repository = self::$loader->getRepository();
        $repository->login(self::$loader->getCredentials(), '//');
    }
}
