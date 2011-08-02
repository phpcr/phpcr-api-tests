<?php
/** make sure we get ALL infos from php */
error_reporting(E_ALL | E_STRICT);

/**
 * Sample bootstrap file
 *
 * the thing you MUST do is define the constants as expected in the
 * 04_Connecting/RepositoryDescriptorsTest.php
 *
 * Otherwise you may use this file to register autoloaders or require files
 * to have your implementation be ready.
 *
 * Have a look at the Jackalope repository for an example how the tests are
 * integrated https://github.com/jackalope/jackalope
 */

/*
 * you need to define the following constants for the repository descriptor test for JCR 1.0/JSR-170 and JSR-283 specs
 */

define('SPEC_VERSION_DESC', 'jcr.specification.version');
define('SPEC_NAME_DESC', 'jcr.specification.name');
define('REP_VENDOR_DESC', 'jcr.repository.vendor');
define('REP_VENDOR_URL_DESC', 'jcr.repository.vendor.url');
define('REP_NAME_DESC', 'jcr.repository.name');
define('REP_VERSION_DESC', 'jcr.repository.version');
define('LEVEL_1_SUPPORTED', 'level.1.supported');
define('LEVEL_2_SUPPORTED', 'level.2.supported');
define('OPTION_TRANSACTIONS_SUPPORTED', 'option.transactions.supported');
define('OPTION_VERSIONING_SUPPORTED', 'option.versioning.supported');
define('OPTION_OBSERVATION_SUPPORTED', 'option.observation.supported');
define('OPTION_LOCKING_SUPPORTED', 'option.locking.supported');
define('OPTION_QUERY_SQL_SUPPORTED', 'option.query.sql.supported');
define('QUERY_XPATH_POS_INDEX', 'query.xpath.pos.index');
define('QUERY_XPATH_DOC_ORDER', 'query.xpath.doc.order');

/*
 * you can do things here like registering your autoloader
 * or require files with classes that are used but not autoloaded
 */
require __DIR__.'/../src/Jackalope/autoloader.php';

### Load two classes needed for jackalope unit tests ###
require __DIR__.'/../tests/Jackalope/TestCase.php';
require __DIR__.'/../tests/Jackalope/Transport/DoctrineDBAL/DoctrineDBALTestCase.php';

### Load the implementation loader class ###
require 'ImplementationLoader.php';
