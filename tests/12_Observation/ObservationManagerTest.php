<?php

namespace PHPCR\Tests\Observation;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Tests for the ObservationManager
 *
 * Covering jcr-2.8.3 spec $12
 */
class ObservationManagerTest extends \PHPCR\Test\BaseCase
{
    public function setUp()
    {
        parent::setUp();
        $this->om = $this->sharedFixture['session']->getWorkspace()->getObservationManager();
    }

    public function testGetEventJournal()
    {
        // TODO: write some real test
        //$j = $this->om->getEventJournal();
    }

}
