<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the preview message window 
 */
class Zalw_Advancemsg_Block_Preview extends Mage_Core_Block_Template
{
	protected $_formData;
	protected $_formData2;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }
    
    public function setFormData($data)
    {
    	$this->_formData = $data;
    }
    public function getFormData() 
    { 
    	return $this->_formData;
    }
    public function setFormData2($data)
    {
    	$this->_formData2 = $data;
    }
    public function getFormData2() 
    {
    	return $this->_formData2;
    }
    public function getTemplateById($templateId)
    {
    	if ($templateId) {
    		$model = Mage::getSingleton('advancemsg/template');
    		$template = $model->load($templateId);
    	} else {
    		return false;
    	}
    	
    }
}

