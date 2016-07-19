<?php
class Zalw_Advancemsg_Block_Adminhtml_Log_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
   protected function _prepareForm()
    {
        $form   = new Varien_Data_Form(array(
            'id'        => 'admin_log_reply_form',
            //'action'    => $this->getData('action'),
            'method'    => 'post'
        ));
        
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset   = $form->addFieldset('reply_fieldset', array(
            'legend'    => Mage::helper('advancemsg')->__('Reply to Previous Message'),
            'class'     => 'fieldset-wide'
        ));

	$fieldset->addField('message_text', 'textarea' , array(
		    'name'     => 'message_text',
		    'label'    => Mage::helper('advancemsg')->__('Message Text'),
		    'required' => false,
		));
	
//	$fieldset->addField('maximum_message_characters', 'note', array(
//          'text'     => "(You can write message upto 250 characters.)",
//        ));
        
        $fieldset->addField('log_reply_submit', 'submit', array(
          'value'     => 'send',
        ));
     
        $form->setAction($this->getUrl('*/*/adminlogreply/id/'.$this->getRequest()->getParam('id')));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    
    }
}


















/*class Zalw_Displayproducts_Block_Adminhtml_Displayproducts_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    

    
    protected function _prepareForm()
    {
        
            echo ("sdf");
 print_r(Mage::registry('abc'));
 print_r(Mage::registry('messageDetails'));
        
        // Instantiate a new form to display our brand for editing.
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl(
                '//edit',
                array(
                    '_current' => true,
                    'continue' => 0,
                )
            ),
            'method' => 'post',
        ));
        $form->setUseContainer(true);
        $this->setForm($form);
        //print_r(Mage::registry('messageDetails'));die;
        $form->setValues(Mage::registry('messageDetails'));
        // Define a new fieldset. We need only one for our simple entity.
        $fieldset = $form->addFieldset(
            'general',
            array(
                'legend' => $this->__('Message Details')
            )
        );

        $brandSingleton = Mage::getSingleton(
            'Zalw_displayproducts/productinfo'
        );
        
        $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('displayproducts')->__('Email Id'),
         // 'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'email',
        ));


        return parent::_prepareForm();
    }

}*/








/*class Zalw_Displayproducts_Block_Adminhtml_Displayproducts_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        echo "dsf";
       
        $form = new Varien_Data_Form();
        $this->setForm($form);
        print_r(Mage::registry('message_details'));
        $fieldset = $form->addFieldset('fondation_form', array('legend'=>Mage::helper('displayproducts')->__('Item information')));
        $fieldset->addField('subject', 'text', array(
          'label'     => Mage::helper('displayproducts')->__('Subject'),
          //'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'subject',
        ));
                
        $fieldset->addField('eamil', 'text', array(
          'label'     => Mage::helper('displayproducts')->__('Email'),
          //'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'email',
        ));
                
        if (Mage::getSingleton('adminhtml/session')->getFondationData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFondationData());
            Mage::getSingleton('adminhtml/session')->setFondationData(null);
        } elseif (Mage::registry('message_details')) {
            $form->setValues(Mage::registry('message_details'));
        }
        return parent::_prepareForm();
    }
}*/
