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

use Exception;
use PHPCR\CredentialsInterface;
use PHPCR\NoSuchWorkspaceException;
use PHPCR\RepositoryFactoryInterface;
use PHPCR\RepositoryInterface;
use PHPCR\SessionInterface;
use PHPUnit\Framework\SkippedTestSuiteError;

/**
 * Base class for the bootstrapping to load your phpcr implementation for the
 * test suite.
 *
 * See the README file Bootstrapping section for an introduction how this works
 */
abstract class AbstractLoader
{
    /**
     * @var string
     */
    protected $factoryclass;

    /**
     * @var string
     */
    protected $workspacename;

    /**
     * @var string
     */
    protected $otherWorkspacename;

    /**
     * array with chapter names to skip all test cases in (without the numbers).
     */
    protected $unsupportedChapters = [];

    /**
     * array in the format Chapter\FeatureTest with all cases to skip.
     */
    protected $unsupportedCases = [];

    /**
     * array in the format Chapter\FeatureTest::testName with all single tests to skip.
     */
    protected $unsupportedTests = [];

    /**
     * Create the loader.
     *
     * @param string $factoryclass the class name of your implementations
     *      RepositoryFactory. You can pass null but then you must overwrite
     *      the getRepository method.
     * @param string $workspacename the workspace to use for the tests, defaults to 'tests'
     * @param string $otherWorkspacename name of second workspace, defaults to 'testsAdditional'
     *      Needed to test certain operations, such as clone, that span workspaces.
     */
    protected function __construct($factoryclass, $workspacename = 'tests', $otherWorkspacename = 'testsAdditional')
    {
        $this->factoryclass = $factoryclass;
        $this->workspacename = $workspacename;
        $this->otherWorkspacename = $otherWorkspacename;
    }

    /**
     * The loader is a singleton.
     *
     * Implement this class to return an ImplementationLoader instance
     * configured to provide things from your implementation.
     *
     * @return AbstractLoader loader for your implementation
     *
     * @throws Exception
     */
    public static function getInstance()
    {
        throw new Exception('You need to overwrite this method, but php does not allow to declare it abstract.');
    }

    /**
     * @return string classname of the repository factory
     */
    public function getRepositoryFactoryClass()
    {
        return $this->factoryclass;
    }

    /**
     * @return array hashmap with the parameters for the repository factory
     */
    abstract public function getRepositoryFactoryParameters();

    /**
     * You should overwrite this to instantiate the repository without the
     * factory.
     *
     * The default implementation uses the factory, but if the factory has an
     * error, you will get failing tests all over.
     *
     * @return RepositoryInterface the repository instance
     */
    public function getRepository()
    {
        $factoryclass = $this->getRepositoryFactoryClass();
        $factory = new $factoryclass();

        if (!$factory instanceof RepositoryFactoryInterface) {
            throw new Exception("$factoryclass is not of type RepositoryFactoryInterface");
        }

        /* @var $factory RepositoryFactoryInterface */
        return $factory->getRepository($this->getRepositoryFactoryParameters());
    }

    /**
     * @return CredentialsInterface the login credentials that lead to successful login into the repository
     */
    abstract public function getCredentials();

    /**
     * @return CredentialsInterface the login credentials that lead to login failure
     */
    abstract public function getInvalidCredentials();

    /**
     * Used when impersonating another user in Reading\SessionReadMethodsTests::testImpersonate
     * And for Reading\SessionReadMethodsTest::testCheckPermissionAccessControlException.
     *
     * The user may not have write access to /tests_general_base/numberPropertyNode/jcr:content/foo
     *
     * @return CredentialsInterface the login credentials with limited permissions for testing impersonate and access control
     */
    abstract public function getRestrictedCredentials();

    /**
     * @return string the user id that is used in the credentials
     */
    abstract public function getUserId();

    /**
     * Make the repository ready for login with null credentials, handling the
     * case where authentication is passed outside the login method.
     *
     * If the implementation does not support this feature, it must return
     * false for this method, otherwise true.
     *
     * @return bool true if anonymous login is supposed to work
     */
    abstract public function prepareAnonymousLogin();

    /**
     * @return string the workspace name used for the tests
     */
    public function getWorkspaceName()
    {
        return $this->workspacename;
    }

    /**
     * @return string name of the default workspace of this repository
     */
    public function getDefaultWorkspaceName()
    {
        return 'default';
    }

    /**
     * @return string the additional workspace name used for tests that need it
     */
    public function getOtherWorkspaceName()
    {
        return $this->otherWorkspacename;
    }

    /**
     * Get a session for this implementation.
     *
     * @param CredentialsInterface $credentials The credentials to log into the repository. If omitted, self::getCredentials should be used
     *
     * @return SessionInterface the session resulting from logging into the repository with the provided credentials
     */
    public function getSession($credentials = false)
    {
        return $this->getSessionForWorkspace($credentials, $this->getWorkspaceName());
    }

    /**
     * Get a session corresponding to the additional workspace for this implementation.
     *
     * @param CredentialsInterface $credentials The credentials to log into the repository. If omitted, self::getCredentials should be used
     *
     * @return SessionInterface the session resulting from logging into the repository with the provided credentials
     */
    public function getAdditionalSession($credentials = false)
    {
        return $this->getSessionForWorkspace($credentials, $this->getOtherWorkspaceName());
    }

    /**
     * If the implementation can automatically update mix:lastModified nodes,
     * this should return a session configured to do that.
     *
     * Otherwise, the test regarding this feature is skipped.
     *
     * @return SessionInterface
     *
     * @throws SkippedTestSuiteError to make whole test
     *      suite skip if implementation does not support updating the
     *      properties automatically.
     */
    public function getSessionWithLastModified()
    {
        if ($this->doesSessionLastModified()) {
            return $this->getSession();
        }

        throw new SkippedTestSuiteError('Not supported');
    }

    /**
     * The implementation loader should provide a session that does not update
     * mix:lastModified. If that is not possible, this method should return
     * true, which will skip the test about this feature.
     *
     * @return bool
     */
    public function doesSessionLastModified()
    {
        return false;
    }

    /**
     * Decide whether this test can be executed.
     *
     * The default implementation uses the unsupported... arrays to decide.
     * Overwrite if you need a different logic.
     *
     * @param string $chapter the chapter name (folder name without number, i.e. Writing)
     * @param string $case the test case full class name but without PHPCR\Tests , i.e. Writing\CopyMethodsTest
     * @param string $name the test name as returned by TestCase::getName(), i.e. Writing\CopyMethodsTest::testCopyUpdateOnCopy - is null when checking for general support of test case in setupBeforeClass
     *
     * @return bool true if the implementation supports the features of this test
     */
    public function getTestSupported($chapter, $case, $name)
    {
        return !(
            in_array($chapter, $this->unsupportedChapters)
                  || in_array($case, $this->unsupportedCases)
                  || in_array($name, $this->unsupportedTests)
        );
    }

    /**
     * @return FixtureLoaderInterface implementation that is used to load test fixtures
     */
    abstract public function getFixtureLoader();

    /**
     * @param $credentials
     * @param $workspaceName
     *
     * @return mixed
     *
     * @throws Exception
     */
    private function getSessionForWorkspace($credentials, $workspaceName)
    {
        $repository = $this->getRepository();
        if (false === $credentials) {
            $credentials = $this->getCredentials();
        }

        try {
            return $repository->login($credentials, $workspaceName);
        } catch (NoSuchWorkspaceException $e) {
            $adminRepository = $this->getRepository(); // get a new repository to log into
            $session = $adminRepository->login($this->getCredentials(), 'default');
            $workspace = $session->getWorkspace();

            if (in_array($workspaceName, $workspace->getAccessibleWorkspaceNames())) {
                throw new Exception(sprintf('Workspace "%s" already exists but could not login to it', $workspaceName), 0, $e);
            }
            $workspace->createWorkspace($workspaceName);

            $repository = $this->getRepository(); // get a new repository to log into

            return $repository->login($credentials, $workspaceName);
        }
    }
}
