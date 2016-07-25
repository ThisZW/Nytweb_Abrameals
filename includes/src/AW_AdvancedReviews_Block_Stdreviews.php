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


class AW_AdvancedReviews_Block_Stdreviews extends Mage_Core_Block_Template
{
    protected $_customerId;
    protected $_collection;
    protected $_collectionLoadFlag = false;

    public function getCustomerId()
    {
        if ($this->_customerId) {
            return $this->_customerId;
        } else {
            return $this->_restoreCustomerId();
        }
    }

    protected function _checkProsconsPlace()
    {
        if ($place = Mage::helper('advancedreviews')->getProsconsPlace()) {
            if (!($place['place'] == 'cust' && $place['id'] == $this->getCustomerId())) {
                Mage::helper('advancedreviews')->resetProscons()
                    ->setProsconsState('pros', 0)
                    ->setProsconsState('cons', 0);
                $place['place'] = 'cust';
                $place['id'] = $this->getCustomerId();
                Mage::helper('advancedreviews')->setProsconsPlace($place);
            }
        } else {
            $place['place'] = 'cust';
            $place['id'] = $this->getCustomerId();
            Mage::helper('advancedreviews')->setProsconsPlace($place);
        }
    }

    protected function _beforeToHtml()
    {
        $this->getCollection()
            ->load()
            ->addRateVotes()
        ;
        return parent::_beforeToHtml();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($toolbar = $this->getLayout()->getBlock('product_review_list.toolbar')) {
            $toolbar->setCollection($this->getReviewsCollection());
            $this->setChild('toolbar', $toolbar);
        }
        return $this;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_collection = Mage::getModel('review/review')->getCollection();

        //Define CustomerId for proscons filter
        Mage::register('advancedreviews_proscons_is_customer', $this->getCustomerId());

        $this->_checkProsconsPlace();
    }

    public function setCustomerId($customerId)
    {
        return $this->_customerId = $customerId;
    }

    protected function _restoreCustomerId()
    {
        return $this->setCustomerId(Mage::registry('aw_ar_customer_id'));
    }

    protected function _prepareCollection()
    {
        if (!$this->_collectionLoadFlag) {
            $this->_collection
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addCustomerFilter($this->getCustomerId())
                ->addProsconsFilter()
                ->setDateOrder();
            $this->_collectionLoadFlag = true;
        } else {
            return $this->_collection;
        }
    }

    public function getCollection()
    {
        $this->_prepareCollection();
        return $this->_collection;
    }

    public function getReviewsCollection()
    {
        return $this->getCollection();
    }

    public function getReviewUrl($id)
    {
        return Mage::helper('advancedreviews')->getReviewUrl($id);
    }
}