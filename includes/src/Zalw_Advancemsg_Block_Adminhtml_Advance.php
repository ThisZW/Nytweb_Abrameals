<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the advance message log
 */
class Zalw_Advancemsg_Block_Adminhtml_Advance extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('advancemsg/advance/list.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('advancemsg/adminhtml_advance_grid', 'advancemsg.advance.grid'));
        return parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        return Mage::helper('advancemsg')->__('Advanced Messages Management');
    }
}
