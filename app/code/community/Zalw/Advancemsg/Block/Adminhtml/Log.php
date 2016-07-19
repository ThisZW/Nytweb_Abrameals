<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the message log at admin side
 */
class Zalw_Advancemsg_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();        
        $this->setTemplate('advancemsg/log/list.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('advancemsg/adminhtml_log_grid', 'advancemsg.log.grid'));
        return parent::_prepareLayout();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    public function getHeaderText()
    {
        return Mage::helper('advancemsg')->__('My Message Box');
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
    
}
