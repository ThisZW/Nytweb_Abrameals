<?php
class Zalw_Memberchurnreport_Block_Adminhtml_Memberchurnreport_Grid extends Mage_Adminhtml_Block_Report_Grid_Abstract {

    protected $_columnGroupBy = 'order_number';

    public function __construct() {
        parent::__construct();
        $this->setId('memberchurnreportGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setSubReportSize(false);
        $this->setCountTotals(true);
    }

    public function getResourceCollectionName()
    {
        return ($this->getFilterData()->getData('report_type') == 'updated_at_order')
            ? 'memberchurnreport/memberchurnreport_collection'
            : 'memberchurnreport/memberchurnreport_collection';
    }

    protected function _prepareColumns() {

        $this->addColumn('order_number', array(
            'header'    =>Mage::helper('reports')->__('Order Number'),
            'align'     =>'right',
            'index'     =>'order_number',
            //'total'     =>'sum',
            'type'      =>'number'
        ));
        $this->addColumn('created_at', array(
            'header'    =>Mage::helper('reports')->__('Order Date'),
            'align'     =>'right',
            'index'     =>'created_at',
            //'total'     =>'sum',
            'type'      =>'date'
        ));
        $this->addColumn('base_original_price', array(
            'header'    =>Mage::helper('reports')->__('Order Original RSP Price'),
            'align'     =>'right',
            'index'     =>'base_original_price',
            //'total'     =>'sum',
            'type'      =>'number'
        ));
        $this->addColumn('total_amount', array(
            'header'    =>Mage::helper('reports')->__('Order Cost'),
            'align'     =>'right',
            'index'     =>'total_amount',
            //'total'     =>'sum',
            'type'      =>'number'
        ));
        $this->addColumn('order_selling_price', array(
            'header'    =>Mage::helper('reports')->__('Order Selling Price'),
            'align'     =>'right',
            'index'     =>'order_selling_price',
            //'total'     =>'sum',
            'type'      =>'number'
        ));
        $this->addColumn('email', array(
            'header'    =>Mage::helper('reports')->__('Email'),
            'align'     =>'right',
            'index'     =>'email',
            //'total'     =>'sum',
            'type'      =>'email'
        ));
        $this->addColumn('member_signup_date', array(
            'header'    =>Mage::helper('reports')->__('Member Signup Date'),
            'align'     =>'right',
            'index'     =>'member_signup_date',
            //'total'     =>'sum',
            'type'      =>'date'
        ));
        $this->addColumn('first_name', array(
            'header'    =>Mage::helper('reports')->__('First Name'),
            'align'     =>'right',
            'index'     =>'first_name',
            //'total'     =>'sum',
            'type'      =>'text'
        ));
        $this->addColumn('last_name', array(
            'header'    =>Mage::helper('reports')->__('Last Name'),
            'align'     =>'right',
            'index'     =>'last_name',
            //'total'     =>'sum',
            'type'      =>'text'
        ));
        $this->addColumn('gender', array(
            'header'    =>Mage::helper('reports')->__('Gender'),
            'align'     =>'right',
            'index'     =>'gender',
            //'total'     =>'sum',
            'type'      =>'text'
        ));
        $this->addColumn('phone_number', array(
            'header'    =>Mage::helper('reports')->__('Phone Number'),
            'align'     =>'right',
            'index'     =>'phone_number',
            //'total'     =>'sum',
            'type'      =>'number'
        ));
        $this->addColumn('address', array(
            'header'    =>Mage::helper('reports')->__('Address'),
            'align'     =>'right',
            'index'     =>'phone_number',
            //'total'     =>'sum',
            'type'      =>'text'
        ));
        $this->addColumn('city', array(
            'header'    =>Mage::helper('reports')->__('City/Suburb'),
            'align'     =>'right',
            'index'     =>'city',
            //'total'     =>'sum',
            'type'      =>'text'
        ));
        $this->addColumn('post_code', array(
            'header'    =>Mage::helper('reports')->__('Postal Code'),
            'align'     =>'right',
            'index'     =>'post_code',
            //'total'     =>'sum',
            'type'      =>'number'
        ));
        $this->addColumn('customer_total_order', array(
            'header'    =>Mage::helper('reports')->__('Customer Total Orders'),
            'align'     =>'right',
            'index'     =>'customer_total_order',
            //'total'     =>'sum',
            'type'      =>'number'
        ));
        $this->addColumn('avg_order_value', array(
            'header'    =>Mage::helper('reports')->__('Avg. Order Value'),
            'align'     =>'right',
            'index'     =>'avg_order_value',
            //'total'     =>'sum',
            'type'      =>'number'
        ));
        $this->addExportType('*/*/exportCsv', Mage::helper('memberchurnreport')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('memberchurnreport')->__('XML'));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return false;
    }
}