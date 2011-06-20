<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

use PHPCR\PropertyType;

/**
 * Test removing a previously referenced node
 *
 *    1) Load a referenceable node
 *    2) Create a reference to it
 *    3) Save and renew session
 *    4) Delete the property reference
 *    5) Save and renew session
 *    6) Delete the previously referenced node
 *
 * This should not cause any error
 */
class Writing_10_DeletePreviouslyReferencedNodeTest extends phpcr_suite_baseCase
{
    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('10_Writing/nodetype');
    }

    public function testDeleteNode()
    {
        $error_occured = false;

        try {
            // 1) Load a referenceable node
            $destnode = $this->sharedFixture['session']->getNode('/tests_nodetype_base/idExample');

            // 2) Create a reference to it
            $sourcenode = $this->sharedFixture['session']->getRootNode();
            $sourcenode->setProperty('reference', $destnode, PropertyType::WEAKREFERENCE);

            // 3) Save and renew session (+ re-read the source and dest)
            $this->saveAndRenewSession();
            $destnode = $this->sharedFixture['session']->getNode('/tests_nodetype_base/idExample');
            $sourcenode = $this->sharedFixture['session']->getProperty('/reference');

            // 4) Delete the property reference
            $sourcenode->remove();

            // 5) Save and renew session
            $this->saveAndRenewSession();

            // 6) Delete the previously referenced node
            $destnode->remove();
            $this->saveAndRenewSession();
        } catch (\Exception $ex) {
            $error_occured = true;
        }

        $this->assertFalse($error_occured);
    }
}