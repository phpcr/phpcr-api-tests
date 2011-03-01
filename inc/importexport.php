<?php

/**
 * Handles basic importing and exporting of fixtures trough
 * the java binary jack.jar
 *
 * Connection parameters for jackrabbit have to be set in the $GLOBALS array (i.e. in phpunit.xml)
 *     <php>
 *      <var name="jcr.url" value="http://localhost:8080/server" />
 *      <var name="jcr.user" value="admin" />
 *      <var name="jcr.pass" value="admin" />
 *      <var name="jcr.workspace" value="tests" />
 *      <var name="jcr.transport" value="davex" />
 *    </php>
 */
class jackalope_importexport
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
            'jcr.url' => 'storage',
            'jcr.user' => 'username',
            'jcr.pass' => 'password',
            'jcr.workspace' => 'workspace',
            'jcr.transport' => 'transport'
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
        $fixture = $this->fixturePath . $fixture;
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
