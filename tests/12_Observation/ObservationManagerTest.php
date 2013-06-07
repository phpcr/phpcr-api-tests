<?php

namespace PHPCR\Tests\Observation;

use PHPCR\RepositoryException;
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

    /**
     * Base path to the node for this test run.
     * Do not use $this->node to avoid confusion as we run several parallel sessions.
     * @var string
     */
    private $nodePath;

    static public function setupBeforeClass($fixtures = '12_Observation/manager')
    {
        parent::setupBeforeClass($fixtures);
        sleep(1); // To avoid having the same date as the journal entries generated by the fixtures loading
    }

    public function setUp()
    {
        parent::setUp();
        //all tests in this suite rely on the trick to have the node populated from the fixtures
        $this->assertInstanceOf('PHPCR\NodeInterface', $this->node, "Something went wrong with fixture loading");
        $this->nodePath = $this->node->getPath();
        $this->node = null;
    }

    public function testGetUnfilteredEventJournal()
    {
        $curTime = time() * 1000;

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
        $curTime = time() * 1000;
        sleep(1);

        $session = self::$loader->getSession();
        $om = $session->getWorkspace()->getObservationManager();

        $this->produceEvents($session);

        $this->assertFilterOnEventType($om, $curTime);
        $this->assertFilterOnPathNoMatch($om, $curTime);
        $this->assertFilterOnPathNoDeep($om, $curTime);
        $this->assertFilterOnPathDeep($om, $curTime);
        $this->assertFilterOnUuidNoMatch($om, $curTime);
        $this->assertFilterOnNodeTypeNoMatch($om, $curTime);
    }

    public function testFilteredEventJournalUuid()
    {
        $curTime = time() * 1000;
        $session = self::$loader->getSession();
        $om = $session->getWorkspace()->getObservationManager();

        $node = $session->getNode($this->nodePath);
        $node->setProperty('ref', $node, \PHPCR\PropertyType::WEAKREFERENCE);
        $session->save();
        $this->produceEvents($session);

        $uuid = $node->getIdentifier();

        // The journal now contains 2 events:
        // a PROP_ADDED (for the prop /ref) and a PERSIST.
        // Filtering on node UUID should return only one event in the journal (instead
        // of 2), because only the PROP_ADDED event was done on a node which has the given UUID.
        $filter = $om->createEventFilter();
        $filter->setIdentifiers(array($uuid));
        $journal = $om->getEventJournal($filter);
        $journal->skipTo($curTime);
        $this->assertTrue($journal->valid());
        $this->assertEquals($this->nodePath . '/ref', $journal->current()->getPath());
        $this->assertEquals(EventInterface::PROPERTY_ADDED, $journal->current()->getType());

        $journal->next();

        $this->assertFalse($journal->valid());
    }

    public function testFilteredEventJournalNodeType()
    {
        $session = self::$loader->getSession();

        $curTime = time() * 1000;
        $om = $session->getWorkspace()->getObservationManager();

        $parent = $session->getNode($this->nodePath);
        $parent->addNode('folder', 'nt:folder');
        $session->save();

        // At this point the journal contains 3 events: PROP_ADDED (for setting the node type of the new node)
        // NODE_ADDED and PERSIST. The only of those event whose concerned node is of type nt:folder
        // is the NODE_ADDED event.
        $filter = $om->createEventFilter();
        $filter->setNodeTypes(array('nt:folder'));
        $journal = $om->getEventJournal($filter);
        $journal->skipTo($curTime);

        // At this point the journal should only contain the NODE_ADDED event
        $this->assertTrue($journal->valid());
        $this->assertEquals($this->nodePath . '/folder', $journal->current()->getPath());
        $this->assertEquals(EventInterface::NODE_ADDED, $journal->current()->getType());
        $type = $journal->current()->getPrimaryNodeType();
        $this->assertInstanceOf('PHPCR\NodeType\NodeTypeInterface', $type);
        $this->assertEquals('nt:unstructured', $type->getName()); // the event is on the parent

        $mixins = $journal->current()->getMixinNodeTypes();
        $this->assertCount(1, $mixins);
        $mixin = reset($mixins);
        $this->assertInstanceOf('PHPCR\NodeType\NodeTypeInterface', $mixin);
        $this->assertEquals('mix:referenceable', $mixin->getName());

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

            $curTime = time() * 1000;
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
        $filter->setAbsPath($this->nodePath . '/child');
        $journal = $observationManager->getEventJournal($filter);
        $journal->skipTo($curTime);

        $this->assertTrue($journal->valid()); // There must be some events in the journal

        while ($journal->valid()) {
            $event = $journal->current();
            $journal->next();
            $this->assertEquals($this->nodePath . '/child', $event->getPath());
        }
    }

    protected function assertFilterOnPathDeep(ObservationManagerInterface $observationManager, $curTime)
    {
        $filter = $observationManager->createEventFilter();
        $filter->setAbsPath($this->nodePath . '/child');
        $filter->setIsDeep(true);
        $journal = $observationManager->getEventJournal($filter);
        $journal->skipTo($curTime);

        $this->assertTrue($journal->valid()); // There must be some events in the journal

        while ($journal->valid()) {
            $event = $journal->current();
            $journal->next();

            // Notice the assertion is slightly different from the one in testFilterOnPathNoDeep
            $this->assertTrue(substr($event->getPath(), 0, strlen($this->nodePath . '/child')) === $this->nodePath . '/child');
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
        $parent = $session->getNode($this->nodePath);
        // Will cause a PROPERTY_ADDED + a NODE_ADDED events
        $node = $parent->addNode('child');
        // Will cause a PERSIST event
        $session->save();

        // Will case a PROPERTY_ADDED event
        $prop = $node->setProperty('prop', 'value');
        // Will cause a PERSIST event
        $session->save();

        // Will cause a PROPERTY_CHANGED event
        $prop->setValue('something else');
        // Will cause a PERSIST event
        $session->save();

        // Will cause a PROPERTY_REMOVED event
        $prop->remove();
        // Will cause a PERSIST event
        $session->save();

        // Will cause a NODE_REMOVED + NODE_ADDED + NODE_MOVED events
        $session->move($node->getPath(), $this->nodePath . '/moved');
        // Will cause a PERSIST event
        $session->save();

        // Will cause a NODE_REMOVED event
        $node->remove();
        // Will cause a PERSIST event
        $session->save();
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
                $this->Fail(sprintf("Unexpected event found, type = %s, path = %s, hash = %s", $event->getType(), $event->getPath(), $hash));
            }
        }

        $this->assertEmpty($expectedEvents, 'Some expected events did not occur');

        return $occuredEvents;
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
                array(EventInterface::NODE_ADDED, $this->nodePath . '/child'),
                array(EventInterface::PROPERTY_ADDED, $this->nodePath . '/child/jcr%3aprimaryType'),
            )
        );

        $this->assertEvent(EventInterface::PERSIST, '', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::PROPERTY_ADDED, $this->nodePath . '/child/prop', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::PERSIST, '', $journal->current());

        try {
            $journal->current()->getPrimaryNodeType();
            $this->fail('Getting the node type of an event that is not about a node should fail');
        } catch(RepositoryException $e) {
            // expected
        }
        try {
            $journal->current()->getMixinNodeTypes();
            $this->fail('Getting the mixin node types of an event that is not about a node should fail');
        } catch(RepositoryException $e) {
            // expected
        }

        $journal->next();
        $this->assertEvent(EventInterface::PROPERTY_CHANGED, $this->nodePath . '/child/prop', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::PERSIST, '', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::PROPERTY_REMOVED, $this->nodePath . '/child/prop', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::PERSIST, '', $journal->current());

        $journal->next();

        // Same problem as before. Moving a node will cause a NODE_REMOVED + NODE_ADDED + NODE_MOVED
        // The order of the events is implementation specific.
        $events = $this->expectEventsInAnyOrder($journal,
            array(
                array(EventInterface::NODE_REMOVED, $this->nodePath . '/child'),
                array(EventInterface::NODE_ADDED, $this->nodePath . '/moved'),
                array(EventInterface::NODE_MOVED, $this->nodePath . '/moved'),
            )
        );
        foreach($events as $event) {
            if (EventInterface::NODE_MOVED === $event->getType()) {
                $type = $event->getPrimaryNodeType();
                $this->assertInstanceOf('PHPCR\NodeType\NodeTypeInterface', $type);
                $this->assertEquals('nt:unstructured', $type->getName());
            }
        }


        $this->assertEvent(EventInterface::PERSIST, '', $journal->current());

        $journal->next();
        $this->assertEvent(EventInterface::NODE_REMOVED, $this->nodePath . '/moved', $journal->current());

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
    protected function varDumpJournal(EventJournalInterface $journal)
    {
        echo "JOURNAL DUMP:\n";
        while ($journal->valid()) {
            $event = $journal->current();
            echo sprintf("%s - %s - %s\n", $event->getDate(), $event->getType(), $event->getPath())   ;
            $journal->next();
        }
    }
}
