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


class AW_AdvancedReviews_Block_Ajax_Filter extends Mage_Core_Block_Template
{
    protected $_prosCollection;
    protected $_consCollection;
    protected $_reviewsCollection;

    protected function _construct()
    {
        parent::_construct();
        Mage::helper('advancedreviews')->setReviewsBackUrl($this->getRedirectBackUrl());
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        if ($list = $this->getParentBlock()) {
            $this->_reviewsCollection = $list->getReviewsCollection();
        }
        return $this;
    }

    public function setReviewsCollection($collection)
    {
        $this->_reviewsCollection = $collection;
        return $this;
    }

    public function getRedirectBackUrl()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['id'])) {
            unset($params['id']);
        }
        if (isset($params['category'])) {
            unset($params['category']);
        }
        $urlParams = array();
        $urlParams['_current'] = true;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }

    /*
     * Get common collection with all filters exclude TYPE filter
     */
    protected function _getCommonCollection()
    {
        $collection = Mage::getModel('advancedreviews/proscons')->getCollection();
        $collection->setStatusFilter()
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->addVoteCount()
            ->useFilteredReviews()
            ->addOrdering();

        if ($customerId = Mage::registry('advancedreviews_proscons_is_customer')) {
            $collection->setCustomerFilter($customerId);
        } elseif ($productId = Mage::registry('advancedreviews_proscons_is_product')) {
            $collection->setProductFilter($productId);
        }

        return $collection;
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

    public function needHidePros($num = null)
    {
        if ($num === null) {
            return (count($this->getProsCollection()) > Mage::helper('advancedreviews')->confProsDisplayCount());
        } else {
            return ($num > Mage::helper('advancedreviews')->confProsDisplayCount());
        }
    }

    public function needHideCons($num = null)
    {
        if ($num === null) {
            return (count($this->getConsCollection()) > Mage::helper('advancedreviews')->confConsDisplayCount());
        } else {
            return ($num > Mage::helper('advancedreviews')->confConsDisplayCount());
        }
    }

    public function canShow()
    {
        return Mage::helper('advancedreviews')->confShowProscons()
        && ($this->isFilterActive() || count($this->getProsCollection()) || count($this->getConsCollection()));
    }

    public function showPros()
    {
        return count($this->getProsCollection());
    }

    public function showCons()
    {
        return count($this->getConsCollection());
    }

    public function getResetFilter()
    {
        return $this->getUrl('advancedreviews/proscons/reset');
    }

    public function isChecked($prosconsId)
    {
        return Mage::helper('advancedreviews')->isProsconsRegistered($prosconsId);
    }

    public function isProsOpen()
    {
        return Mage::helper('advancedreviews')->getProsconsState('pros');
    }

    public function isConsOpen()
    {
        return Mage::helper('advancedreviews')->getProsconsState('cons');
    }

    public function isFilterActive()
    {
        return (Mage::helper('advancedreviews')->getAllProscons() !== null)
        && count(Mage::helper('advancedreviews')->getAllProscons());
    }

    public function getFilteredCount()
    {
        return count($this->_reviewsCollection);
    }

    public function hasPros()
    {
        $proscons = Mage::helper('advancedreviews')->getAllProscons();
        foreach ($this->_prosCollection as $pros) {
            if (in_array($pros->getId(), $proscons)) {
                return true;
            }
        }
        return false;
    }

    public function hasCons()
    {
        $proscons = Mage::helper('advancedreviews')->getAllProscons();
        foreach ($this->_consCollection as $cons) {
            if (in_array($cons->getId(), $proscons)) {
                return true;
            }
        }
        return false;
    }

    public function getFilterPros()
    {
        $prosArray = array();
        $proscons = Mage::helper('advancedreviews')->getAllProscons();
        foreach ($this->_prosCollection as $pros) {
            if (in_array($pros->getId(), $proscons)) {
                $prosArray[] = $pros;
            }
        }
        return $prosArray;
    }

    public function getFilterCons()
    {
        $consArray = array();
        $proscons = Mage::helper('advancedreviews')->getAllProscons();
        foreach ($this->_consCollection as $cons) {
            if (in_array($cons->getId(), $proscons)) {
                $consArray[] = $cons;
            }
        }
        return $consArray;
    }

    public function getDeleteUrl($prosconsId)
    {
        return $this->getUrl('advancedreviews/proscons/delete', array('prosconsId' => $prosconsId));
    }

    public function getProductId()
    {
        return $this->getRequest()->getParam('id');
    }

    public function getCustomerId()
    {
        return $this->getRequest()->getParam('customerId');
    }

    public function getPage()
    {
        return $this->getRequest()->getParam('p');
    }
}