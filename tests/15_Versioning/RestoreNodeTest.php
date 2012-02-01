<?php
namespace PHPCR\Tests\Versioning;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
* Testing whether node property manipulations work correctly
*
* Covering jcr-2.8.3 spec $15.1
*/
class RestoreNodeTest extends \PHPCR\Test\BaseCase
{
    static public function setupBeforeClass($fixtures = '15_Versioning/base')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        parent::setUp();
        $this->vm = $this->sharedFixture['session']->getWorkspace()->getVersionManager();
    }

    public function testRestoreversion()
    {
        $this->vm->checkout('/tests_version_base/versioned');
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');

        $node->setProperty('foo', 'bar');
        $this->sharedFixture['session']->save();
        $version = $this->vm->checkin('/tests_version_base/versioned');

        $this->vm->checkout('/tests_version_base/versioned');

        $node->setProperty('foo', 'bar2');
        $this->sharedFixture['session']->save();
        $this->vm->checkin('/tests_version_base/versioned');

        $this->vm->checkout('/tests_version_base/versioned');
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');

        $node->setProperty('foo', 'bar3');
        $this->sharedFixture['session']->save();
        $this->vm->checkin('/tests_version_base/versioned');

        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        // Read the OLD value out of the var and fill the cache
        $this->assertEquals('bar3', $node->getProperty('foo')->getValue());

        // Restore the 1.0 value aka 'bar'
        $this->vm->restore(true, $version->getName(), '/tests_version_base/versioned');

        // Read the NEW value and test if the cache has been cleared.
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        $this->assertEquals('bar', $node->getProperty('foo')->getValue());

         // Read the NEW value out of the var after the session is renewed and the cache clear
        $this->renewSession();
        $node = $this->sharedFixture['session']->getNode('/tests_version_base/versioned');
        $this->assertEquals('bar', $node->getProperty('foo')->getValue());

    }

}
