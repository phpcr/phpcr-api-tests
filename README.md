# PHPCR API tests

Test suite to test implemenations against the PHPCR API interfaces.

The tests are organised by feature, with the numbers referencing the chapter
numbers in the JCR v2.0 specification, JSR 283.
(available at http://www.day.com/specs/jcr/2.0/index.html)


Some of the chapters have not yet been implemented. They have a file named TODO
in the folder.
TODO: check existing chapters for completeness and correctness
Some tests are missing, some are skipped although jackalope implements the feature.
Write operations are less tested than read operations. Should go through all tests and fix failing ones and implement missing ones.
For write, check Session::save too and more complicated chained operations.
I.e.:
 * move a not yet loaded node, then load it with the old path -> fail. with new path -> get it
 * same with moving child nodes not yet loaded and calling Node::getChildren. and loaded as well.
 * Test if order of write operations to backend is correct in larger batches. if i have
 * /some/path/parent/node and set the path of "parent" to /some/other/path/parent and in the same session change the path of node to /some/path/parent/something/node, result will depend on the order.
     * if you first move parent, then node, node ends up at the expected path.
     * if you first move node, then parent, node will end up in /some/other/path/parent/something/node, because a node is moved with all its children.


TODO: tests should check workspace if it supports that feature and mark tests
skipped if implemenation does not claim to implement this feature.

TODO: Although generically useable this checkout comes with fixtures and data for the Jackalope API tests.
clean out the jackalope references and move all jackalope specific stuff (the .jars and so on)
into the jackalope api-tests folder. These tests should be clean. The only relevant folders are
tests and fixtures, plus the .sample files, the rest should go out of this.

TODO: convert all fixtures into system view to have the expected types


## Usage

The phpcr-api-tests is a suite to test compliance for implementations of
the java content repository specification.
[https://github.com/phpcr/phpcr](https://github.com/phpcr/phpcr)

You need to provide a couple of files in order to let the tests detect your
implementation:

* Copy phpunit.xml.sample to phpunit.xml and adapt if necessary. All <php /> vars beginning
  with "jcr." are copied to the configuration array that is passed to the functions `getJCRSession`,
  `getRepository` and `getFixtureLoader`.
* Write your own bootstrap file. Have a look at inc/bootstrap.php.sample. You
  have to implement the following methods:
    ** getJCRSession()
    ** getRepository()
    ** getSimpleCredentials()
    ** getFixtureLoader()
* Implement data for all the necessary fixtures. See the "fixtures/" folder for
  a JCR XML system view export of the fixture data. If your implementation can
  not import this format, you will need to convert them into a suitable format.

Once this binding is working, run the tests with phpunit. If you use a normal
php installation, this is usually along the lines of:

    $ phpunit -c path/to/folder-with-phpunit

You can run the tests for a specific chapter of the specification with
phpunit -c path/to/folder-with-phpunit path/to/NN_chaptername


### Required Fixtures

The fixture loading passes strings without the file extension so you can roll
your own fixtures if you want.
The test suite provides default fixtures in the fixtures folder in the JCR
system view export format. It is recommended to use those.

Current list of fixtures (subject to be extended):

* general/base
* general/query
* 10_Writing/add
* 10_Writing/copy
* 10_Writing/delete
* 10_Writing/move
* 10_Writing/nodetype
* 15_Versioning/base



### Using jackrabbit_importexport for load your own fixtures

TODO: move this into jackalope, its implementation specific. and we should just
implement the session::importXML method anyways

The class jackrabbit_importexport can be used to import fixtures in xml format.
It relies on jack.jar. The class can be plugged in Symfony2 autoload mechanism
through autoload.php, which can be used to feed a MapFileClassLoader istance. E.g:

```php
$phpcr_loader = new MapFileClassLoader(
  __DIR__.'/../vendor/doctrine-phpcr-odm/lib/vendor/jackalope/api-test/suite/inc/autoload.php'
);
$phpcr_loader->register();
```


## Dependencies

* PHPUnit in PATH
* PHPUnit in include_path


## Implementation

All tests extend from the baseCase, found in inc/baseCase.php
The baseCase prepares a couple of fixtures and assertions that can be used by
the tests. Read the comments in that file for details.

To improve test running speed, tests should load the fixtures in the
setupBeforeClass method.
For the read-only tests, we have just two fixture files that cover all cases.
For the write tests, we have one fixture per file with nodes named after the
test names, which baseCase::setUp puts into $this->node. This way, each test
has its own "root" node and does not influence the other tests.


## Note on JCR

It would be nice if we were able to run the relevant parts of the JSR-283
Technology Compliance Kit (TCK) against php implementations. Note that we would
need to have some glue for things that look different in php than in Java, like
the whole topic of Value and ValueFactory.
[https://jira.liip.ch/browse/JACK-24](https://jira.liip.ch/browse/JACK-24)

Once we manage to do that, we could hopefully also use the performance test suite
[https://jira.liip.ch/browse/JACK-23](https://jira.liip.ch/browse/JACK-23)


# Contributors

* Christian Stocker <chregu@liip.ch>
* David Buchmann <david@liip.ch>
* Tobias Ebnöther <ebi@liip.ch>
* Roland Schilter <roland.schilter@liip.ch>
* Uwe Jäger <uwej711@googlemail.com>
* Lukas Kahwe Smith <lukas@liip.ch>
* Benjamin Eberlei <kontakt@beberlei.de>
* Daniel Barsotti <daniel.barsotti@liip.ch>
