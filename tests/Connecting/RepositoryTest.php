<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Connecting;

use PHPCR\LoginException;
use PHPCR\NoSuchWorkspaceException;
use PHPCR\RepositoryException;
use PHPCR\RepositoryInterface;
use PHPCR\SessionInterface;
use PHPCR\Test\BaseCase;

class RepositoryTest extends BaseCase
{
    public static function setupBeforeClass($fixtures = null)
    {
        // Don't care about fixtures
        parent::setupBeforeClass($fixtures);
    }

    // 4.1 Repository
    public function testRepository()
    {
        $rep = self::$loader->getRepository();
        $this->assertInstanceOf(RepositoryInterface::class, $rep);
    }

    public function testLoginSession()
    {
        $repository = self::$loader->getRepository();
        $session = $repository->login(self::$loader->getCredentials(), self::$loader->getWorkspaceName());
        $this->assertInstanceOf(SessionInterface::class, $session);
        $this->assertEquals(self::$loader->getWorkspaceName(), $session->getWorkspace()->getName());
    }

    public function testDefaultWorkspace()
    {
        $repository = self::$loader->getRepository();
        $session = $repository->login(self::$loader->getCredentials());
        $this->assertInstanceOf(SessionInterface::class, $session);
        $this->assertEquals(self::$loader->getDefaultWorkspaceName(), $session->getWorkspace()->getName());
    }

    /**
     * external authentication.
     */
    public function testNoLogin()
    {
        $repository = self::$loader->getRepository();
        if (!self::$loader->prepareAnonymousLogin()) {
            $this->expectException(LoginException::class);
        }

        $session = $repository->login(null, self::$loader->getWorkspaceName());

        $this->assertInstanceOf(SessionInterface::class, $session);
        $this->assertEquals(self::$loader->getWorkspaceName(), $session->getWorkspace()->getName());
    }

    /**
     * external authentication.
     */
    public function testNoLoginAndWorkspace()
    {
        $repository = self::$loader->getRepository();
        if (!self::$loader->prepareAnonymousLogin()) {
            $this->expectException(LoginException::class);
        }

        $session = $repository->login();
        $this->assertInstanceOf(SessionInterface::class, $session);
        $this->assertEquals('default', $session->getWorkspace()->getName());
    }

    public function testLoginException()
    {
        $this->expectException(LoginException::class);

        $repository = self::$loader->getRepository();
        $repository->login(self::$loader->getInvalidCredentials());
    }

    public function testLoginNoSuchWorkspace()
    {
        $this->expectException(NoSuchWorkspaceException::class);

        $repository = self::$loader->getRepository();
        $repository->login(self::$loader->getCredentials(), 'notexistingworkspace');
    }

    public function testLoginRepositoryException()
    {
        $this->expectException(RepositoryException::class);

        $repository = self::$loader->getRepository();
        $repository->login(self::$loader->getCredentials(), '//');
    }
}
