<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @group module:Mage_Catalog
 * @magentoDataFixture Mage/Catalog/_files/products_sort_attributes.php
 */
class Mage_Catalog_Model_Resource_Product_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Resource_Product_Collection
     */
    protected $_collection;

    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->_collection = new Mage_Catalog_Model_Resource_Product_Collection;
    }

    /**
     * Clean up collection instance after every test
     */
    protected function tearDown()
    {
        $this->_collection->clear();
        $this->_collection = null;
    }

    /**
     * @dataProvider setOrderDataProvider
     */
    public function testSetOrder($order, $expectedOrder)
    {
        $this->_collection->setOrder($order);
        $this->_collection->load(); // perform real SQL query

        $selectOrder = $this->_collection->getSelect()->getPart(Zend_Db_Select::ORDER);
        foreach ($expectedOrder as $field) {
            $orderBy = array_shift($selectOrder);
            $this->assertArrayHasKey(0, $orderBy);
            $this->assertTrue(false !== strpos($orderBy[0], $field),
                'Ordering by same column more than once is restricted by multiple RDBMS requirements.'
            );
        }
    }

    public function setOrderDataProvider()
    {
        return array(
            array(array('sku', 'sku'), array('sku')),
            array(array('sku', 'name', 'sku'), array('name', 'sku')),
        );
    }

    /**
     * @param $attributeCode
     * @param string $accessor
     * @dataProvider sortByCustomAttributesDataProvider
     */
    public function testSortAscByCustomAttributes($attributeCode, $accessor)
    {
        $this->_collection
                ->setOrder($attributeCode, 'ASC')
                ->addAttributeToSelect($attributeCode)
                ->load();
        $first = $this->_collection->getFirstItem()->$accessor($attributeCode);
        $last  = $this->_collection->getLastItem()->$accessor($attributeCode);
        $this->assertLessThan($last, $first, "Sorting by custom attribute '$attributeCode' (ASC)");
    }

    /**
     * @param string $attributeCode
     * @param string $accessor
     * @dataProvider sortByCustomAttributesDataProvider
     */
    public function testSortDescByCustomAttributes($attributeCode, $accessor)
    {
        $this->_collection
                ->setOrder($attributeCode, 'DESC')
                ->addAttributeToSelect($attributeCode)
                ->load();

        $first = $this->_collection->getFirstItem()->$accessor($attributeCode);
        $last  = $this->_collection->getLastItem()->$accessor($attributeCode);
        $this->assertLessThan($first, $last, "Sorting by custom attribute '$attributeCode' (DESC)");
    }

    /**
     * The attribute codes have to match the ones declared in the fixture file
     *
     * See Mage/Catalog/_files/products_sort_attributes.php
     *
     * @return array
     */
    public function sortByCustomAttributesDataProvider()
    {
        return array(
            array('attr_src_bool', 'getData'),
            array('attr_src_cntryofmnfctr', 'getData'),
            array('attr_src_table', 'getAttributeText'),
        );
    }
}
