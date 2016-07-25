<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_AdvancedReviews
 * @version    2.3.9
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_AdvancedReviews_Block_Adminhtml_Proscons_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                 'id'      => 'edit_form',
                 'action'  => $this->getUrl(
                     '*/*/save',
                     array(
                          'id'  => $this->getRequest()->getParam('id'),
                          'ref' => $this->getRequest()->getParam('ref'),
                     )
                 ),
                 'method'  => 'post',
                 'enctype' => 'multipart/form-data'
            )
        );

        $this->setForm($form);

        //General Information group
        $fieldset = $form->addFieldset(
            'proscons_form',
            array(
                 'legend' => Mage::helper('advancedreviews')->__('General Information')
            )
        );

        $fieldset->addField(
            'name',
            'text',
            array(
                 'name'     => 'name',
                 'label'    => Mage::helper('advancedreviews')->__('Name'),
                 'title'    => Mage::helper('advancedreviews')->__('Name'),
                 'class'    => 'required-entry',
                 'required' => true,
            )
        );

        $fieldset->addField(
            'status',
            'select',
            array(
                 'label'   => Mage::helper('advancedreviews')->__('Status'),
                 'title'   => Mage::helper('advancedreviews')->__('Status'),
                 'name'    => 'status',
                 'options' => Mage::getSingleton('advancedreviews/proscons_options')->getStatusOptionArray(),
            )
        );

        $fieldset->addField(
            'sort_order', 'text',
            array(
                 'name'  => 'sort_order',
                 'label' => Mage::helper('advancedreviews')->__('Sort Order'),
                 'title' => Mage::helper('advancedreviews')->__('Sort Order'),
                 'value' => 0,
            )
        );

        //Visibility group
        $fieldset = $form->addFieldset(
            'visibility_form',
            array(
                 'legend' => Mage::helper('advancedreviews')->__('Visibility')
            )
        );

        $fieldset->addField(
            'stores', 'multiselect',
            array(
                 'label'    => Mage::helper('rating')->__('Visible In'),
                 'required' => true,
                 'name'     => 'stores[]',
                 'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            )
        );

        if (Mage::getSingleton('adminhtml/session')->getprosconsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getProsconsData());
            Mage::getSingleton('adminhtml/session')->setProsconsData(null);
        } elseif (Mage::registry('advancedreviews_proscons_data')) {
            $form->setValues(Mage::registry('advancedreviews_proscons_data')->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}