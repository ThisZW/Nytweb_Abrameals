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


class AW_AdvancedReviews_Block_Adminhtml_Proscons extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    protected $_currentRef;

    protected function _getRef()
    {
        if ($this->_currentRef) {
            return $this->_currentRef;
        } else {
            return $this->_currentRef = (Mage::helper('advancedreviews')->isPros() ? 'pros' : 'cons');
        }
    }

    public function __construct()
    {
        switch (Mage::registry(Mage::helper('advancedreviews')->getConstPcRegRef())) {
            case Mage::helper('advancedreviews')->getConstTypePros() :
                //Pros
                $title = Mage::helper('advancedreviews')->__('Pros');
                $addButtonLabel = Mage::helper('advancedreviews')->__('Add New Pros');
                break;

            case Mage::helper('advancedreviews')->getConstTypeCons() :
                //Cons
                $title = Mage::helper('advancedreviews')->__('Cons');
                $addButtonLabel = Mage::helper('advancedreviews')->__('Add New Cons');
                break;

            default:
                //Nothing
                $title = Mage::helper('advancedreviews')->__('Error');
        }

        $this->_controller = 'adminhtml_proscons';
        $this->_blockGroup = 'advancedreviews';
        $this->_headerText = $title;
        $this->_addButtonLabel = $addButtonLabel;
        parent::__construct();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new', array('ref' => $this->_getRef()));
    }
}