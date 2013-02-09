<?php

namespace PHPCR\Tests\PhpcrUtils;

require_once(__DIR__ . '/../../inc/BaseCase.php');

use PHPCR\PropertyType;
use PHPCR\Util\NodeHelper;

use PHPCR\Test\BaseCase;

class PurgeTest extends BaseCase
{
    public function setUp()
    {
        if (! class_exists('PHPCR\Util\NodeHelper')) {
            $this->markTestSkipped('This testbed does not have phpcr-utils available');
        }
        parent::setUp();
    }
    public function testPurge()
    {
        /** @var $session \PHPCR\SessionInterface */
        $session = $this->sharedFixture['session'];
        $emptycount = count($session->getRootNode()->getNodes()) - 1; // already has one node from fixtures

        $a = $session->getRootNode()->addNode('a', 'nt:unstructured');
        $a->addMixin('mix:referenceable');
        $b = $session->getRootNode()->addNode('b', 'nt:unstructured');
        $b->addMixin('mix:referenceable');
        $session->save();

        $a->setProperty('ref', $b, PropertyType::REFERENCE);
        $b->setProperty('ref', $a, PropertyType::REFERENCE);
        $session->save();

        NodeHelper::deleteAllNodes($session);
        $session->save();

        // if there where system nodes, they should still be here
        $this->assertCount($emptycount, $session->getRootNode()->getNodes());
    }
}