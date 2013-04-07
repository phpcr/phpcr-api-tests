# PHPCR API tests

Test suite to test implementations against the PHPCR API interfaces.

The tests are organised by feature, with the numbers referencing the chapter
numbers in the JCR v2.0 specification, JSR 283.
(available at http://www.day.com/specs/jcr/2.0/index.html)


Some of the chapters have not yet been implemented. They have a file named ```TODO```
in the folder. The tests in all chapters are probably not feature complete, but
steadily increasing. Help is of course welcome.

## Usage

The phpcr-api-tests is a suite to test compliance for implementations of
the java content repository specification.
[https://github.com/phpcr/phpcr](https://github.com/phpcr/phpcr)

This test suite is independent of an actual implementation. You need to do a
couple of things to provide proper bootstrapping for your implementation:

* Add the api tests as submodule of your project, for example in a folder tests/api
* Copy phpunit.dist.xml to ../phpunit.xml and adapt as necessary.
* Implement the bootstrapping (see below)

### Bootstrapping

You find a sample bootstrap.dist.php that you can copy to ../bootstrap.php and
adjust to your implementation. Your bootstrap must ensure that the
```ImplementationLoader``` class extending the \PHPCR\Test\AbstractLoader
is available in the environment.
The ImplementationLoader is used by the \PHPCR\Test\BaseCase to acquire the
PHPCR instances.

See the inc/AbstractLoader.php file to see what the ImplementationLoader has to do.

You can pass parameters from phpunit.xml into your bootstrap with the <php><var... syntax.

### Fixtures loading

The ImplementationLoader must provide a fixture loader. The fixture names are
generic, but all names map to .xml files in the fixtures folder. Those files
are in the JCR system view format. This is the format that is used with
SessionInterface::import and produced by SessionInterface::exportSystemView

If your implementation can not import that format, you will need to convert
them into a suitable format.

We recommend not to use your phpcr implementation import() implementation to
load the fixtures but something stripped down.
If you use the import() implementation but have some error or bug in that
implementation, you will get confusing errors on tests that should not fail.


## Running the tests

Once this binding is working, run the tests with phpunit. If you use a normal
php installation, this is usually along the lines of:

    $ phpunit -c path/to/folder-with/phpunit.xml

You can run the tests for a specific chapter of the specification or just a
single test case by specifying a path

    $ phpunit -c path/to/folder-with/phpunit.xml path/to/suite/tests/NN_chaptername


## Dependencies

* PHPUnit in PATH
* PHPUnit in include_path


## Implementation notes

This test suite is made to work with all implementations of PHPCR. Never write
tests for implementation specific things in here - use your applications unit
or functional tests for that.

All tests must extend from the PHPCR\Test\BaseCase which provides the static
member $loader with the ImplementationLoader to load data.
The BaseCase offers infrastructure to load fixtures efficiently and has some
additional assertions that can be used by the tests.
Read the comments in inc/BaseCase.php for details.

To improve test running speed, fixture are loaded in the setupBeforeClass
method. If you need specific fixtures, pass the name to the parent class
setupBeforeClass method, if you do not need fixtures at all, pass false.

For the read-only tests, we have just two fixtures that cover all cases:
general/base and general/query

To only load fixtures once but reliably test write operations, have a fixture
per test case class with nodes named after the test names. The BaseCase will
put that name into $this->node for each test in setUp(). This way, each test
has its own "root" node and does not influence the other tests.

To add or adjust fixtures, please keep them in the PHPCR system view format.
If you work on Jackalope, you can use jackrabbit and the jack.jar tool to
import and export data:
http://github.com/jackalope/jackrabbit-importexport
See the readme file of jackrabbit-importexport for details.


# Contributors

* Christian Stocker <chregu@liip.ch>
* David Buchmann <david@liip.ch>
* Tobias Ebnöther <ebi@liip.ch>
* Roland Schilter <roland.schilter@liip.ch>
* Uwe Jäger <uwej711@googlemail.com>
* Johannes Stark <starkj@gmx.de>
* Lukas Kahwe Smith <lukas@liip.ch>
* Benjamin Eberlei <kontakt@beberlei.de>
* Daniel Barsotti <daniel.barsotti@liip.ch>
