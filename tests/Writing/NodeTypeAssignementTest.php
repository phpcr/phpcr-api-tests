<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Tests\Writing;

use PHPCR\NodeType\ConstraintViolationException;
use PHPCR\NodeType\NoSuchNodeTypeException;
use PHPCR\Test\BaseCase;

/**
 * Test setting node types on nodes.
 */
class NodeTypeAssignementTest extends BaseCase
{
    public static function setupBeforeClass($fixtures = '10_Writing/nodetype'): void
    {
        parent::setupBeforeClass($fixtures);
    }

    public function setUp(): void
    {
        $this->renewSession();
        parent::setUp();

        //all tests in this suite have a node in the fixtures, but without the dataset in the name
        $name = $this->getName();
        if (false !== $pos = strpos($this->getName(), ' ')) {
            $name = substr($name, 0, $pos);
        }
        $this->node = $this->rootNode->getNode("tests_write_nodetype/$name");
    }

    // TODO: a repository MAY also allow changing the primary node type.

    /**
     * the predefined mixin types that do not depend on optional features.
     */
    public static $mixins = [
        'mix:etag',
        'mix:language',
        'mix:lastModified',
        'mix:mimeType',
        'mix:referenceable',
        'mix:shareable',
        'mix:title',
    ];

    public static function mixinTypes()
    {
        $ret = [];

        foreach (self::$mixins as $mixin) {
            $ret[] = [$mixin];
        }

        return $ret;
    }

    /**
     * @dataProvider mixinTypes
     */
    public function testAddMixinOnNewNode($mixin)
    {
        $newNode = $this->node->addNode('parent-'.strtr($mixin, ':', '-'), 'nt:unstructured');
        $newNode->addMixin($mixin);
        $path = $newNode->getPath();
        $session = $this->saveAndRenewSession();
        $savedNode = $session->getNode($path);
        $resultTypes = [];

        foreach ($savedNode->getMixinNodeTypes() as $type) {
            $resultTypes[] = $type->getName();
        }
        $this->assertContains($mixin, $resultTypes, "Node mixins should contain $mixin");
    }

    /**
     * @dataProvider mixinTypes
     */
    public function testAddMixinOnExistingNode($mixin)
    {
        $node = $this->node->getNode(strtr($mixin, ':', '-'));
        $path = $node->getPath();
        $node->addMixin($mixin);
        $session = $this->saveAndRenewSession();
        $savedNode = $session->getNode($path);
        $resultTypes = [];
        foreach ($savedNode->getMixinNodeTypes() as $type) {
            $resultTypes[] = $type->getName();
        }
        $this->assertContains($mixin, $resultTypes, "Node mixins should contain $mixin");
    }

    /**
     * adding an already existing mixin should not set the node into the modified state
     * adding a mixin to a node that already has a mixin in the permanent storage should work too.
     */
    public function testAddMixinTwice()
    {
        $this->assertFalse($this->node->isModified());
        $this->assertTrue($this->node->isNodeType('mix:referenceable'), 'error with the fixtures, the node should have this mixin type');
        $this->node->addMixin('mix:referenceable'); // this mixin is already in the fixtures
        $this->assertFalse($this->node->isModified());

        $this->node->addMixin('mix:mimeType');
        $this->assertTrue($this->node->isModified());
        $this->session->save();
        $this->assertFalse($this->node->isModified());
        $this->node->addMixin('mix:mimeType');
        $this->assertFalse($this->node->isModified());
    }

    /**
     * add a mixin type that extends another type and check if the node
     * is properly reported as implementing the base type too.
     */
    public function testAddMixinExtending()
    {
        if (!$this->session->getRepository()->getDescriptor('option.versioning.supported')) {
            $this->markTestSkipped('PHPCR repository doesn\'t support versioning');
        }

        $this->node->addMixin('mix:versionable');
        $this->assertTrue($this->node->isNodeType('mix:referenceable'));
    }

    public function testAddMixinPrimaryType()
    {
        $this->expectException(ConstraintViolationException::class);

        $this->node->addMixin('nt:unstructured');
        $this->saveAndRenewSession();
    }

    /**
     * Test that assigning an unexisting mixin type to a node will fail.
     */
    public function testAddMixinNonexisting()
    {
        $this->expectException(NoSuchNodeTypeException::class);

        $this->node->addMixin('mix:nonexisting');
        $this->saveAndRenewSession();
    }

    public function testRemoveMixin()
    {
        $this->assertTrue($this->node->isNodeType('mix:mimeType'));
        $this->node->removeMixin('mix:mimeType');

        $this->assertTrue($this->node->isModified());
        $this->assertFalse($this->node->isNodeType('mix:mimeType'));

        $this->saveAndRenewSession();

        $this->assertFalse($this->node->isNodeType('mix:mimeType'));
    }

    public function testRemoveMixinLast()
    {
        $this->assertTrue($this->node->isNodeType('mix:mimeType'));
        $this->node->removeMixin('mix:mimeType');
        $this->assertTrue($this->node->isModified());

        $this->assertFalse($this->node->isNodeType('mix:mimeType'));

        $this->saveAndRenewSession();

        $this->assertFalse($this->node->isNodeType('mix:mimeType'));
    }

    public function testRemoveMixinNotSet()
    {
        $this->expectException(NoSuchNodeTypeException::class);

        $this->node->removeMixin('mix:referenceable');
        $this->saveAndRenewSession();
    }

    public function testRemoveMixinNone()
    {
        $this->expectException(NoSuchNodeTypeException::class);

        $this->node->removeMixin('mix:mimeType');
        $this->saveAndRenewSession();
    }

    public function testSetMixins()
    {
        $this->node->setMixins(['mix:referenceable', 'mix:mimeType']);
        $this->assertTrue($this->node->isModified());

        $this->assertTrue($this->node->isNodeType('mix:mimeType'));
        $this->assertTrue($this->node->isNodeType('mix:referenceable'));

        $this->saveAndRenewSession();

        $this->assertTrue($this->node->isNodeType('mix:mimeType'));
        $this->assertTrue($this->node->isNodeType('mix:referenceable'));
    }

    public function testSetMixinsReplace()
    {
        $this->assertTrue($this->node->isNodeType('mix:referenceable'));
        $this->node->setMixins(['mix:mimeType']);
        $this->assertTrue($this->node->isModified());

        $this->assertTrue($this->node->isNodeType('mix:mimeType'));
        $this->assertFalse($this->node->isNodeType('mix:referenceable'));

        $this->assertTrue($this->node->isNodeType('mix:mimeType'));
        $this->assertFalse($this->node->isNodeType('mix:referenceable'));
    }

    public function testSetMixinsNotFound()
    {
        $this->expectException(NoSuchNodeTypeException::class);

        $this->node->setMixins(['mix:referenceable', 'mix:nonexisting']);
        $this->saveAndRenewSession();
    }

    public function testSetMixinsConstraintViolation()
    {
        $this->expectException(ConstraintViolationException::class);

        $this->node->setMixins(['mix:referenceable', 'nt:folder']);
        $this->saveAndRenewSession();
    }
}
