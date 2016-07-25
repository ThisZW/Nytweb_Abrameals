<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the authenticate inbox form
 */
class Zalw_Advancemsg_Block_Adminhtml_Customermsg_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  /**
     * Define Form settings
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Newsletter_Template_Edit_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'reply_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('reply_fieldset', array(
            'legend'    => Mage::helper('advancemsg')->__('Reply to Previous Message'),
            'class'     => 'fieldset-wide'
        ));

	$fieldset->addField('message_text', 'textarea' , array(
		    'name'     => 'message_text',
		    'label'    => Mage::helper('advancemsg')->__('Message Text'),
		    'required'  => true,
                    'class'     => 'validate-length minimum-length-2 maximum-length-250',
		));
	
	$fieldset->addField('maximum_message_characters', 'note', array(
          'text'     => "(You can write message upto 250 characters.)",
        ));
        
        $fieldset->addField('reply_submit', 'submit', array(
          'value'     => 'send',
        ));
     
        $form->setAction($this->getUrl('*/*/reply/id/'.$this->getRequest()->getParam('id')));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
