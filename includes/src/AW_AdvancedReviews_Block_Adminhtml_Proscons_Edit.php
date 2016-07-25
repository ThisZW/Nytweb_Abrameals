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


class AW_AdvancedReviews_Block_Adminhtml_Proscons_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_currentRef;

    protected function _getRef()
    {
        if ($this->_currentRef) {
            return $this->_currentRef;
        } else {
            return $this->_currentRef = $this->getRequest()->getParam('ref');
        }
    }

    protected function _getItemName()
    {
        if ($ref = $this->_getRef()) {
            if ($ref === 'pros') {
                return Mage::helper('advancedreviews')->__('Pros');
            } elseif ($ref === 'cons') {
                return Mage::helper('advancedreviews')->__('Cons');
            } elseif ($ref === 'user') {
                return Mage::helper('advancedreviews')->getProsconsItemNameById($this->getRequest()->getParam('id'));
            }
        }
        return '';
    }

    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_proscons';
        $this->_blockGroup = 'advancedreviews';

        $this->_updateButton(
            'save',
            'label',
            Mage::helper('advancedreviews')->__('Save') . " " . $this->_getItemName()
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('advancedreviews')->__('Delete') . " " . $this->_getItemName()
        );

        if ($this->getRequest()->getParam($this->_objectId)) {
            $prosconsData = Mage::getModel('advancedreviews/proscons')->load(
                $this->getRequest()->getParam($this->_objectId)
            );

            Mage::register('advancedreviews_proscons_data', $prosconsData);
        }
    }

    public function getHeaderText()
    {
        if (
            Mage::registry('advancedreviews_proscons_data')
            && Mage::registry('advancedreviews_proscons_data')->getId()
        ) {
            return Mage::helper('advancedreviews')->__("Edit") . " " . $this->_getItemName();
        } else {
            return Mage::helper('advancedreviews')->__("New") . " " . $this->_getItemName();
        }
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/' . $this->_getRef());
        parent::getDeleteUrl();
    }

    public function getDeleteUrl()
    {
        return $this->getUrl(
            '*/*/delete',
            array(
                 $this->_objectId => $this->getRequest()->getParam($this->_objectId),
                 'ref'            => $this->_getRef(),
            )
        );
    }
}