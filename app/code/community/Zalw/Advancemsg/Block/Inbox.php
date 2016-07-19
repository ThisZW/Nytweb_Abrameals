<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the inbox of particular customer
 */
class Zalw_Advancemsg_Block_Inbox extends Mage_Core_Block_Template
{
    public function __construct()
    {
		parent::_construct();
		
        $this->setTemplate('advancemsg/list.phtml');
    }

    protected function _prepareLayout()
    {    	
        parent::_prepareLayout();
        $this->setChild('grid', $this->getLayout()->createBlock('advancemsg/grid', 'advancemsg.grid'));
        return $this;
    }    
    
    public function getViewUrl($messageId)
    {
    	return $this->getUrl('*/*/view/id/'.$messageId);
    }
    
    protected function getCountOfMessages()
    {
        return Mage::getModel('advancemsg/content')->getTotalCount(Mage::getSingleton('customer/session')->getCustomer()->getId());
    }
    
}

