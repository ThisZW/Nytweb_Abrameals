<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the message template at admin side
 */
class Zalw_Advancemsg_Block_Adminhtml_Template extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('advancemsg/template/list.phtml');
    }


    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('advancemsg/adminhtml_template_grid', 'advancemsg.template.grid'));
        return parent::_prepareLayout();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    public function getHeaderText()
    {
        return Mage::helper('advancemsg')->__('Message Templates');
    }
}
