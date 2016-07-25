<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the message as per message id
 */
class Zalw_Advancemsg_Block_View extends Mage_Core_Block_Template
{
	
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }
    
    public function getDetail()
    {
    	$model = Mage::getSingleton('advancemsg/content');
    	$messageId = $this->getRequest()->getParam('id');
    	if ($model->checkOwner($messageId, Mage::getSingleton('customer/session')->getCustomer()->getId())) {
    		return $model->load($messageId);
    	} else {
    		return false;
    	}
    }
    public function getTemplateStyles($templateId)
    {
    	if ($templateId) {
    		$template = Mage::getSingleton('advancemsg/template');
    		$templateObj = $template->load($templateId);
    		if ($templateObj) {
    			return $templateObj->getTemplateStyles();
    		}
    	}
	return false;
    }
    
    public function markMessageAsRead($messageId)
    {
    	try
	{		
		$message= Mage::getModel('advancemsg/content')
				->load($messageId)
				->setStatus(Zalw_Advancemsg_Model_Content::MESSAGE_STATUS_READ)
				->save();		
	} catch (Exception $e) {
		Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
    	}
    }
}