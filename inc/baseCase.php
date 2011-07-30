<?php
require_once dirname(__FILE__).'/importexport.php';

// PHPUnit 3.4 compat
if (method_exists('PHPUnit_Util_Filter', 'addDirectoryToFilter')) {
    require_once 'PHPUnit/Framework.php';
}

abstract class phpcr_suite_baseCase extends PHPUnit_Framework_TestCase
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
     * with this message to tell assertSession when getPHPCRSession has been called
     * with parameters not supported by this implementation (like credentials null)
     */
    const NOTSUPPORTEDLOGIN = 'Not supported login';

    /**
     * we use this place to fetch a session and possibly load fixtures.
     *
     * this speeds up the tests considerably as fixture loading can be
     * quite expensive
     *
     * @param string $fixtures the fixtures name to import, defaults to
     *      general/base. if you want to load fixtures yourself, send false
     *
     * @see initProperties()
     */
    public static function setupBeforeClass($fixtures = 'general/base')
    {
        self::$staticSharedFixture = array();
        date_default_timezone_set('Europe/Zurich');
        foreach ($GLOBALS as $cfgKey => $value) {
            if ('phpcr.' === substr($cfgKey, 0, 6)) {
                self::$staticSharedFixture['config'][substr($cfgKey, 6)] = $value;
            }
        }

        self::$staticSharedFixture['session'] = getPHPCRSession(self::$staticSharedFixture['config']);
        self::$staticSharedFixture['ie'] = getFixtureLoader(self::$staticSharedFixture['config']);

        if ($fixtures) {
            self::$staticSharedFixture['ie']->import($fixtures);
        }
    }

    protected function setUp()
    {
        $this->sharedFixture = self::$staticSharedFixture;

        $this->initProperties();
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
        self::$staticSharedFixture['session'] = getPHPCRSession(self::$staticSharedFixture['config']);
        $this->sharedFixture['session'] = self::$staticSharedFixture['session'];

        $this->initProperties();

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

    /**
     * You can load the fixtures in the setupBeforeClass() to speed up the
     * tests quite a lot.
     *
     * This method helps to populate test case properties both at test setUp
     * and after renewing the session.
     *
     * The default schema
     * is to have one node per test with the test name under /tests_something
     *
     * You can overwrite this to have some other logic
     */
    protected function initProperties()
    {
        $this->rootNode = $this->sharedFixture['session']->getNode('/');

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
        $this->assertInstanceOf('PHPCR\CredentialsInterface', $cr);
        return $cr;
    }

    /** try to create a session with the config and credentials */
    protected function assertSession($cfg, $credentials = null)
    {
        try {
            $ses = getPHPCRSession($cfg, $credentials);
        } catch(PHPCR\RepositoryException $e) {
            if ($e->getMessage() == phpcr_suite_baseCase::NOTSUPPORTEDLOGIN) {
                $this->markTestSkipped('This implementation does not support this type of login.');
            } else {
                throw $e;
            }
        }
        $this->assertInstanceOf('PHPCR\SessionInterface', $ses);
        return $ses;
    }
    /** assert that this is an object that is traversable */
    protected function assertTraversableImplemented($obj) {
        $this->assertTrue($obj instanceof \Iterator || $obj instanceof \IteratorAggregate, 'To provide Traversable, you have to either implement Iterator or IteratorAggregate');
    }
}
