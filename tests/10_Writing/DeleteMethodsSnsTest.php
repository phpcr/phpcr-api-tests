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
        $parentPath = '/tests_write_manipulation_delete_sns/testRemoveSnsBySession';
        $session = $this->sharedFixture['session'];
        $childNames = array('child', 'child[2]', 'child[3]');

        $parent = $session->getNode($parentPath);
        $this->assertInstanceOf('PHPCR\NodeInterface', $parent);

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
        $parentPath = '/tests_write_manipulation_delete_sns/testRemoveSnsByNode';
        $session = $this->sharedFixture['session'];
        $childNames = array('child', 'child[2]', 'child[3]');

        $parent = $session->getNode($parentPath);
        $this->assertInstanceOf('PHPCR\NodeInterface', $parent);

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
}
