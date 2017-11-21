<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\PhpcrUtils;

use InvalidArgumentException;
use PHPCR\NodeType\NodeTypeDefinitionInterface;
use PHPCR\NodeType\NodeTypeTemplateInterface;
use PHPCR\PropertyType;
use PHPCR\NodeType\PropertyDefinitionTemplateInterface;
use PHPCR\Test\BaseCase;
use PHPCR\Util\CND\Exception\ParserException;
use PHPCR\Util\CND\Exception\ScannerException;
use PHPCR\Version\OnParentVersionAction;
use PHPCR\Util\CND\Parser\CndParser;

class CndParserTest extends BaseCase
{
    /** @var CndParser */
    private $cndParser;

    public function setUp()
    {
        parent::setUp();
        $this->cndParser = new CndParser($this->session->getWorkspace()->getNodeTypeManager());
    }

    public function testParseNormal()
    {
        $res = $this->cndParser->parseFile(__DIR__.'/resources/cnd/example.cnd');
        $this->assertExampleCnd($res);
    }

    public function testParseCompact()
    {
        $res = $this->cndParser->parseFile(__DIR__.'/resources/cnd/example.compact.cnd');
        $this->assertExampleCnd($res);
    }

    public function testParseVerbose()
    {
        $res = $this->cndParser->parseFile(__DIR__.'/resources/cnd/example.verbose.cnd');
        $this->assertExampleCnd($res);
    }

    public function testParseString()
    {
        // the "worst case" example from http://jackrabbit.apache.org/node-type-notation.html
        $cnd = <<<EOT
/**  An example node type definition */
<ns ='http://namespace.com/ns'>
[ns:NodeType] > ns:ParentType1, ns:ParentType2
  orderable mixin
  - ex:property (STRING)
  = 'default1' , 'default2'
    mandatory autocreated protected multiple
    VERSION
    < 'constraint1', 'constraint2'
  + ns:node (ns:reqType1, ns:reqType2)
    = ns:defaultType
    mandatory autocreated protected VERSION
EOT;

        $res = $this->cndParser->parseString($cnd);
        $this->assertExampleCnd($res);
    }

    /**
     * Have invalid-string in the middle of options for a property.
     */
    public function testParseError()
    {
        $this->expectException(ParserException::class);

        $cnd = <<<EOT
/**  An example node type definition */
<ns ='http://namespace.com/ns'>
[ns:NodeType] > ns:ParentType1, ns:ParentType2
  orderable mixin
  - ex:property (STRING)
  = 'default1' , 'default2'
    mandatory invalid-string protected multiple
    VERSION
    < 'constraint1', 'constraint2'
  + ns:node (ns:reqType1, ns:reqType2)
    = ns:defaultType
    mandatory autocreated protected VERSION
EOT;

        $this->cndParser->parseString($cnd);
    }

    public function testErrorNoFile()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cndParser->parseFile('/not/found');
    }

    /**
     * Have a comment that is never closed. Starting that at the end of the
     * file turned out to be particularly nasty.
     */
    public function testScannerErrorComment()
    {
        /**
         * Remove this PHP7 only condition after PHPCR-Utils #168
         * (https://github.com/phpcr/phpcr-utils/issues/168) is closed
         */
        if (PHP_MAJOR_VERSION >= 7) {
            $this->markTestSkipped();
        }

        $this->expectException(ScannerException::class);

        $cnd = <<<EOT
la /*
EOT;

        $this->cndParser->parseString($cnd);
    }

    /**
     * Have a newline in a name (here the ns declaration).
     */
    public function testScannerErrorNewline()
    {
        $this->expectException(ScannerException::class);

        $cnd = <<<EOT
/**  An example node type definition */
<ns ='http://namespace.com/ns
'>
[ns:NodeType] > ns:ParentType1, ns:ParentType2
  orderable mixin
  - ex:property (STRING)
EOT;

        $this->cndParser->parseString($cnd);
    }

    /**
     * Test the case where the parser did not parse correctly
     * the default values at the end of the parsed file.
     *
     * Assert no exception is thrown
     */
    public function testNoStopAtEofError()
    {
        $res = $this->cndParser->parseFile(__DIR__.'/resources/cnd/no-stop-at-eof.cnd');

        $this->assertTrue(isset($res['namespaces']));
        $this->assertEquals(['phpcr' => 'http://www.doctrine-project.org/projects/phpcr_odm'], $res['namespaces']);

        $this->assertTrue(isset($res['nodeTypes']));
    }

    public function testBigFile()
    {
        //var_dump($this->session->getWorkspace()->getNodeTypeManager()->getNodeType('nt:file')->hasOrderableChildNodes());die;
        $res = $this->cndParser->parseFile(__DIR__.'/resources/cnd/jackrabbit_nodetypes.cnd');

        // some random sanity checks
        $this->assertTrue(isset($res['nodeTypes']));

        $def = $res['nodeTypes'];
        $this->assertTrue(isset($def['nt:file']));
        /** @var $parsed NodeTypeDefinitionInterface */
        $parsed = $def['nt:file'];
        $this->assertEquals('nt:file', $parsed->getName());
        $this->assertFalse($parsed->isAbstract());
        $this->assertFalse($parsed->hasOrderableChildNodes());
        $this->assertFalse($parsed->isMixin());
        // queryable default is implementation specific
    }

    /**
     * Check if $res matches the expected node type definition from the
     * "worst case" example.
     *
     * @param array $res namespaces and node types
     */
    protected function assertExampleCnd($res)
    {
        $this->assertTrue(isset($res['namespaces']));
        $this->assertEquals(['ns' => 'http://namespace.com/ns'], $res['namespaces']);

        $this->assertTrue(isset($res['nodeTypes']));
        // get first node type
        reset($res['nodeTypes']);
        /** @var $def NodeTypeDefinitionInterface */
        $def = current($res['nodeTypes']);
        $name = key($res['nodeTypes']);

        $this->assertEquals('ns:NodeType', $name);
        $this->assertInstanceOf(NodeTypeTemplateInterface::class, $def);
        $this->assertEquals('ns:NodeType', $def->getName());
        $this->assertEquals(['ns:ParentType1', 'ns:ParentType2'], $def->getDeclaredSuperTypeNames());
        $this->assertTrue($def->hasOrderableChildNodes());
        $this->assertTrue($def->isMixin());
        // queryable default is implementation specific
        $this->assertFalse($def->isAbstract());
        $this->assertCount(1, $def->getPropertyDefinitionTemplates());

        /** @var $prop PropertyDefinitionTemplateInterface */
        $prop = $def->getPropertyDefinitionTemplates()->getIterator()->current();

        $this->assertEquals('ex:property', $prop->getName());
        $this->assertEquals(PropertyType::STRING, $prop->getRequiredType());
        $this->assertEquals(['default1', 'default2'], $prop->getDefaultValues());
        $this->assertEquals(['constraint1', 'constraint2'], $prop->getValueConstraints());
        $this->assertTrue($prop->isAutoCreated());
        $this->assertTrue($prop->isMandatory());
        $this->assertTrue($prop->isProtected());
        $this->assertTrue($prop->isMultiple());
        $this->assertEquals(OnParentVersionAction::VERSION, $prop->getOnParentVersion());
        $this->assertEquals([], $prop->getAvailableQueryOperators());
        $this->assertTrue($prop->isFullTextSearchable()); // True because there was no "nofulltext" attribute
        $this->assertTrue($prop->isQueryOrderable());     // True because there was no "noqueryorder" attribute
    }
}
