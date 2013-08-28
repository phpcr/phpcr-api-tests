<?php
namespace PHPCR\Tests\NodeTypeManagement;

require_once 'NodeTypeBaseCase.php';

/**
 * Test the compact nodetype definition format registering
 * added in JCR 2.1
 */
class CndTest extends NodeTypeBaseCase
{
    protected function registerNodeTypes($allowUpdate)
    {
        $ntm = $this->workspace->getNodeTypeManager();

        return $ntm->registerNodeTypesCnd($this->cnd, $allowUpdate);
    }

    protected function registerNodeTypePrimaryItem()
    {
        $ntm = $this->workspace->getNodeTypeManager();

        return $ntm->registerNodeTypesCnd($this->primary_item_cnd, true);
    }

    private $cnd = "
        <'phpcr'='http://www.doctrine-project.org/projects/phpcr_odm'>
         [phpcr:apitest]
          mixin
          - phpcr:class (string) multiple
          [phpcr:test]
          mixin
          - phpcr:prop (string)
          ";

    private $primary_item_cnd = "
        <'phpcr'='http://www.doctrine-project.org/projects/phpcr_odm'>
        [phpcr:primary_item_test]
        - phpcr:content (string)
        primary
        ";
}
