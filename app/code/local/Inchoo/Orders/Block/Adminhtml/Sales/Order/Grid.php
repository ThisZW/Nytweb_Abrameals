<?php
 
class Inchoo_Orders_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('inchoo_order_grid');
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('group_id',4)-> addFieldToFilter('freeze_status', 2);

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }
    
    protected function _prepareColumns()
    {
        $helper = Mage::helper('inchoo_orders');
        $currency = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        $this->addColumn('lastname', array(
            'header' => $helper->__('Last Name'),
            'index'  => 'lastname'
        ));

        $this->addColumn('firstname', array(
            'header' => $helper->__('First Name'),
            'index'  => 'firstname'
        ));
 
        // $this->addColumn('purchased_on', array(
        //     'header' => $helper->__('Purchased On'),
        //     'type'   => 'datetime',
        //     'index'  => 'created_at'
        // ));
 
        // $this->addColumn('products', array(
        //     'header'       => $helper->__('Products Purchased'),
        //     'index'        => 'products',
        //     'filter_index' => '(SELECT GROUP_CONCAT(\' \', x.name) FROM sales_flat_order_item x WHERE main_table.entity_id = x.order_id AND x.product_type != \'configurable\')'
        // ));
 
        // $this->addColumn('fullname', array(
        //     'header'       => $helper->__('Name'),
        //     'index'        => 'fullname',
        //     'filter_index' => 'CONCAT(customer_firstname, \' \', customer_lastname)'
        // ));
 
        // $this->addColumn('grand_total', array(
        //     'header'        => $helper->__('Grand Total'),
        //     'index'         => 'grand_total',
        //     'type'          => 'currency',
        //     'currency_code' => $currency
        // ));

        // $this->addColumn('order_status', array(
        //     'header'  => $helper->__('Status'),
        //     'index'   => 'status',
        //     'type'    => 'options',
        //     'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        // ));
 
        $this->addExportType('*/*/exportInchooCsv', $helper->__('CSV'));
        $this->addExportType('*/*/exportInchooExcel', $helper->__('Excel XML'));
 
        return parent::_prepareColumns();
    }
 
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}