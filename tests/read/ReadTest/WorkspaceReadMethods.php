<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/** test javax.jcr.Workspace read methods (read)
 *  most of the pdf specification is in chapter 4.5
 *
 *  Locking: getLockManager
 *  Versioning: getVersionManager
 *  level2: WorkspaceWriteMethods: clone, copy, createWorkspace, deleteWorkspace, getImportContentHandler, importXML, move
 */
class jackalope_tests_read_ReadTest_WorkspaceReadMethods extends jackalope_baseCase {
    protected $path = 'read/read';
    protected $workspace;

    static public function  setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('read/read/base.xml');
        self::$staticSharedFixture['session'] = getJCRSession(self::$staticSharedFixture['config']);
    }

    //4.5 Workspace Read Methods

    function setUp() {
        parent::setUp();
        $this->workspace = $this->sharedFixture['session']->getWorkspace();
    }

    //4.5.2
    public function testGetSession() {
        $this->assertEquals($this->sharedFixture['session'], $this->workspace->getSession());
    }

    //4.5.3
    public function testGetName() {
        $this->assertEquals($this->sharedFixture['config']['workspace'], $this->workspace->getName());
    }

    public function testGetQueryManager() {
        $qm = $this->workspace->getQueryManager();
        $this->assertTrue(is_object($qm));
        $this->assertTrue($qm instanceOf PHPCR_Query_QueryManagerInterface);
    }

    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetQueryManagerRepositoryException() {
        $this->markTestIncomplete('TODO: Figure how to produce this exception.');
    }

    public function testGetNamespaceRegistry() {
        $nr = $this->workspace->getNamespaceRegistry();
        $this->assertTrue(is_object($nr));
        $this->assertTrue($nr instanceOf PHPCR_NamespaceRegistryInterface);
    }

    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetNamespaceRegistryRepositoryException() {
        $this->markTestIncomplete('TODO: Figure how to produce this exception.');
    }

    public function testGetNodeTypeManager() {
        $ntm = $this->workspace->getNodeTypeManager();
        $this->assertTrue(is_object($ntm));
        $this->assertTrue($ntm instanceOf PHPCR_NodeType_NodeTypeManagerInterface);
    }

    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetNodeTypeManagerRepositoryException() {
        $this->markTestIncomplete('TODO: Figure how to produce this exception.');
    }

    //4.5.4
    public function testGetAccessibleWorkspaceNames() {
        $names = $this->workspace->getAccessibleWorkspaceNames();
        $this->assertTrue(is_array($names));
        $this->assertContains($this->sharedFixture['config']['workspace'], $names);
    }

    /**
     * @expectedException PHPCR_RepositoryException
     */
    public function testGetAccessibleWorkspaceNamesRepositoryException() {
        $this->markTestIncomplete('TODO: Figure how to produce this exception.');
    }
}
