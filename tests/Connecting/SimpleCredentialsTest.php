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

use PHPCR\CredentialsInterface;
use PHPCR\SimpleCredentials;
use PHPCR\Test\BaseCase;

/**
 * Test the simple credentials PHPCR class.
 */
class SimpleCredentialsTest extends BaseCase
{
    public static function setupBeforeClass($fixtures = false): void
    {
        // Don't care about fixtures
        parent::setupBeforeClass($fixtures);
    }

    const CR_USER = 'foo';
    const CR_PASS = 'bar';

    /** try to create credentials from this user/password */
    protected function assertSimpleCredentials($user, $password)
    {
        $cr = new SimpleCredentials($user, $password);
        $this->assertInstanceOf(CredentialsInterface::class, $cr);

        return $cr;
    }

    // 6.1.2 Credentials
    public function testSimpleCredentials()
    {
        $this->assertSimpleCredentials(self::CR_USER, self::CR_PASS);
    }

    public function testGetUser()
    {
        $cr = $this->assertSimpleCredentials(self::CR_USER, self::CR_PASS);
        $user = $cr->getUserID();
        $this->assertEquals($user, self::CR_USER);
    }

    // The password gets currently cleared for safety
    public function testGetPassword()
    {
        $cr = $this->assertSimpleCredentials(self::CR_USER, self::CR_PASS);
        $pass = $cr->getPassword();
        $this->assertSame('bar', $pass);
    }

    public function testAttributes()
    {
        $attrName = 'foo';
        $attrValue = 'bar';
        $cr = $this->assertSimpleCredentials(self::CR_USER, self::CR_PASS);
        $cr->setAttribute($attrName, $attrValue);
        $this->assertEquals($attrValue, $cr->getAttribute($attrName));
        $attrs = $cr->getAttributeNames();
        $this->assertIsArray($attrs);
        $this->assertContains($attrName, $attrs);
        $cr->removeAttribute($attrName);
        $this->assertNull($cr->getAttribute($attrName));
        $cr->removeAttribute('nonexistent'); // Removing nonexistent attribute should not cause an error
    }
}
