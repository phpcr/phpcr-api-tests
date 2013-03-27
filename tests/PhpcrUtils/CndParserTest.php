<?php

namespace PHPCR\Tests\PhpcrUtils;

require_once(__DIR__ . '/../../inc/BaseCase.php');

use PHPCR\PropertyType;
use PHPCR\NodeType\PropertyDefinitionTemplateInterface;
use PHPCR\Version\OnParentVersionAction;

use PHPCR\Util\CND\Helper\NodeTypeGenerator;
use PHPCR\Util\CND\Reader\BufferReader;
use PHPCR\Util\CND\Parser\CndParser;
use PHPCR\Util\CND\Scanner\GenericScanner;
use PHPCR\Util\CND\Scanner\Context\DefaultScannerContextWithoutSpacesAndComments;

class CndParserTest extends \PHPCR\Test\BaseCase
{
    function testGenerator()
    {
        // the worst case example from http://jackrabbit.apache.org/node-type-notation.html
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

        //define('DEBUG', true);

        $parser = new CndParser($this->sharedFixture['session']->getWorkspace()->getNodeTypeManager());

        $res = $parser->parseString($cnd);

        $def = reset($res['nodeTypes']);

        $this->assertEquals(array('ns' => 'http://namespace.com/ns'), $res['namespaces']);

        $this->assertInstanceOf('\PHPCR\NodeType\NodeTypeTemplateInterface', $def);
        $this->assertEquals('ns:NodeType', $def->getName());
        $this->assertEquals(array('ns:ParentType1', 'ns:ParentType2'), $def->getDeclaredSuperTypeNames());
        $this->assertTrue($def->hasOrderableChildNodes());
        $this->assertTrue($def->isMixin());
        $this->assertFalse($def->isQueryable());
        $this->assertFalse($def->isAbstract());
        $this->assertEquals(1, count($def->getPropertyDefinitionTemplates()));

        /** @var $prop PropertyDefinitionTemplateInterface */
        $prop = $def->getPropertyDefinitionTemplates()->getIterator()->current();

        $this->assertEquals('ex:property', $prop->getName());
        $this->assertEquals(PropertyType::STRING, $prop->getRequiredType());
        $this->assertEquals(array('default1', 'default2'), $prop->getDefaultValues());
        $this->assertEquals(array('constraint1', 'constraint2'), $prop->getValueConstraints());
        $this->assertTrue($prop->isAutoCreated());
        $this->assertTrue($prop->isMandatory());
        $this->assertTrue($prop->isProtected());
        $this->assertTrue($prop->isMultiple());
        $this->assertEquals(OnParentVersionAction::VERSION, $prop->getOnParentVersion());
        $this->assertEquals(array(), $prop->getAvailableQueryOperators());
        $this->assertTrue($prop->isFullTextSearchable()); // True because there was no "nofulltext" attribute
        $this->assertTrue($prop->isQueryOrderable());     // True because there was no "noqueryorder" attribute
    }

    public function testBigFile()
    {
        $cnd = file_get_contents(__DIR__ . '/resources/jackrabbit_nodetypes.cnd');
        $parser = new CndParser($this->sharedFixture['session']->getWorkspace()->getNodeTypeManager());

        $res = $parser->parseString($cnd);
        // TODO: compare with the types we get from the repository
    }
}
