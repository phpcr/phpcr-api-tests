<?php

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/**
 * Covering jcr-283 spec $10.7
 */
class jackalope_tests_write_ManipulationTest_CopyMethods extends jackalope_baseCase {


    /**
     * @covers jackalope_Workspace::copy
     */
    public function testWorkspaceCopy() {
        $session = $this->sharedFixture['session'];
        $workspace = $session->getWorkspace();
        $src = '/tests_write_manipulation_base/multiValueProperty';
        $dst = '/tests_write_manipulation_base/emptyExample';

        $workspace->copy($src, $dst);

        // session was updated as well
        $this->assertTrue(!is_null($session->getNode($dst.'/'.basename($src))));

        // TODO workspace-write method, also verify that the move was dispatched to the backend
    }


}



