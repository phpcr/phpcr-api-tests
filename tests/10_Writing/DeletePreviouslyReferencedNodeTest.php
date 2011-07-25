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
    /**
     * @expectedException PHPCR\ReferentialIntegrityException
     */
    public function testDeleteReferencedNode()
    {
        $destnode = $this->sharedFixture['session']->getNode('/tests_general_base/idExample');
        $destnode->remove();
        $this->saveAndRenewSession();
    }

    /**
     * this test is just to see if there is any exception in this workflow.
     */
    public function testDeleteNode()
    {
        // 1) Load a referenced node
        $destnode = $this->sharedFixture['session']->getNode('/tests_general_base/idExample');

        // 2) Get the referencing property and delete it
        $sourceprop = $this->sharedFixture['session']->getProperty('/tests_general_base/numberPropertyNode/jcr:content/ref');
        $sourceprop->remove();
        $sourceprop = $this->sharedFixture['session']->getProperty('/tests_general_base/numberPropertyNode/jcr:content/multiref');
        $sourceprop->remove();

        // 3) Save and renew session
        $this->saveAndRenewSession();

        // 4) Delete the previously referenced node
        $destnode = $this->sharedFixture['session']->getNode('/tests_general_base/idExample');
        $destnode->remove();
        $this->saveAndRenewSession();

        $this->assertFalse($this->sharedFixture['session']->itemExists('/tests_general_base/idExample'));
    }
}