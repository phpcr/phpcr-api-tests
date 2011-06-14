<?php

namespace Jackalope\Tests\QOM;

require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

use Jackalope\Query\QOM\Converter\Sql2Scanner;

/**
 * Test for Jackalope\Query\QomParser
 */
class ScannerTest extends \phpcr_suite_baseCase
{
    protected $sql2;
    protected $tokens;

    public function setUp() {
        $this->sql2 = 'SELECT * FROM [nt:file] INNER JOIN [nt:folder] ON ISSAMENODE(sel1, sel2, [/home])';
        $this->tokens = array(
            'SELECT', '*', 'FROM','[nt:file]', 'INNER', 'JOIN', '[nt:folder]',
            'ON', 'ISSAMENODE', '(', 'sel1', ',', 'sel2', ',', '[/home]', ')');
    }

    public function testConstructor()
    {
        parent::setUp();

        if (! $this->sharedFixture['session']->getWorkspace() instanceof \Jackalope\Workspace) {
            $this->markTestSkipped('This is a test for Jackalope specific functionality');
        }

        $scanner = new Sql2Scanner($this->sql2);
        $this->assertAttributeEquals($this->sql2, 'sql2', $scanner);
        $this->assertAttributeEquals($this->tokens, 'tokens', $scanner);
    }

    public function testLookupAndFetch()
    {
        $scanner = new Sql2Scanner($this->sql2);
        foreach($this->tokens as $token) {
            $this->assertEquals($token, $scanner->lookupNextToken());
            $this->assertEquals($token, $scanner->fetchNextToken());
        }

        $this->assertEquals('', $scanner->lookupNextToken());
        $this->assertEquals('', $scanner->fetchNextToken());
    }
}
