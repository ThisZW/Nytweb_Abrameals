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


class AW_AdvancedReviews_Block_Proscons_Checker extends Mage_Core_Block_Template
{
    protected $_prosCollection;
    protected $_consCollection;

    /*
     * Get common collection with all filters exclude TYPE filter
     */
    protected function _getCommonCollection()
    {
        return Mage::getModel('advancedreviews/proscons')->getCollection()
            ->setStatusFilter()
            ->addOrdering()
            ->setStoreFilter(Mage::app()->getStore()->getId())
        ;
    }

    public function getProsCollection()
    {
        if ($this->_prosCollection) {
            return $this->_prosCollection;
        } else {
            return $this->_prosCollection = $this->_getCommonCollection()->setProsFilter();
        }
    }

    public function getConsCollection()
    {
        if ($this->_consCollection) {
            return $this->_consCollection;
        } else {
            return $this->_consCollection = $this->_getCommonCollection()->setConsFilter();
        }
    }

    public function canShow()
    {
        return Mage::helper('advancedreviews')->confShowProscons()
        && (count($this->getProsCollection())
            || count($this->getConsCollection())
            || Mage::helper('advancedreviews')->confEnableUserDefined()
        );
    }

    public function showUserDefined()
    {
        return Mage::helper('advancedreviews')->confEnableUserDefined();
    }
}