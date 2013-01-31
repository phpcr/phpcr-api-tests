<?php
namespace PHPCR\Test;

// PHPUnit 3.4 compat
if (method_exists('PHPUnit_Util_Filter', 'addDirectoryToFilter')) {
    require_once 'PHPUnit/Framework.php';
}

/**
 * Base class for all phpcr api tests
 */
abstract class BaseCase extends \PHPUnit_Framework_TestCase
{
    protected $path = ''; // Describes the path to the test

    /**
     * The root node of the fixture, initialized for each test
     *
     * @var \PHPCR\NodeInterface
     */
    protected $rootNode = null;

    /**
     * The node in the current fixture at /test_class_name/testMethod
     *
     * @var \PHPCR\NodeInterface
     */
    protected $node = null;

    /**
     * Instance of the implementation specific loader
     *
     * The BaseCase offers some utility methods, but tests can access the
     * loader directly to get implementation instances.
     *
     * @var AbstractLoader
     */
    protected static $loader;

    /**
     * Populated in the setupBeforeClass method.
     *
     * Contains the fields
     * - session (the PHPCR Session)
     * - ie (the fixture loader instance)
     *
     * @var array
     */
    protected static $staticSharedFixture = null;

    /**
     * Same as staticSharedFixture, loaded in setUp for your convenience
     */
    protected $sharedFixture = array();

    /**
     * the loader can throw a PHPCR\RepositoryException
     * with this message to tell assertSession when getSession has been called
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
        self::$loader = \ImplementationLoader::getInstance();

        $fqn = get_called_class();
        list($phpcr, $tests, $chapter, $case) = explode('\\', $fqn);
        $case = "$chapter\\$case";
        if (! self::$loader->getTestSupported($chapter, $case, null)) {
            throw new \PHPUnit_Framework_SkippedTestSuiteError('Test case not supported by this implementation');
        }

        self::$staticSharedFixture = array();
        date_default_timezone_set('Europe/Zurich'); //TODO put this here?

        self::$staticSharedFixture['ie'] = self::$loader->getFixtureLoader();
        if ($fixtures) {
            self::$staticSharedFixture['ie']->import($fixtures);
        }

        // only load session once fixtures have been imported (relevant i.e. for jackalope-doctrine-dbal)
        self::$staticSharedFixture['session'] = self::$loader->getSession();
    }

    protected function setUp()
    {
        $fqn = get_called_class();
        $parts = explode('\\', $fqn);
        $case_n = count($parts)-1;
        $case = $parts[$case_n];
        $chapter = '';

        for($i = 2; $i < $case_n; $i++) {
            $chapter .= $parts[$i] . '\\';
        }

        $case = $chapter . $case;
        $test = "$case::".$this->getName();

        if (! self::$loader->getTestSupported($chapter, $case, $test)) {
            $this->markTestSkipped('Feature not supported by this implementation');
        }

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

    /**
     * Utility method for tests to get a new session
     *
     * Logout from the old session but does *NOT* save the session
     *
     * @return \PHPCR\SessionInterface   The new session
     */
    protected function renewSession()
    {
        if (isset(self::$staticSharedFixture['session'])) {
            self::$staticSharedFixture['session']->logout();
        }
        self::$staticSharedFixture['session'] = self::$loader->getSession();
        $this->sharedFixture['session'] = self::$staticSharedFixture['session'];

        $this->initProperties();

        return $this->sharedFixture['session'];
    }

    /**
     * Utility method for tests to save the session and get a new one
     *
     * Saves the old session and logs it out.
     *
     * @return \PHPCR\SessionInterface   The new session
     */
    protected function saveAndRenewSession()
    {
        $this->sharedFixture['session']->save();
        $this->renewSession();
        return $this->sharedFixture['session'];
    }

    /**
     * This method populates the test case properties both at test setUp
     * and after renewing the session.
     *
     * The default schema is to have a root node /test_<something> with one
     * child node per test with the node name being the test name.
     */
    protected function initProperties()
    {
        $this->node = null;

        $this->rootNode = $this->sharedFixture['session']->getRootNode();

        $children = $this->rootNode->getNodes("tests_*");
        $child = current($children);
        if (false !== $child && $child->hasNode($this->getName())) {
            $this->node = $child->getNode($this->getName());
        }
    }

    /*************************************************************************
     * Custom assertions
     *************************************************************************/

    /**
     * create a session with the given credentials and assert this is a session
     *
     * this is similar to doing self::$loader->getSession($credentials) but
     * does error handling and asserts the session is a valid SessionInterface
     *
     * @return \PHPCR\SessionInterface the session from the login
     */
    protected function assertSession($credentials = false)
    {
        try {
            $ses = self::$loader->getSession($credentials);
        } catch(\PHPCR\RepositoryException $e) {
            if ($e->getMessage() == self::NOTSUPPORTEDLOGIN) {
                $this->markTestSkipped('This implementation does not support this type of login.');
            } else {
                throw $e;
            }
        }
        $this->assertInstanceOf('PHPCR\SessionInterface', $ses);
        return $ses;
    }

    /** assert that this is an object that is traversable */
    protected function assertTraversableImplemented($obj)
    {
        $this->assertTrue($obj instanceof \Iterator || $obj instanceof \IteratorAggregate, 'To provide Traversable, you have to either implement Iterator or IteratorAggregate');
    }
}
