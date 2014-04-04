<?php
namespace PHPCR\Tests\Reading;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * functional tests for Jackalope fetch depth
 */
class JackalopeFetchDepthTest extends \PHPCR\Test\BaseCase
{

    public static function setupBeforeClass($fixtures = '05_Reading/jackalopeFetchDepth')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp()
    {
        parent::setUp();
        $this->renewSession();
    }

    public function testGetNodeWithFetchDepth()
    {
        if (!$this->session instanceof \Jackalope\Session) {
            return;
        }

        $node = $this->rootNode->getNode('tests_read_jackalope_fetch_depth');

        $this->session->setSessionOption(\Jackalope\Session::OPTION_FETCH_DEPTH, 5);
        $deepExample = $node->getNode('deepExample');
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());

        $deepExample = $deepExample->getNode('deepExample');
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());

        $deepExample = $deepExample->getNode('deepExample');
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());

        $deepExample = $deepExample->getNode('deepExample');
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());
   }

    public function testGetNodesWithFetchDepth()
    {
        if (!$this->session instanceof \Jackalope\Session) {
            return;
        }

        $node = $this->rootNode->getNode('tests_read_jackalope_fetch_depth');

        $this->session->setSessionOption(\Jackalope\Session::OPTION_FETCH_DEPTH, 5);
        $deepExamples = $node->getNodes('deepExample');
        $deepExample = $deepExamples->current();
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());

        $deepExamples = $node->getNodes('deepExample');
        $deepExample = $deepExamples->current();
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());

        $deepExamples = $node->getNodes('deepExample');
        $deepExample = $deepExamples->current();
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());

        $deepExamples = $node->getNodes('deepExample');
        $deepExample = $deepExamples->current();
        $this->assertEquals(array('deepExample'), (array) $deepExample->getNodeNames());
    }
}
