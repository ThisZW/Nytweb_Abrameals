<?php

class Zalw_Advancemsg_Block_Adminhtml_Log_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
       
        $this->_objectId = 'id';
        //vwe assign the same blockGroup as the Grid Container
        $this->_blockGroup = 'advancemsg';
        //and the same controller
        $this->_controller = 'adminhtml_log';
        //define the label for the save and delete button

        $this->_mode = 'edit';
      //  echo $this->_objectId;
     parent::__construct();
     	//$this->_removeButton('reset');
	//$this->_removeButton('delete');
	//$this->_removeButton('save');
	//$this->_removeButton('back');
        
       // $this->_updateButton('delete', 'label', __('Delete'));
       // $this->_removeButton('save');
       // 
       //  
        //$this->_addButton('button_id', array(
        //    'label'     => Mage::helper('displayproducts')->__('Send'),
        //     'onclick'   => 'saveAndContinueEdit()',
        //), 0, 100,'footer');
        //   
        // $this->_formScripts[] = "
        //    
        //
        //    function saveAndContinueEdit(){
        //    
        //        editForm.submit($('admin_log_reply_form').action');
        //    }
        //";     
       //    
       // $newOrEdit = $this->getRequest()->getParam('id')
       //     ? $this->__('Edit') 
       //     : $this->__('New');
       // $this->_headerText =  $newOrEdit . ' ' . $this->__('Contact'); 
       // 
    }
       
}