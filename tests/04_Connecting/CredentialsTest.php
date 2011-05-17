<?php
require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

class Connecting_4_CredentialsTest extends jackalope_baseCase
{
    //don't care about fixtures

    const CR_USER = 'foo';
    const CR_PASS = 'bar';

    // 6.1.2 Credentials
    public function testSimpleCredentials()
    {
        $cr = $this->assertSimpleCredentials(self::CR_USER, self::CR_PASS);
    }

    public function testGetUser()
    {
        $cr = $this->assertSimpleCredentials(self::CR_USER, self::CR_PASS);
        $user = $cr->getUserId();
        $this->assertEquals($user, self::CR_USER);
    }

    //The password gets currently cleared for safety
    public function testGetPassword()
    {
        $cr = $this->assertSimpleCredentials(self::CR_USER, self::CR_PASS);
        $pass = $cr->getPassword();
        $this->assertSame($pass, 'bar');
    }

    public function testAttributes()
    {
        $attrName = 'foo';
        $attrValue = 'bar';
        $cr = $this->assertSimpleCredentials(self::CR_USER, self::CR_PASS);
        $cr->setAttribute($attrName, $attrValue);
        $this->assertEquals($attrValue, $cr->getAttribute($attrName));
        $attrs = $cr->getAttributeNames();
        $this->assertType('array', $attrs);
        $this->assertContains($attrName, $attrs);
        $cr->removeAttribute($attrName);
        $this->assertNull($cr->getAttribute($attrName));
        $cr->removeAttribute('nonexistent'); //removing nonexistent attribute should not cause an error
    }
}
