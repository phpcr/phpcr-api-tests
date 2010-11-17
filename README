*******************
Jackalope API tests
*******************
http://liip.to/jackalope

ebi at liip.ch
david at liip.ch
chregu at liip.ch

*****
Usage
*****

The jackalope-api-tests is a suite to test compliance for implementations of
the java content repository specification.
https://github.com/jackalope/phpcr

You need to provide a couple of files in order to let the tests detect your
implementation:

* Copy phpunit.xml.sample to phpunit.xml and adapt if necessary.
* Write your own bootstrap file. Have a look at inc/bootstrap.php.sample

Once you are done, run phpunit inside of the root directory. If you use a
standard php installation, this is usually along the lines of
$ phpunit tests/read/ReadTest.php


*****
Setup
*****

Setting up submodules
*********************

After the first clone, don't forget to

git submodule init
git submodule update

Dependencies
************

PHPUnit in PATH
PHPUnit in include_path

Setting up Jackrabbit
*********************
- Create tests workspace which is different from your default workspace.
See http://jackrabbit.apache.org/jackrabbit-configuration.html#JackrabbitConfiguration-Workspaceconfiguration
Or:
- Go to the directory you started jackrabbit-standalone (eg. /opt/svn/jackrabbit/jackrabbit-standalone/target)
- cd jackrabbit
- cd workspaces
- cp -r default tests
- cd tests
- change <Workspace name="default"> to <Workspace name="tests">
- start jackrabbit again



**************
Implementation
**************

This code defines a set of unit tests against the PHPCR interfaces.
The tests are oriented at the javadoc of the JSR 283 interfaces.

tests/read contains tests for the base read functionality.
tests/write contains tests for the base write functionality.


****
TODO
****

At the moment, the API tests assume that the storage backend is Jackrabbit.
This should be refactored out of the test suite and replaced by a method in
bootstrap.php to load fixtures into the backend.


This test classes are not completely covering the specification.
It would be nice if we were able to run the JSR-283 Technology Compliance
Kit (TCK) against php implementations.
https://jira.liip.ch/browse/JACK-24

Once we manage to do that, we could hopefully also use the performance test suite
https://jira.liip.ch/browse/JACK-23
