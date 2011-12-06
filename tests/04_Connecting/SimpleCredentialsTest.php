<?php
namespace PHPCR\Tests\Connecting;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Test the simple credentials PHPCR class
 */
class SimpleCredentialsTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = false)
    {
        //don't care about fixtures
        parent::setupBeforeClass($fixtures);
    }

    const CR_USER = 'foo';
    const CR_PASS = 'bar';

    /** try to create credentials from this user/password */
    protected function assertSimpleCredentials($user, $password)
    {
        $cr = new \PHPCR\SimpleCredentials($user, $password);
        $this->assertInstanceOf('PHPCR\CredentialsInterface', $cr);
        return $cr;
    }

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
        $this->assertInternalType('array', $attrs);
        $this->assertContains($attrName, $attrs);
        $cr->removeAttribute($attrName);
        $this->assertNull($cr->getAttribute($attrName));
        $cr->removeAttribute('nonexistent'); //removing nonexistent attribute should not cause an error
    }
}
