<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the send new message form
 */
class Zalw_Advancemsg_Block_Adminhtml_Manage_Form extends Mage_Adminhtml_Block_Widget_Form
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
        $form   = new Varien_Data_Form(array(
            'id'        => 'manage_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('advancemsg')->__('Choose Message Template'),
            'class'     => 'fieldset-wide'
        ));
	$fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => Mage::helper('advancemsg')->__('Message Title'),
            'title'     => Mage::helper('advancemsg')->__('Message Title'),
            'required'  => true,
            'value'     => '',
        ));
        $fieldset->addField('link', 'text', array(
            'name'      => 'link',
            'label'     => Mage::helper('advancemsg')->__('Message Link'),
            'title'     => Mage::helper('advancemsg')->__('Message Link'),
	    'class'     => 'validate-link-url',
            'required'  => false,
            'value'     => '',
        ));
	$fieldset->addField('allowed_url', 'note', array(
            'text'     => "(Please enter a valid URL. For example http://www.example.com)",
        ));
        $fieldset->addField('template_id', 'select', array(
            'name'      => 'template_id',
            'label'     => Mage::helper('advancemsg')->__('Message Template'),
            'title'     => Mage::helper('advancemsg')->__('Template Name'),
            'required'  => true,
            'value'     => '',
            'values'    => Mage::getSingleton('advancemsg/template')->getTemplatesOtionArray(),
        ));
	$fieldset->addField('message_text', 'textarea' , array(
	    'name'     => 'message_text',
	    'label'    => Mage::helper('advancemsg')->__('Message Text'),
	    'required' => false,
	    'class'     => 'validate-length maximum-length-250',
	));
	$fieldset->addField('maximum_message_characters', 'note', array(
            'text'     => "(You can write message upto 250 characters.)",
        ));
	$fieldset->addField('template_attachment', 'file', array(
	    'name'      => 'template_attachment',
	    'label'     => Mage::helper('advancemsg')->__('Attachment File'),
	    'required'  => false,	    
	));
	$fieldset->addField('allowed_filetypes', 'note', array(
            'text'     => "(Files type allowed: jpg,jpeg,gif,png,pdf,doc,xls,csv,docx)",
        ));
        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
