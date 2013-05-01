<?php

namespace PHPCR\Tests\Observation;

use PHPCR\SessionInterface;
use PHPCR\Observation\EventInterface;
use PHPCR\Observation\ObservationManagerInterface;
use PHPCR\Observation\EventJournalInterface;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Tests for the ObservationManager
 *
 * WARNING: With the Jackrabbit backend we noticed that sometimes the journal gets corrupted. If this
 * happens then Jackrabbit will not log anything in the journal anymore. This will make the following
 * tests to fail without a reason why.
 * To correct that problem, please restart Jackrabbit.
 *
 * Covering jcr-2.8.3 spec $12
 */
class ObservationManagerTest extends \PHPCR\Test\BaseCase
{
    // TODO: write some tests for journal filtering with combined filters.
    // All the tests here will only tests filtering on a single criterion.

    public function testGetUnfilteredEventJournal()
    {
        sleep(1); // To avoid having the same date as the journal entries generated by the fixtures loading

        $curTime = strtotime('now');

        $producerSession = self::$loader->getSession();
        $consumerSession = self::$loader->getSession();
        $consumerOm = $consumerSession->getWorkspace()->getObservationManager();

        // Produce some events in the producer session
        $this->produceEvents($producerSession);

        // Read the events in the consumer session
        $this->expectEvents($consumerOm->getEventJournal($consumerOm->createEventFilter()), $curTime);
    }

    public function testFilteredEventJournal()
    {
        sleep(1); // To avoid having the same date as the journal entries generated by the fixtures loading or other tests

        $curTime = strtotime('now');

        $session = self::$loader->getSession();
        $om = $session->getWorkspace()->getObservationManager();

        $this->produceEvents($session);

        $this->assertFilterOnEventType($om, $curTime);
        $this->assertFilterOnPathNoMatch($om, $curTime);
        $this->assertFilterOnPathNoDeep($om, $curTime);
        $this->assertFilterOnPathDeep($om, $curTime);
        $this->assertFilterOnUuidNoMatch($om, $curTime);
        $this->assertFilterOnNodeTypeNoMatch($om, $curTime);
        $this->assertFilterOnNodeTypeNoMatch($om, $curTime);
    }

    public function testFilteredEventJournalUuid()
    {
        sleep(1); // To avoid having the same date as the journal entries generated by the fixtures loading or other tests

        $curTime = strtotime('now');
        $session = self::$loader->getSession();
        $om = $session->getWorkspace()->getObservationManager();

        // Make the root node have a UUID
        $root = $session->getRootNode();
        $root->addMixin('mix:referenceable');
        $session->save();

        $root->setProperty('ref', $root, \PHPCR\PropertyType::WEAKREFERENCE);
        $session->save();

        $uuid = $session->getRootNode()->getIdentifier();

        // The journal now contains 2 events:
        // a PROP_ADDED (for the prop /ref) and a PERSIST.
        // Filtering on the root node UUID should return only one event in the journal (instead
        // of 2), because the only the PROP_ADDED event was done on a node which parent node
        // has the given UUID.
        $filter = $om->createEventFilter();
        $filter->setIdentifiers(array($uuid));
        $journal = $om->getEventJournal($filter);
        $journal->skipTo($curTime);
        $this->assertTrue($journal->valid());
        $this->assertEquals('/ref', $journal->current()->getPath());
        $this->assertEquals(EventInterface::PROPERTY_ADDED, $journal->current()->getType());

        $journal->next();

        $this->assertFalse($journal->valid());
    }

    public function testFilteredEventJournalNodeType()
    {
        sleep(1); // To avoid having the same date as the journal entries generated by the fixtures loading or other tests

        $curTime = strtotime('now');
        $session = self::$loader->getSession();
        $om = $session->getWorkspace()->getObservationManager();

        // Make the root node have a UUID
        $root = $session->getRootNode();
        $node = $root->addNode('/tests_observation');
        $node->addNode('unstructured');
        $session->save();

        // At this point the journal contains 3 events: PROP_ADDED (for setting the node type of the new node)
        // NODE_ADDED and PERSIST. The only of those event whose concerned node is of type nt:unstructured
        // is the NODE_ADDED event.
        $filter = $om->createEventFilter();
        $filter->setNodeTypes(array('nt:unstructured'));
        $journal = $om->getEventJournal($filter);
        $journal->skipTo($curTime);

        // At this point the journal
        $this->assertTrue($journal->valid());
        $this->assertEquals('/tests_observation/unstructured', $journal->current()->getPath());
        $this->assertEquals(EventInterface::NODE_ADDED, $journal->current()->getType());

        $journal->next();
        $this->assertFalse($journal->valid());
    }

    public function testUserData()
    {
        $producerSession = self::$loader->getSession();
        $consumerSession = self::$loader->getSession();
        $consumerOm = $consumerSession->getWorkspace()->getObservationManager();
        $producerOm = $producerSession->getWorkspace()->getObservationManager();

        $userDataValues = array(
            "somedifferent\" data\nnext line ä<>;&:,'x\txx",
            null,
            ""
        );

        $expectedUserDataValues = array(
            "somedifferent\" data\nnext line ä<>;&:,'x\txx",
            null,
            null
        );

        foreach ($userDataValues as $key => $userData) {

            sleep(1); // To avoid having the same date as journal entries generated by previous tests

            $curTime = time();
            $producerOm->setUserData($userData);

            // Produce some events in the producer session
            $this->produceEvents($producerSession);

            // Read the events in the consumer session
            $filter = $consumerOm->createEventFilter();
            $this->expectEventsWithUserData($consumerOm->getEventJournal($filter), $curTime, $expectedUserDataValues[$key]);
        }
    }

    protected function assertFilterOnEventType(ObservationManagerInterface $observationManager, $curTime)
    {
        $filter = $observationManager->createEventFilter();
        $filter->setEventTypes(EventInterface::PROPERTY_ADDED);
        $journal = $observationManager->getEventJournal($filter);
        $journal->skipTo($curTime);

        $this->assertTrue($journal->valid()); // There must be some events in the journal

        while ($journal->valid()) {
            $event = $journal->current();
            $journal->next();
            $this->assertEquals(EventInterface::PROPERTY_ADDED, $event->getType());
        }
    }

    protected function assertFilterOnPathNoDeep(ObservationManagerInterface $observationManager, $curTime)
    {
        $filter = $observationManager->createEventFilter();
        $filter->setAbsPath('/child');
        $journal = $observationManager->getEventJournal($filter);
        $journal->skipTo($curTime);

        $this->assertTrue($journal->valid()); // There must be some events in the journal

        while ($journal->valid()) {
            $event = $journal->current();
            $journal->next();
            $this->assertEquals('/child', $event->getPath());
        }
    }

    protected function assertFilterOnPathDeep(ObservationManagerInterface $observationManager, $curTime)
    {
        $filter = $observationManager->createEventFilter();
        $filter->setAbsPath('/child');
        $filter->setIsDeep(true);
        $journal = $observationManager->getEventJournal($filter);
        $journal->skipTo($curTime);

        $this->assertTrue($journal->valid()); // There must be some events in the journal

        while ($journal->valid()) {
            $event = $journal->current();
            $journal->next();

            // Notice the assertion is slightly different from the one in testFilterOnPathNoDeep
            $this->assertTrue(substr($event->getPath(), 0, strlen('/child')) === '/child');
        }
    }

    protected function assertFilterOnPathNoMatch(ObservationManagerInterface $observationManager, $curTime)
    {
        $filter = $observationManager->createEventFilter();
        $filter->setAbsPath('/nonexisting-path');
        $journal = $observationManager->getEventJournal($filter);
        $journal->skipTo($curTime);
        $this->assertFalse($journal->valid()); // No entry match
    }

    protected function assertFilterOnUuidNoMatch(ObservationManagerInterface $observationManager, $curTime)
    {
        $filter = $observationManager->createEventFilter();
        $filter->setIdentifiers(array());

        $journal = $observationManager->getEventJournal($filter);
        $journal->skipTo($curTime);
        $this->assertFalse($journal->valid());
    }

    protected function assertFilterOnNodeTypeNoMatch(ObservationManagerInterface $observationManager, $curTime)
    {
        $filter = $observationManager->createEventFilter();
        $filter->setNodeTypes(array('non:existing'));

        $journal = $observationManager->getEventJournal($filter);
        $journal->skipTo($curTime);
        $this->assertFalse($journal->valid());
    }

    /**
     * Produce the following entries at the end of the event journal:
     *
     *      PROPERTY_ADDED      /child/jcr:primaryType
     *      NODE_ADDED          /child
     *      PERSIST
     *      PROPERTY_ADDED      /child/prop
     *      PERSIST
     *      PROPERTY_CHANGED    /child/prop
     *      PERSIST
     *      PROPERTY_REMOVED    /child/prop
     *      PERSIST
     *      NODE_REMOVED        /child
     *      PERSIST
     *
     * WARNING:
     * If you change the events (or the order of events) produced here, you
     * will have to adapt self::expectEvents so that it checks for the correct
     * events.
     *
     * @param $session
     * @return void
     */
    protected function produceEvents(SessionInterface $session)
    {
        $root = $session->getRootNode();
        $node = $root->addNode('child');             // Will cause a PROPERTY_ADDED + a NODE_ADDED events
        $session->save();                            // Will cause a PERSIST event

        $prop = $node->setProperty('prop', 'value'); // Will case a PROPERTY_ADDED event
        $session->save();                            // Will cause a PERSIST event

        $prop->setValue('something else');           // Will cause a PROPERTY_CHANGED event
        $session->save();                            // Will cause a PERSIST event

        $prop->remove();                             // Will cause a PROPERTY_REMOVED event
        $session->save();                            // Will cause a PERSIST event

        $session->move('/child', '/moved');          // Will cause a NODE_REMOVED + NODE_ADDED + NODE_MOVED events
        $session->save();                            // Will cause a PERSIST event

        $node->remove();                             // Will cause a NODE_REMOVED event
        $session->save();                            // Will cause a PERSIST event
    }

    /**
     * Check that the journal only contains the given events (in any order)
     *
     * Algorithm:
     *      - construct an array of hash for the expected events
     *      - foreach occured event:
     *          - construct the hash
     *          - if it's in the expected events array, remove it
     *          - else it's an unexpected event
     *      - if they are still events in the expected array then all the expected events did not occur
     *
     * WARNING: this is a simple implementation that will not work if you expect the same event more than once.
     * For example if your list of expected events contains more than one PERSIST event (without path) then
     * this method will not work ! If really needed it should not be too hard to add a counter to the constructed
     * hash in order to allow the same event more than once.
     *
     * @param EventJournalInterface $journal The event journal to check
     * @param array                 $events An array of type array(array(eventType, eventPath)) containing all the expected events in an arbitrary order.
     */
    protected function expectEventsInAnyOrder(EventJournalInterface $journal, $events)
    {
        // Construct an hash map with the expected events
        $expectedEvents = array();
        foreach ($events as $event) {

            if (!is_array($event) || count($event) !== 2) {
                throw new \InvalidArgumentException("Invalid expected events array !");
            }
            // Construct an hash based on the event type and path
            $hash = sprintf('%s-%s', md5($event[0]), md5($event[1]));

            $expectedEvents[$hash] = true;
        }

        // Read the correct number of events from the journal
        /** @var $occuredEvents EventInterface[] */
        $occuredEvents = array();
        for ($i = 1; $i <= count($events); $i++) {
            $occuredEvents[] = $journal->current();
            $journal->next();
        }

        // Now check we only got the expected events
        foreach ($occuredEvents as $event) {
            $hash = sprintf('%s-%s', md5($event->getType()), md5($event->getPath()));

            if (array_key_exists($hash, $expectedEvents)) {
                unset($expectedEvents[$hash]);
            } else {
                var_dump($hash);
                $this->Fail(sprintf("Unexpected event found, type = %s, path = %s", $event->getType(), $event->getPath()));
            }
        }

        $this->assertEmpty($expectedEvents, 'Some expected events did not occur');
    }

    /**
     * Check if the expected events are in the event journal.
     *
     * WARNING:
     * This function will expect the events produced by self::produceEvents
     * If you add or remove events from self::produceEvents, you will have
     * to adapt this function so that it expects the correct events in the
     * correct order.
     *
     * @param EventJournalInterface $journal
     * @param int                   $startDate The timestamp to use with EventJournal::skipTo to reach the wanted events
     */
    protected function expectEvents(EventJournalInterface $journal, $startDate)
    {
        $journal->skipTo($startDate);

        $this->assertTrue($journal->valid());

        // Adding a node will cause a NODE_ADDED + PROPERTY_ADDED (for the primary node type)
        // The order is implementation specific (Jackrabbit will trigger the prop added before the node added event)
        $this->expectEventsInAnyOrder($journal,
            array(
                array(EventInterface::NODE_ADDED, '/child'),
                array(EventInterface::PROPERTY_ADDED, '/child/jcr%3aprimaryType'),
            )
        );

        $this->assertEvent(EventInterface::PERSIST, '', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::PROPERTY_ADDED, '/child/prop', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::PERSIST, '', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::PROPERTY_CHANGED, '/child/prop', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::PERSIST, '', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::PROPERTY_REMOVED, '/child/prop', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::PERSIST, '', $journal->current());

        $journal->next();

        // Same problem as before. Moving a node will cause a NODE_REMOVED + NODE_ADDED + NODE_MOVED
        // The order of the events is implementation specific.
        $this->expectEventsInAnyOrder($journal,
            array(
                array(EventInterface::NODE_REMOVED, '/child'),
                array(EventInterface::NODE_ADDED, '/moved'),
                array(EventInterface::NODE_MOVED, '/moved'),
            )
        );

        $this->assertEvent(EventInterface::PERSIST, '', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::NODE_REMOVED, '/moved', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::PERSIST, '', $journal->current());

        $journal->next();
        $this->assertFalse($journal->valid());
    }

    /**
     * Assert an event of the event journal has the expected type and path.
     * @param int $expectedType
     * @param string $expectedPath
     * @param \PHPCR\Observation\EventInterface $event
     * @return void
     */
    protected function assertEvent($expectedType, $expectedPath, EventInterface $event)
    {
        $this->assertInstanceOf('\PHPCR\Observation\EventInterface', $event);
        $this->assertEquals($expectedType, $event->getType());
        $this->assertEquals($expectedPath, $event->getPath());
    }

    /**
     * Assert events in the journal have the expected userdata
     *
     * @param EventJournalInterface $journal
     * @param int                   $startDate unix timestamp
     * @param mixed                 $userData  string or null
     */
    protected function expectEventsWithUserData(EventJournalInterface $journal, $startDate, $expectedUserData)
    {
        $journal->skipTo($startDate);

        $this->assertTrue($journal->valid());

        // all events in this series are expected to have the same userData (either string === string, or null === null)
        while ($journal->valid()) {
            $this->assertSame($expectedUserData, $journal->current()->getUserData());
            $journal->next();
        }
    }

    /**
     * Internal function used to dump the events in the journal for debugging
     * @param $journal
     * @return void
     */
    protected function varDumpJournal($journal)
    {
        echo "JOURNAL DUMP:\n";
        while ($journal->valid()) {
            $event = $journal->current();
            echo sprintf("%s - %s - %s\n", $event->getDate(), $event->getType(), $event->getPath())   ;
            $journal->next();
        }
    }
}
