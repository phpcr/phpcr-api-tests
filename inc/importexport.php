<?php
/**
 * Basic interface that is to be implemented by Implementations willing to test
 * against the API testsuite.
 */
interface phpcrApiTestSuiteImportExportFixtureInterface
{
    /**
     * Load fixture data into your implementation to prepare for a test.
     *
     * The list of possible fixture names is in the README file.
     * Default fixtures in the jcr system view format live folder fixtures/
     *
     * @param string $fixture the fixtures "name", i.e. "general/base"
     * @return void
     */
    public function import($fixture);
}

/**
 * Handles basic importing and exporting of fixtures trough
 * the java binary jack.jar
 * TODO: move this to jackalope
 *
 * Connection parameters for jackrabbit have to be set in the $GLOBALS array (i.e. in phpunit.xml)
 *     <php>
 *      <var name="phpcr.url" value="http://localhost:8080/server" />
 *      <var name="phpcr.user" value="admin" />
 *      <var name="phpcr.pass" value="admin" />
 *      <var name="phpcr.workspace" value="tests" />
 *      <var name="phpcr.transport" value="davex" />
 *    </php>
 */
class jackrabbit_importexport implements phpcrApiTestSuiteImportExportFixtureInterface
{

    protected $fixturePath;
    protected $jar;

    /**
     * @param string $fixturePath path to the fixtures directory. defaults to dirname(__FILE__) . '/../fixtures/'
     * @param string $jackjar path to the jar file for import-export. defaults to dirname(__FILE__) . '/../bin/jack.jar'
     */
    public function __construct($fixturePath = null, $jackjar = null)
    {
        if (is_null($fixturePath)) {
            $this->fixturePath = dirname(__FILE__) . '/../fixtures/';
        } else {
            $this->fixturePath = $fixturePath;
        }
        if (!is_dir($this->fixturePath)) {
            throw new Exception('Not a valid directory: ' . $this->fixturePath);
        }

        if (is_null($jackjar)) {
            $this->jar = dirname(__FILE__) . '/../bin/jack.jar';
        } else {
            $this->jar = $jackjar;
        }
        if (!file_exists($this->jar)) {
            throw new Exception('jack.jar not found at: ' . $this->jar);
        }
    }

    private function getArguments()
    {
        $args = array(
            'phpcr.url' => 'storage',
            'phpcr.user' => 'username',
            'phpcr.pass' => 'password',
            'phpcr.workspace' => 'workspace',
            'phpcr.transport' => 'transport',
            'phpcr.basepath' => 'repository-base-xpath',
        );
        $opts = "";
        foreach ($args AS $arg => $newArg) {
            if (isset($GLOBALS[$arg])) {
                if ($opts != "") {
                    $opts .= " ";
                }
                $opts .= " " . $newArg . "=" . $GLOBALS[$arg];
            }
        }
        return $opts;
    }

    /**
     * import the jcr dump into jackrabbit
     * @param string $fixture path to the fixture file, relative to fixturePath
     * @throws Exception if anything fails
     */
    public function import($fixture)
    {
        $fixture = $this->fixturePath . $fixture . ".xml";
        if (!is_readable($fixture)) {
            throw new Exception('Fixture not found at: ' . $fixture);
        }

        //TODO fix the stderr redirect which doesn't work properly
        exec('java -jar ' . $this->jar . ' import ' . $fixture . " " . $this->getArguments() . " 2>&1", $output, $ret);
        if ($ret !== 0) {
            $msg = '';
            foreach ($output as $line) {
                $msg .= $line . "\n";
            }
            throw new Exception($msg);
        }
        return true;
    }

    /**
     * export a document view to a file
     *
     * TODO: add path parameter so you can export just content parts (exporting / exports jcr:system too, which is huge and ugly)
     * @param $file path to the file, relative to fixturePath. the file may not yet exist
     * @throws Exception if the file already exists or if the export fails
     */
    public function exportdocument($file)
    {
        $fixture = $this->fixturePath . $file;
        if (is_readable($fixture)) {
            throw new Exception('File existing: ' . $fixture);
        }

        //TODO fix the stderr redirect which doesn't work properly
        exec('java -jar ' . $this->jar . ' exportdocument ' . $fixture . " " . $this->getArguments() . " 2>&1", $output, $ret);
        if ($ret !== 0) {
            $msg = '';
            foreach ($output as $line) {
                $msg .= $line . "\n";
            }
            throw new Exception($msg);
        }
        return true;
    }
}
