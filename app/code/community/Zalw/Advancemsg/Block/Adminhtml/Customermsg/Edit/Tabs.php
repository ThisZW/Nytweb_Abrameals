<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show admin inbox tabs to authenticate message 
 */
class Zalw_Advancemsg_Block_Adminhtml_Customermsg_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
  //constructor have tabs id and label name
  public function __construct()
  {
   // parent::__construct();
      //sets up tabs id ,destination form,show title	
    /*    $this->setId('customermsg_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('advancemsg')->__('Message Information'));*/
  }
  //function shows the label
 /* protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
      'label'     => Mage::helper('advancemsg')->__('Authenticate Messages'),
      'title'     => Mage::helper('advancemsg')->__('Message'),
      'content'   => $this->getLayout()->createBlock('advancemsg/adminhtml_customermsg_edit_tab_form')->toHtml(),
      ));
     
		 
      return parent::_beforeToHtml();
  }*/
}
