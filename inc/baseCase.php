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
    protected $configKeys = array('jcr.url', 'jcr.user', 'jcr.pass', 'jcr.workspace', 'jcr.transport');
    protected $sharedFixture = array();
    protected static $staticSharedFixture = null;

    /** the bootstrap.php from the client can throw PHPCR\RepositoryException
     * with this message to tell assertSession when getJCRSession has been called
     * with parameters not supported by this implementation (like credentials null)
     */
    const NOTSUPPORTEDLOGIN = 'Not supported login';

    public static function setupBeforeClass()
    {
        self::$staticSharedFixture = array();
        $configKeys = array('jcr.url', 'jcr.user', 'jcr.pass', 'jcr.workspace', 'jcr.transport');
        foreach ($configKeys as $cfgKey) {
            self::$staticSharedFixture['config'][substr($cfgKey, 4)] = $GLOBALS[$cfgKey];
        }
        self::$staticSharedFixture['session'] = getJCRSession(self::$staticSharedFixture['config']);
        self::$staticSharedFixture['ie'] = new jackalope_importexport(dirname(__FILE__) . "/../fixture/");
        self::$staticSharedFixture['qm'] = new Jackalope\Query\QueryManager(self::$staticSharedFixture['session']->getObjectManager());
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
    }

    protected function setUp()
    {
        $this->sharedFixture = self::$staticSharedFixture;

        date_default_timezone_set('Europe/Zurich');
        foreach ($this->configKeys as $cfgKey) {
            $this->config[substr($cfgKey, 4)] = $GLOBALS[$cfgKey];
        }

        $this->rootNode = $this->sharedFixture['session']->getNode('/');

        $this->node = null;
        $children = $this->rootNode->getNodes();
        // first node seems to be always jcr:system?
        $child = next($children);
        $this->node = $child->hasNode($this->getName()) ? $child->getNode($this->getName()) : null;
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
    protected function assertTraversableImplemented($obj) {
        $this->assertTrue($obj instanceof \Iterator || $obj instanceof \IteratorAggregate, 'To provide a traversable, you have to either implement Iterator or IteratorAggregate');
    }
}
