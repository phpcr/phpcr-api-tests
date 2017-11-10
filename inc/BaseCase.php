<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Test;

use ImplementationLoader;
use Iterator;
use IteratorAggregate;
use PHPCR\RepositoryException;
use PHPCR\SessionInterface;
use PHPCR\NodeInterface;
use DateTime;
use PHPUnit_Framework_SkippedTestSuiteError;
use PHPUnit\Framework\TestCase;

/**
 * Base class for all phpcr api tests.
 */
abstract class BaseCase extends TestCase
{
    /**
     * Describes the path to the node for this test, used with writing tests.
     *
     * @var string
     */
    protected $path = '';

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * The root node of the fixture, initialized for each test.
     *
     * @var NodeInterface
     */
    protected $rootNode = null;

    /**
     * The node in the current fixture at /test_class_name/testMethod.
     *
     * @var NodeInterface
     */
    protected $node = null;

    /**
     * Instance of the implementation specific loader.
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
     * Same as staticSharedFixture, loaded in setUp for your convenience.
     */
    protected $sharedFixture = [];

    /**
     * the loader can throw a PHPCR\RepositoryException
     * with this message to tell assertSession when getSession has been called
     * with parameters not supported by this implementation (like credentials null).
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
        self::$loader = ImplementationLoader::getInstance();

        $fqn = get_called_class();
        list($phpcr, $tests, $chapter, $case) = explode('\\', $fqn);
        $case = "$chapter\\$case";
        if (!self::$loader->getTestSupported($chapter, $case, null)) {
            throw new PHPUnit_Framework_SkippedTestSuiteError('Test case not supported by this implementation');
        }

        self::$staticSharedFixture = [];
        date_default_timezone_set('Europe/Zurich'); //TODO put this here?

        self::$staticSharedFixture['ie'] = self::$loader->getFixtureLoader();
        if ($fixtures) {
            self::$staticSharedFixture['ie']->import($fixtures);
        }

        // only load sessions once fixtures have been imported (relevant i.e. for jackalope-doctrine-dbal)
        self::$staticSharedFixture['session'] = self::$loader->getSession();
        self::$staticSharedFixture['additionalSession'] = self::$loader->getAdditionalSession();
    }

    protected function setUp()
    {
        $fqn = get_called_class();
        $parts = explode('\\', $fqn);
        $case_n = count($parts) - 1;
        $case = $parts[$case_n];
        $chapter = '';

        for ($i = 2; $i < $case_n; $i++) {
            $chapter .= $parts[$i].'\\';
        }

        $case = $chapter.$case;
        $test = "$case::".$this->getName();

        if (!self::$loader->getTestSupported($chapter, $case, $test)) {
            $this->markTestSkipped('Test '.$this->getName().' not supported by this implementation');
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
     * Utility method for tests to get a new session.
     *
     * Logout from the old session but does *NOT* save the session
     *
     * @return SessionInterface   The new session
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
     * Utility method for tests to save the session and get a new one.
     *
     * Saves the old session and logs it out.
     *
     * @return SessionInterface   The new session
     */
    protected function saveAndRenewSession()
    {
        $this->session->save();
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
        $this->session = $this->sharedFixture['session'];
        $this->node = null;

        $this->rootNode = $this->session->getRootNode();

        $children = $this->rootNode->getNodes('tests_*');
        $child = $children->current();
        if ($child && $child->hasNode($this->getName())) {
            $this->node = $child->getNode($this->getName());
        }
    }

    /*************************************************************************
     * Custom assertions
     *************************************************************************/

    /**
     * create a session with the given credentials and assert this is a session.
     *
     * this is similar to doing self::$loader->getSession($credentials) but
     * does error handling and asserts the session is a valid SessionInterface
     *
     * @return SessionInterface the session from the login
     */
    protected function assertSession($credentials = false)
    {
        try {
            $session = self::$loader->getSession($credentials);
        } catch (RepositoryException $e) {
            if ($e->getMessage() === self::NOTSUPPORTEDLOGIN) {
                $this->markTestSkipped('This implementation does not support this type of login.');
            } else {
                throw $e;
            }
        }

        $this->assertInstanceOf(SessionInterface::class, $session);

        return $session;
    }

    /** assert that this is an object that is traversable */
    protected function assertTraversableImplemented($obj)
    {
        $this->assertTrue($obj instanceof Iterator || $obj instanceof IteratorAggregate, 'To provide Traversable, you have to either implement Iterator or IteratorAggregate');
    }

    /**
     * Check specified property exists, then compare property value to the supplied one using assertEquals.
     *
     * @param NodeInterface $node
     * @param string $property
     * @param mixed $value
     */
    protected function checkNodeProperty(NodeInterface $node, $property, $value)
    {
        $this->assertTrue($node->hasProperty($property));
        $this->assertEquals($value, $node->getPropertyValue($property));
    }

    protected function assertEqualDateString($date1, $date2)
    {
        $this->assertEquals(strtotime($date1), strtotime($date2));
    }

    protected function assertEqualDateTime(DateTime $date1, DateTime $date2)
    {
        $this->assertEquals($date1->getTimestamp(), $date2->getTimestamp());
    }

    /**
     * Assert that both arguments are datetime and are within 3 seconds of each
     * other. Use this rather than plain "Equal" when checking application
     * generated dates.
     *
     * @param DateTime $expected
     * @param DateTime $data
     */
    protected function assertSimilarDateTime($expected, $data)
    {
        $this->assertInstanceOf(DateTime::class, $expected);
        $this->assertInstanceOf(DateTime::class, $data);
        $this->assertTrue(abs($expected->getTimestamp() - $data->getTimestamp()) <= 3,
            $data->format('c').' is not close to the expected '.$expected->format('c')
        );
    }

    /**
     * Check whether the repository supports this descriptor and skip the test if it is not supported.
     *
     * @param string $descriptor
     *
     * @return bool True if the test can be done. Otherwise the test is skipped.
     */
    protected function skipIfNotSupported($descriptor)
    {
        if (false === $this->session->getRepository()->getDescriptor($descriptor)) {
            $this->markTestSkipped('Descriptor "'.$descriptor.'" not supported');
        }

        return true;
    }
}
