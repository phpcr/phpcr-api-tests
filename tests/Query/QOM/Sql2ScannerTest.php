<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Query\QOM;

use PHPCR\Util\QOM\Sql2Scanner;

/**
 * Test for PHPCR\Util\QOM\Sql2Scanner.
 */
class Sql2ScannerTest extends \PHPCR\Test\BaseCase
{
    protected $sql2;
    protected $tokens;

    public function setUp(): void
    {
        parent::setUp();

        $this->sql2 = '
            SELECT * FROM
                [nt:file]
            INNER JOIN
                [nt:folder] ON ISSAMENODE(sel1, sel2, [/home])';
        $this->tokens = [
            'SELECT', '*', 'FROM','[nt:file]', 'INNER', 'JOIN', '[nt:folder]',
            'ON', 'ISSAMENODE', '(', 'sel1', ',', 'sel2', ',', '[/home]', ')', ];
    }

    public function testConstructor()
    {
        $scanner = new Sql2Scanner($this->sql2);
        $refl = new \ReflectionClass($scanner);
        $sql2Property = $refl->getProperty('sql2');
        $sql2Property->setAccessible(true);
        $this->assertSame($this->sql2, $sql2Property->getValue($scanner));
        $tokensProperty = $refl->getProperty('tokens');
        $tokensProperty->setAccessible(true);
        $this->assertSame($this->tokens, $tokensProperty->getValue($scanner));
    }

    public function testLookupAndFetch()
    {
        $scanner = new Sql2Scanner($this->sql2);
        foreach ($this->tokens as $token) {
            $this->assertEquals($token, $scanner->lookupNextToken());
            $this->assertEquals($token, $scanner->fetchNextToken());
        }

        $this->assertEquals('', $scanner->lookupNextToken());
        $this->assertEquals('', $scanner->fetchNextToken());
    }
}
