<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @copyright   Zalw
 * @use    	To show customer inbox form 
 */
class Zalw_Advancemsg_Block_Customermsg extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('advancemsg/customermsg.phtml');
    }   
     
    //protected function getCountOfMessages()
    //{
    //    $collection = Mage::getResourceModel('advancemsg/customermsg_collection')
    //        ->addFieldToSelect('*')
    //        ->addFieldToFilter('name',$customerName = Mage::getSingleton('customer/session')->getCustomer()->getName());
    //        
    //    return count($collection);
    //}
    
    public function getViewUrl($messageId)
    {
    	return $this->getUrl('*/*/customersentview/id/'.$messageId);
    }
 	
}

