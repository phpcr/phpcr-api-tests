<?php
namespace PHPCR\Tests\Connecting;

require_once(__DIR__ . '/../../inc/BaseCase.php');

class RepositoryFactoryTest extends \PHPCR\Test\BaseCase
{
    public static function setupBeforeClass($fixtures = false)
    {
        //don't care about fixtures
        parent::setupBeforeClass($fixtures);
    }

    // 4.1 Repository
    public function testRepositoryFactory()
    {
        $class = self::$loader->getRepositoryFactoryClass();
        $factory = new $class;
        $repo = $factory->getRepository(self::$loader->getRepositoryFactoryParameters());
        $this->assertInstanceOf('PHPCR\RepositoryInterface', $repo);
    }

}
