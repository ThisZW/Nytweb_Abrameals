<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the edit of admin inbox 
 */
class Zalw_Advancemsg_Block_Adminhtml_Customermsg_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //constructor 	
	public function __construct()
    {
       	$this->_objectId = 'id';
	$this->_blockGroup = 'advancemsg';
	$this->_controller = 'adminhtml_customermsg';
	$this->_mode = 'edit';
	$this->_headerText = '';
	
	parent::__construct();
    }

}
