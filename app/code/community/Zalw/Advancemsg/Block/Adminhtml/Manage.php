<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the manage message 
 */
class Zalw_Advancemsg_Block_Adminhtml_Manage extends Mage_Adminhtml_Block_Template
{
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('advancemsg/manage/index.phtml');
	$this->_controller = 'adminhtml_manage';
    }

    protected function _prepareLayout()
    {
    	$this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('advancemsg')->__('To Message Box'),
                    'onclick'   => "window.location.href = '" . $this->getUrl('*/*/log') . "'",
                    'class'     => 'back'
                ))
        );
        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('advancemsg')->__('Reset'),
                    'onclick'   => 'window.location.href = window.location.href'
                ))
        );
        $this->setChild('preview_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('advancemsg')->__('Preview Messages'),
                    'onclick'   => 'messageControl.preview();',
                    'class'     => 'task'
                ))
        );
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('advancemsg')->__('Send Messages'),
                    'onclick'   => 'messageControl.save();',
                    'class'     => 'save'
                ))
        );
        $this->setChild('grid', $this->getLayout()->createBlock('advancemsg/adminhtml_manage_customer_grid', 'advancemsg.customer.grid'));
        return parent::_prepareLayout();
    }
	public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }
    public function getPreviewButtonHtml()
    {
        return $this->getChildHtml('preview_button');
    }
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }
    public function getSendUrl()
    {
        return $this->getUrl('*/*/send');
    }
    public function getPreviewUrl()
    {
        return $this->getBaseUrl().'advancemsg/manage/preview';
    }

    public function getHeaderText()
    {
        return Mage::helper('advancemsg')->__('Send New Messages');
    }
    public function getForm()
    {
        return $this->getLayout()
            ->createBlock('advancemsg/adminhtml_manage_form')
            ->toHtml();
    }
    public function getAjaxUrl()
    {
    	return $this->getUrl('*/*/ajax');
    }
}
