<?php
namespace PHPCR\Tests\Export;

require_once(__DIR__ . '/../../inc/BaseCase.php');

use DOMDocument, DOMElement, DOMNode, DOMText, DOMXPath;

//7 Export Repository Content
class ExportRepositoryContentTest extends \PHPCR\Test\BaseCase
{
    static public function setupBeforeClass($fixtures = '07_Export/systemview')
    {
        parent::setupBeforeClass($fixtures);
    }

    public function testExportSystemView()
    {
        $stream = fopen('php://memory', 'rwb+');
        $this->sharedFixture['session']->exportSystemView('/tests_export', $stream, false, false);
        rewind($stream);
        $output = new DOMDocument();
        $output->loadXML(stream_get_contents($stream));
        $expected = new DOMDocument();
        $expected->preserveWhiteSpace = false;
        $expected->load(__DIR__.'/../../fixtures/07_Export/systemview.xml');
        fclose($stream);
        $this->assertEquivalentSystem($expected->documentElement, $output->documentElement, new DOMXPath($output));
    }

    /**
     * build a path to this node.
     *
     * for system view, including the name attributes in addition to the
     * element names.
     */
    private function buildPath(DOMNode $n)
    {
        $ret = '';
        while(! $n instanceof DOMDocument) {
            if ($n instanceof DOMElement && in_array($n->tagName, array('sv:node', 'sv:property'))) {
                $name = $n->attributes->getNamedItem('name');
                if ($name == null) {
                    $elem = 'sv:node(unnamed)';
                } else {
                    $elem = $n->tagName.'('.$name->value.')';
                }
            } else {
                $elem = $n->nodeName;
            }
            $ret = $elem . '/' . $ret;
            $n = $n->parentNode;
        }
        return "/$ret";
    }

    /**
     * compare two system view documents.
     *
     * they must have the same properties and values, and the same child nodes,
     * but the order is not necessarily the same, as it is not determined
     */
    private function assertEquivalentSystem(DOMElement $expected, DOMElement $output, DOMXPath $oxpath)
    {
        $this->assertEquals($expected->tagName, $output->tagName);

        foreach ($expected->attributes as $attr) {
            //i.e. sv:name attribute
            $oattr = $output->attributes->getNamedItem($attr->name);
            $this->assertNotNull($oattr, 'missing attribute '.$attr->name);
            $this->assertEquals($attr->value, $oattr->value, 'wrong attribute value');
        }

        if ($expected->tagName == 'sv:property') {
            // properties have ordered sv:value children
            if ($expected->attributes->getNamedItem('name')->value == 'jcr:created') {
                $this->assertNotEmpty($output->textContent);
            } else {
                foreach ($expected->childNodes as $index => $child) {
                    $this->assertEquals('sv:value', $child->tagName);
                    $o = $output->childNodes->item($index);
                    $this->assertInstanceOf('DOMElement', $o, "No child element at $index in ".$this->buildPath($child));
                    $this->assertEquals('sv:value', $o->tagName, 'Unexpected tag name at '.$this->buildPath($expected)."sv:value[$index]");
                    $this->assertEquals($child->textContent, $o->textContent, 'Not the same text at '.$this->buildPath($output)."sv:value[$index]");
                }
            }
        } elseif ($expected->tagName == 'sv:node') {
            // nodes have sv:node or sv:property children
            foreach ($expected->childNodes as $child) {
                $this->assertContains($child->tagName, array('sv:property', 'sv:node'), 'unexpected child of sv:node');
                $childname = $child->attributes->getNamedItem('name')->value;
                $q = $oxpath->query($child->tagName.'[@sv:name="'.$childname.'"]', $output);
                $this->assertEquals(1, $q->length, 'expected to find exactly one node named '.$childname.' under '.$this->buildPath($output));
                $this->assertEquivalentSystem($child, $q->item(0), $oxpath);
            }
        }
    }

    public function testExportDocumentView()
    {
        $stream = fopen('php://memory', 'rwb+');
        $this->sharedFixture['session']->exportDocumentView('/tests_export', $stream, false, false);
        rewind($stream);
        $output = new DOMDocument();
        $output->loadXML(stream_get_contents($stream));
        $expected = new DOMDocument();
        $expected->preserveWhiteSpace = false;
        $expected->load(__DIR__.'/../../fixtures/07_Export/documentview.xml');
        fclose($stream);
        $this->assertEquivalentDocument($expected->documentElement, $output->documentElement, new DOMXPath($output));
    }

    /**
     * compare two document view documents.
     *
     * nodes are elements, properties attributes and must have equal names and values,
     * but the order is not necessarily the same, as it is not determined
     */
    private function assertEquivalentDocument(DOMElement $expected, DOMElement $output, DOMXPath $oxpath)
    {
        if ($expected instanceof DOMText) {
            $this->assertEquals($expected->textContent, $output->textContent, 'Not the same text at '.$this->buildPath($expected));
        } elseif ($expected instanceof DOMElement) {
            $this->assertEquals($expected->tagName, $output->tagName);

            foreach ($expected->attributes as $attr) {
                if ('jcr:created' == $attr->nodeName) {
                    $this->assertNotEmpty($attr->value);
                } else {
                    $oattr = $output->attributes->getNamedItem($attr->name);
                    $this->assertNotNull($oattr, 'missing attribute '.$attr->name.' at '.$this->buildPath($expected));
                    $this->assertEquals($attr->value, $oattr->value, 'wrong attribute value at '.$this->buildPath($expected).'/'.$attr->name);
                }
            }

            foreach ($expected->childNodes as $child) {
                $q = $oxpath->query($child->tagName, $output); //TODO: same-name siblings
                $this->assertEquals(1, $q->length, 'expected to find exactly one node named '.$child->tagName.' under '.$this->buildPath($expected));
                $this->assertEquivalentDocument($child, $q->item(0), $oxpath);
            }
        }
    }
}
