<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show admin inbox form
 */
class Zalw_Advancemsg_Block_Adminhtml_Customermsg_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
	//sets form and titles form
      	$form = new Varien_Data_Form();
      	$this->setForm($form);
      	$fieldset = $form->addFieldset('customermsg_form', array('legend'=>Mage::helper('advancemsg')->__('Message')));
     	
	$id =  $this->getRequest()->getParam('id');
	$collection = Mage::getModel('advancemsg/customermsg')->load($id);
	$status = $collection->getStatus();
	if($status==0){
		$statusValue ="Unread";
	}
	else{
		$statusValue ="Read";
	}
	$fieldset->addField('name', 'label', array(
          	'label'     => Mage::helper('advancemsg')->__('Customer Name'),
          	'name'      => 'name',
		
      	));

	$fieldset->addField('messagetitle', 'label', array(
          	'label'     => Mage::helper('advancemsg')->__('Subject'),
          	'name'      => 'messagetitle',
		
      	));

	$fieldset->addField('message', 'label', array(
          	'label'     => Mage::helper('advancemsg')->__('Message'),
          	'name'      => 'message',
		
      	));


	$fieldset->addField('status', 'note', array(
		  'label'     => Mage::helper('advancemsg')->__('Status'),
          'text'     => $statusValue,
        ));
	
	
    	//maintains session on the basis of userid
      	if ( Mage::getSingleton('adminhtml/session')->getmessageData() )
      	{
          	$form->setValues(Mage::getSingleton('adminhtml/session')->getmessageData());
          	Mage::getSingleton('adminhtml/session')->setmessageData(null);
      	} 
	//maintains userid with the help of registry
	elseif ( Mage::registry('message_data') ) 
	{
          	$form->setValues(Mage::registry('message_data')->getData());
      	}
	
      	return parent::_prepareForm();
  }
}
