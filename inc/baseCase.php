<?php
require_once dirname(__FILE__).'/importexport.php';

// PHPUnit 3.4 compat
if (method_exists('PHPUnit_Util_Filter', 'addDirectoryToFilter')) {
    require_once 'PHPUnit/Framework.php';
}

abstract class jackalope_baseCase extends PHPUnit_Framework_TestCase
{
    protected $path = ''; // Describes the path to the test

    /** The root node of the fixture, initialized for each test */
    protected $rootNode = null;

    /** The node in the current fixture at /test_class_name/testMethod */
    protected $node = null;

    protected $config;

    /**
     * Populated in the setupBeforeClass method.
     *
     * Contains the fields
     * - session (the PHPCR Session)
     * - ie (the import export instance)
     */
    protected static $staticSharedFixture = null;

    /**
     * Same as staticSharedFixture, loaded in setUp for your convenience
     */
    protected $sharedFixture = array();

    /**
     * the bootstrap.php from the client can throw PHPCR\RepositoryException
     * with this message to tell assertSession when getJCRSession has been called
     * with parameters not supported by this implementation (like credentials null)
     */
    const NOTSUPPORTEDLOGIN = 'Not supported login';

    public static function setupBeforeClass()
    {
        self::$staticSharedFixture = array();
        date_default_timezone_set('Europe/Zurich');
        foreach ($GLOBALS AS $cfgKey => $cfgValue) {
            if (strpos($cfgKey, "jcr.") === 0) {
                self::$staticSharedFixture['config'][substr($cfgKey, 4)] = $cfgValue;
            }
        }
        self::$staticSharedFixture['session'] = getJCRSession(self::$staticSharedFixture['config']);
        self::$staticSharedFixture['ie'] = getFixtureLoader(self::$staticSharedFixture['config']);
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
    }

    public static function tearDownAfterClass()
    {
        if (isset(self::$staticSharedFixture['session'])) {
            self::$staticSharedFixture['session']->logout();
        }
        self::$staticSharedFixture = null;
    }

    protected function renewSession()
    {
        if (isset(self::$staticSharedFixture['session'])) {
            self::$staticSharedFixture['session']->logout();
        }
        self::$staticSharedFixture['session'] = getJCRSession(self::$staticSharedFixture['config']);
        $this->sharedFixture['session'] = self::$staticSharedFixture['session'];
        return $this->sharedFixture['session'];
    }

    /**
     * Saves the session and clears the cache
     * @return \Jackalope\Session   The new session
     */
    protected function saveAndRenewSession()
    {
        $this->sharedFixture['session']->save();
        $this->renewSession();
        return $this->sharedFixture['session'];
    }

    protected function setUp()
    {
        $this->sharedFixture = self::$staticSharedFixture;
        $this->rootNode = $this->sharedFixture['session']->getNode('/');

        /* we create the fixtures in one go
         * the data must all exist under a node /tests_something
         * with one tree per test
         * jackrabbit always puts in a node jcr:system, so we look for nodes under root with the name tests_* only
         */
        $this->node = null;
        $children = $this->rootNode->getNodes("tests_*");
        $child = current($children);
        if (false !== $child) {
            $this->node = $child->hasNode($this->getName()) ? $child->getNode($this->getName()) : null;
        }
    }

    /*************************************************************************
     * Custom assertions
     *************************************************************************/

    /** try to create credentials from this user/password */
    protected function assertSimpleCredentials($user, $password)
    {
        $cr = getSimpleCredentials($user, $password);
        $this->assertType('PHPCR\CredentialsInterface', $cr);
        return $cr;
    }

    /** try to create a session with the config and credentials */
    protected function assertSession($cfg, $credentials = null)
    {
        try {
            $ses = getJCRSession($cfg, $credentials);
        } catch(PHPCR\RepositoryException $e) {
            if ($e->getMessage() == jackalope_baseCase::NOTSUPPORTEDLOGIN) {
                $this->markTestSkipped('This implementation does not support this type of login.');
            } else {
                throw $e;
            }
        }
        $this->assertType('PHPCR\SessionInterface', $ses);
        return $ses;
    }
    /** assert that this is an object that is traversable */
    protected function assertTraversableImplemented($obj) {
        $this->assertTrue($obj instanceof \Iterator || $obj instanceof \IteratorAggregate, 'To provide Traversable, you have to either implement Iterator or IteratorAggregate');
    }
}
