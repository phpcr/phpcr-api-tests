<?php

require_once(dirname(__FILE__) . '/../../inc/baseCase.php');

/**
 * Testing whether node property manipulations work correctly
 *
 * Covering jcr-2.8.3 spec $10.4.2
 */
class Writing_10_SetPropertyMethodsTest extends phpcr_suite_baseCase
{
    protected $nodePath = '/tests_nodetype_base/numberPropertyNode/jcr:content';
    protected $propPath = '/tests_nodetype_base/numberPropertyNode/jcr:content/longNumber';

    static public function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$staticSharedFixture['ie']->import('10_Writing/nodetype');
    }

    public function setUp()
    {
        parent::setUp();
        $this->node = $this->sharedFixture['session']->getNode($this->nodePath);
        $this->property = $this->sharedFixture['session']->getProperty($this->propPath);
    }

    /**
     * @covers \PHPCR\PropertyInterface::setValue
     */
    public function testSetValue()
    {
        $this->property->setValue(1024);

        $this->saveAndRenewSession();
        $prop = $this->sharedFixture['session']->getProperty($this->propPath);
        $this->assertEquals(1024, $prop->getLong());
    }

    /**
     * @covers \PHPCR\NodeInterface::setProperty
     */
    public function testSetPropertyExisting()
    {
        $this->assertTrue($this->node->hasProperty('longNumber'));
        $property = $this->node->setProperty('longNumber', 1024);

        $this->saveAndRenewSession();
        $prop = $this->sharedFixture['session']->getNode($this->nodePath)->getProperty('longNumber');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals(1024, $prop->getLong());
    }


    /**
     * @covers \PHPCR\NodeInterface::setProperty
     */
    public function testSetPropertyNew()
    {
        $property = $this->node->setProperty('newLongNumber', 1024);

        $this->saveAndRenewSession();
        $prop = $this->sharedFixture['session']->getNode($this->nodePath)->getProperty('newLongNumber');
        $this->assertInstanceOf('PHPCR\PropertyInterface', $prop);
        $this->assertEquals(1024, $prop->getLong());
    }

    /**
     * change type of existing property
     * @covers \PHPCR\NodeInterface::setProperty
     */
    public function testSetPropertyWithType()
    {
        $this->node->setProperty('longNumber', 1024.5, \PHPCR\PropertyType::LONG);

        $this->saveAndRenewSession();
        $prop = $this->sharedFixture['session']->getNode($this->nodePath)->getProperty('longNumber');
        $this->assertEquals(1024, $prop->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $prop->getType());
    }

    /**
     * add new property
     * @covers \PHPCR\NodeInterface::setProperty
     */
    public function testSetPropertyNewWithType()
    {
        $this->node->setProperty('newLongNumber', 102.5, \PHPCR\PropertyType::LONG);

        $this->saveAndRenewSession();
        $prop = $this->sharedFixture['session']->getNode($this->nodePath)->getProperty('newLongNumber');
        $this->assertEquals(102, $prop->getLong());
        $this->assertEquals(\PHPCR\PropertyType::LONG, $prop->getType());
        $this->assertFalse($prop->isMultiple());
    }

    public function testSetPropertyMultivalue()
    {
        $this->node->setProperty('multivalue', array(1, 2, 3));

        $this->saveAndRenewSession();
        $node = $this->sharedFixture['session']->getNode($this->nodePath);
        $prop = $node->getProperty('multivalue');
        $this->assertEquals(array(1,2,3), $this->node->getPropertyValue('multivalue'));
        $this->assertEquals(\PHPCR\PropertyType::LONG, $prop->getType());
        $this->assertTrue($prop->isMultiple());
    }

    public function testNewNodeSetProperty()
    {
        $session = $this->sharedFixture['session'];
        $node = $this->node->addNode('child');
        $prop = $node->setProperty('p', 'abc');

        $this->saveAndRenewSession();
        $this->assertTrue($session->nodeExists($this->nodePath . '/child'));
        $this->assertTrue($session->propertyExists($this->nodePath . '/child/p'));

        $node = $session->getNode($this->nodePath . '/child');
        $prop = $node->getProperty('p');

        $this->assertInstanceOf('\PHPCR\PropertyInterface', $prop);
        $this->assertEquals(\PHPCR\PropertyType::STRING, $prop->getType());
        $this->assertEquals('abc', $prop->getString());
    }

    //TODO: is this all creation modes? the types are tested in SetPropertyTypes

    //TODO: Session::hasPendingChanges
}
