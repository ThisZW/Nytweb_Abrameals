<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the message as per message id
 */
class Zalw_Advancemsg_Block_Customersentview extends Mage_Core_Block_Template
{
	
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }
    
    public function getDetail()
    {
    	$model = Mage::getSingleton('advancemsg/customermsg');
    	$messageId = $this->getRequest()->getParam('id');
    	//if ($model->checkOwner($messageId, Mage::getSingleton('customer/session')->getCustomer()->getId())) {
    		return $model->load($messageId);
    	//} else {
    	//	return false;
    	//}
    }
    
    public function markMessageAsRead($messageId)
    {
    	try
	{		
		$message= Mage::getModel('advancemsg/customermsg')
				->load($messageId)
				->setStatus(Zalw_Advancemsg_Model_Content::MESSAGE_STATUS_READ)
				->save();		
	} catch (Exception $e) {
		Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
    	}
    }
}

