<?php
require_once(dirname(__FILE__) . '/../../../inc/baseCase.php');

/** test the javax.jcr.Row interface
 *  todo: getNode, getPath, getScore
 */
class jackalope_tests_read_SearchTest_Row extends jackalope_baseCase {
    private $row;

    public static function setupBeforeClass() {
        parent::setupBeforeClass();
        self::$staticSharedFixture['qm'] = self::$staticSharedFixture['session']->getWorkspace()->getQueryManager();
    }

    public function setUp() {
        parent::setUp();
        $query = $this->sharedFixture['qm']->createQuery('/*/element(tests_read_search_base, nt:folder)', 'xpath');
        $qr = $query->execute();
        //sanity check
        $this->assertType('PHPCR\Query\QueryResultInterface', $qr);

        $rs = $qr->getRows();
        $rs->rewind();
        $this->row = $rs->current();

        $this->assertType('PHPCR\Query\RowInterface', $this->row);
    }

    public function testRowGetValues() {
        $ret = $this->row->getValues();
        $this->assertType('array', $ret);

        foreach($ret as $value) {
            $this->assertNotNull($value);
        }
    }

    public function testRowGetValue() {
        foreach(jackalope_tests_read_SearchTest_QueryResults::$expect as $propName) {
            $val = $this->row->getValue($propName);
            $this->assertNotNull($val);

            switch($propName) {
                case 'jcr:createdBy':
                    $val->getString();
                    //TODO: seems not to be implemented in alpha5 or null for some other reason. whatever
                    break;
                case 'jcr:created':
                    //2009-07-07T14:35:06.955+02:00
                    list($y, $m, $dusw) = split('-',$val);
                    list($d, $usw) = split('T', $dusw);
                    $this->assertTrue($y > 0);
                    $this->assertTrue($m > 0);
                    $this->assertTrue($d > 0);
                    $this->assertTrue(strlen($usw)==18);
                    $d = $val->getDate();
                    $this->assertTrue($d instanceof DateTime);
                    break;
                case 'jcr:primaryType':
                    //nt:folder - depends on the search query
                    $this->assertEquals('nt:folder', $val);
                    break;
                case 'jcr:path':
                    $str = $val->getString();
                    $this->assertEquals('/tests_read_search_base', $val);
                    break;
                case 'jcr:score':
                    //for me, it was 1788 but i guess that is highly implementation dependent
                    $num = $val->getLong();
                    $this->assertTrue($num > 0);
                    break;
                default:
                    $this->fail("Unknown property $propName");
            }
        }
    }
}
