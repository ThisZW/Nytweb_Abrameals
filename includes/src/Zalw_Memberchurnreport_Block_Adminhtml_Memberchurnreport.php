<?php
class Zalw_Memberchurnreport_Block_Adminhtml_Memberchurnreport extends Mage_Adminhtml_Block_Widget_Grid_Container {
	
	public function __construct() {
	    $this->_controller = 'adminhtml_memberchurnreport';
	    $this->_blockGroup = 'memberchurnreport';
	    parent::__construct();
	    $this->_headerText = Mage::helper('memberchurnreport')->__('Member Churn Report');
	    $this->setTemplate('report/grid/container.phtml');
	    $this->_removeButton('add');
	    $this->addButton('filter_form_submit', array(
                'label'     => Mage::helper('memberchurnreport')->__('Show Report'),
                'onclick'   => 'filterFormSubmit()'
            ));
	}



	/**
     * This function will prepare our filter URL
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/index', array('_current' => true));
    }
}