<?php
require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

class Connecting_4_RepositoryFactoryTest extends phpcr_suite_baseCase
{
    //don't care about fixtures

    // 4.1 Repository
    public function testRepositoryFactory()
    {
        $class = getRepositoryFactoryClass();
        $factory = new $class;
        $this->assertInstanceOf('PHPCR\RepositoryFactoryInterface', $factory);

        $repo = $factory->getRepository(getRepositoryFactoryParameters($this->config));
        $this->assertInstanceOf('PHPCR\RepositoryInterface', $repo);
    }

}
