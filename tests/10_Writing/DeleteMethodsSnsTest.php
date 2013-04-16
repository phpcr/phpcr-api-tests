<?php
namespace PHPCR\Tests\Writing;

require_once(__DIR__ . '/../../inc/BaseCase.php');

/**
 * Test for deleting same name siblings (SNS).
 *
 * The fixtures needed for this test can only be loaded for transports that
 * support SNS.
 *
 * At this point, we are only testing for the ability to delete existing SNS;
 * creating or manipulating them is not supported.
 */
class DeleteMethodsSnsTest extends \PHPCR\Test\BaseCase
{
    static public function setupBeforeClass($fixtures = '10_Writing/delete')
    {
        parent::setupBeforeClass(null);

        if (! self::includeSameNameSiblings()) {
            self::markTestSkipped();
        }

        self::$staticSharedFixture['ie']->import('10_Writing/deletesns');
    }

    public function setUp()
    {
        $this->renewSession(); // get rid of cache from previous tests
        parent::setUp();
    }

    /**
     * Call session->removeItem() with multiple items before session->save()
     */
    public function testRemoveItemMultiple()
    {
$this->markTestSkipped();
        $parentPath = '/tests_write_manipulation_delete_sns/testRemoveSnsBySession';
        $session = $this->sharedFixture['session'];
        $childNames = array('child', 'child[2]', 'child[3]');

        $parent = $this->getParentNode($session, $parentPath);

        foreach ($childNames as $childName) {
            $this->assertTrue($parent->hasNode($childName));
            $session->removeItem($parentPath . '/' . $childName);
            $this->assertFalse($parent->hasNode($childName), 'Node was not removed');
        }

        $this->saveAndRenewSession();

        foreach ($childNames as $childName) {
            $this->assertFalse($this->sharedFixture['session']->nodeExists($parentPath . '/' . $childName));
        }
    }

    /**
     * Call node->remove() with multiple items before session->save()
     *
     * \PHPCR\ItemInterface::remove
     */
    public function testRemoveNode()
    {
$this->markTestSkipped();
        $parentPath = '/tests_write_manipulation_delete_sns/testRemoveSnsByNode';
        $session = $this->sharedFixture['session'];
        $childNames = array('child', 'child[2]', 'child[3]');

        $parent = $this->getParentNode($session, $parentPath);

        foreach ($childNames as $childName) {
            $this->assertTrue($parent->hasNode($childName));
            $child = $parent->getNode($childName);
            $this->assertInstanceOf('PHPCR\NodeInterface', $child);
            $child->remove();
            $this->assertFalse($parent->hasNode($childName), 'Node was not removed');
        }

        $this->saveAndRenewSession();

        foreach ($childNames as $childName) {
            $this->assertFalse($this->sharedFixture['session']->nodeExists($parentPath . '/' . $childName));
        }
    }

    /**
     * Delete 6 nodes from a set of 12, and check that the correct ones remain
     * (using a property to track individual nodes as they get renamed).
     */
    public function testDeleteManyNodes()
    {
        $parentPath = '/tests_write_manipulation_delete_sns/testRemoveManyNodes';
        $session = $this->sharedFixture['session'];
        $childrenAtStart = array(
            'child'     => '1',
            'child[2]'  => '2',
            'child[3]'  => '3',
            'child[4]'  => '4',
            'child[5]'  => '5',
            'child[6]'  => '6',
            'child[7]'  => '7',
            'child[8]'  => '8',
            'child[9]'  => '9',
            'child[10]' => '10',
            'child[11]' => '11',
            'child[12]' => '12',
        );
        $childrenToDelete = array(
            'child',
            'child[2]',
            'child[3]',
            'child[6]',
            'child[10]',
            'child[11]',
        );
        $childrenAtEnd = array(
            'child'     => '4',
            'child[2]'  => '5',
            'child[3]'  => '7',
            'child[4]'  => '8',
            'child[5]'  => '9',
            'child[6]'  => '12',
        );

        $parent = $this->getParentNode($session, $parentPath);

        foreach ($childrenAtStart as $childName => $childNumber) {
            $this->assertTrue($parent->hasNode($childName), "Child $childNumber not found.");
        }

        foreach ($childrenToDelete as $childName) {
            $session->removeItem($parentPath . '/' . $childName);
            $this->assertFalse($parent->hasNode($childName), 'Node was not removed');
        }

        $this->saveAndRenewSession();

        $parent = $this->sharedFixture['session']->getNode($parentPath);
        $this->assertEquals(count($childrenAtEnd), count($parent->getNodes()));

        foreach ($parent->getNodes() as $node) {
            $child = each($childrenAtEnd);
            $this->assertEquals($parentPath . '/' . $child['key'], $node->getPath());
            $this->assertEquals($child['value'], $node->getProperty('childNumber')->getValue());
        }
    }

    /**
     * Determine if tests for deleting same name siblings should be included
     *
     * @return bool
     */
    private static function includeSameNameSiblings()
    {
        // Special case; we should really use getRepository()->getDescriptor()
        // but in this case we don't support creating same name siblings,
        // but need to be able to delete them.
        $session = self::$staticSharedFixture['session'];
        return 'Jackalope\Transport\Jackrabbit\Client' == get_class($session->getTransport());
    }

    /**
     * @param $session
     * @param $parentPath
     * @return mixed
     */
    private function getParentNode($session, $parentPath)
    {
        $parent = $session->getNode($parentPath);
        $this->assertInstanceOf('PHPCR\NodeInterface', $parent);
        return $parent;
    }
}
