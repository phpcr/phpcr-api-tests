<?php
namespace PHPCR\Tests\Connecting;

require_once(dirname(__FILE__) . '/../../inc/BaseCase.php');

class RepositoryDescriptorsTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = false)
    {
        //don't care about fixtures
        parent::setupBeforeClass($fixtures);
    }

    //Those constants need to be defined in the bootstrap file
    protected $expectedDescriptors = array(
        SPEC_VERSION_DESC,
        SPEC_NAME_DESC,
        REP_VENDOR_DESC,
        REP_VENDOR_URL_DESC,
        REP_NAME_DESC,
        REP_VERSION_DESC,
        LEVEL_1_SUPPORTED,
        LEVEL_2_SUPPORTED,
        OPTION_TRANSACTIONS_SUPPORTED,
        OPTION_VERSIONING_SUPPORTED,
        OPTION_OBSERVATION_SUPPORTED,
        OPTION_LOCKING_SUPPORTED,
        OPTION_QUERY_SQL_SUPPORTED,
        QUERY_XPATH_POS_INDEX,
        QUERY_XPATH_DOC_ORDER
    );

    // 24.2 Repository Descriptors
    public function testDescriptorKeys()
    {
        $rep = self::$loader->getRepository();
        $keys = $rep->getDescriptorKeys();
        $this->assertInternalType('array', $keys);
        $this->assertNotEmpty($keys);
        foreach ($this->expectedDescriptors as $descriptor) {
            $this->assertContains($descriptor, $keys);
        }
    }

    //TODO: Check if the values are compatible to the spec
    public function testDescription()
    {
        $rep = self::$loader->getRepository();
        foreach ($this->expectedDescriptors as $descriptor) {
            $str = $rep->getDescriptor($descriptor);
            $this->assertInternalType('string', $str);
            $this->assertNotEmpty($str);
        }
    }

    public function testIsStandardDescriptor()
    {
        $this->markTestSkipped('TODO: implement');
    }
}
