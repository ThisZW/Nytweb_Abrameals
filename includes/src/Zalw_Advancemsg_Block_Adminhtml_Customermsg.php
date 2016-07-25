<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the admin inbox
 */
class Zalw_Advancemsg_Block_Adminhtml_Customermsg extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //constructor 	
    public function __construct()
    {
     $this->_controller = 'adminhtml_customermsg';
     $this->_blockGroup = 'advancemsg';
     $this->_headerText = Mage::helper('advancemsg')->__('Manage Customer Messages');    
    parent::__construct();
	$this->_removeButton('add');
    } 
	
	public function getCreateUrl()
    {
        return $this->getUrl('*/*/index');
    }
}
